#!/usr/bin/env -S uv run --script
#
# /// script
# requires-python = ">=3.13"
# dependencies = [
#     "beautifulsoup4",
#     "lxml",
#     "html5lib",
# ]
# ///

"""
Compare canonical versions of HTML files in leoauri.com/ against their HEAD versions
using Beautiful Soup for normalized comparison.
"""

from pathlib import Path
from bs4 import BeautifulSoup, Tag, NavigableString, Comment
from typing import Tuple, List, Optional
import difflib
import subprocess
import sys
import argparse
import re


def find_html_files(directory: Path) -> List[Path]:
    """Recursively find all HTML files in a directory."""
    return sorted(directory.rglob("*.html"))


def get_relative_path(file_path: Path, base_dir: Path) -> Path:
    """Get the relative path from the base directory."""
    return file_path.relative_to(base_dir)


def get_git_head_html_files(repo_root: Path, html_dir: Path) -> List[Path]:
    """
    Get list of all HTML files in HEAD under the html_dir directory.
    Returns absolute paths.
    """
    rel_html_dir = html_dir.relative_to(repo_root)
    try:
        result = subprocess.run(
            ["git", "ls-tree", "-r", "--name-only", "HEAD", str(rel_html_dir)],
            cwd=repo_root,
            capture_output=True,
            text=True,
            check=True,
        )
        # Filter for .html files and convert to absolute paths
        html_files = []
        for line in result.stdout.strip().split('\n'):
            if line and line.endswith('.html'):
                html_files.append(repo_root / line)
        return sorted(html_files)
    except subprocess.CalledProcessError:
        return []


def get_git_head_content(file_path: Path, repo_root: Path) -> Optional[str]:
    """
    Get the content of a file from git HEAD.
    Returns None if the file doesn't exist in HEAD.
    """
    rel_path = file_path.relative_to(repo_root)
    try:
        result = subprocess.run(
            ["git", "show", f"HEAD:{rel_path}"],
            cwd=repo_root,
            capture_output=True,
            text=True,
            check=True,
        )
        return result.stdout
    except subprocess.CalledProcessError:
        return None


def normalize_html(html_content: str) -> str:
    """
    Parse and normalize HTML using Beautiful Soup.
    Returns prettified, canonical version of the HTML.
    """
    soup = BeautifulSoup(html_content, 'html.parser')
    # Prettify creates consistent formatting
    return soup.prettify()


def normalize_attr_value(value: str) -> str:
    """Normalize attribute values by removing whitespace around commas."""
    if ',' in value:
        # Remove spaces around commas in comma-separated values
        return re.sub(r'\s*,\s*', ',', value)
    return value


def normalize_dom(element):
    """Recursively normalize all attribute values and comments in a DOM tree in-place."""
    if isinstance(element, Comment):
        # Normalize comment whitespace by stripping and adding single spaces
        normalized = ' ' + str(element).strip() + ' '
        element.replace_with(Comment(normalized))
    elif isinstance(element, Tag):
        # Normalize all attribute values
        for key in element.attrs:
            if isinstance(element.attrs[key], str):
                element.attrs[key] = normalize_attr_value(element.attrs[key])

        # Recursively normalize children (need list() to avoid modification during iteration)
        for child in list(element.children):
            normalize_dom(child)


def is_whitespace_node(elem) -> bool:
    """Check if element is a whitespace-only text node."""
    return (isinstance(elem, NavigableString) and
            not isinstance(elem, Comment) and
            str(elem).strip() == '')


def elements_equal(elem1, elem2) -> bool:
    """
    Recursively compare two Beautiful Soup elements for semantic equality.
    Normalizes attribute values to ignore whitespace differences.
    Ignores whitespace-only text nodes.
    """
    # Both must be same type
    if type(elem1) != type(elem2):
        return False

    # Handle NavigableString (text nodes)
    if isinstance(elem1, NavigableString):
        # Ignore comments differences if both are comments
        if isinstance(elem1, Comment) and isinstance(elem2, Comment):
            return str(elem1).strip() == str(elem2).strip()
        return str(elem1) == str(elem2)

    # Handle Tag elements
    if isinstance(elem1, Tag):
        # Tag names must match
        if elem1.name != elem2.name:
            return False

        # Must have same attributes (keys)
        if set(elem1.attrs.keys()) != set(elem2.attrs.keys()):
            return False

        # Compare attribute values with normalization
        for key in elem1.attrs:
            val1 = elem1.attrs[key]
            val2 = elem2.attrs[key]

            # Normalize both values if they're strings
            if isinstance(val1, str) and isinstance(val2, str):
                val1 = normalize_attr_value(val1)
                val2 = normalize_attr_value(val2)

            if val1 != val2:
                return False

        # Filter out whitespace-only text nodes from children
        children1 = [c for c in elem1.children if not is_whitespace_node(c)]
        children2 = [c for c in elem2.children if not is_whitespace_node(c)]

        if len(children1) != len(children2):
            return False

        # Recursively compare all children
        for child1, child2 in zip(children1, children2):
            if not elements_equal(child1, child2):
                return False

        return True

    # For other types, use default equality
    return elem1 == elem2


def compare_html_content(
    content1: str, content2: str, label1: str, label2: str
) -> Tuple[bool, Optional[str]]:
    """
    Compare two HTML content strings by comparing parsed DOM structure.
    Returns (are_equal, diff_message)
    """
    try:
        # Parse both HTML contents into DOM trees using html5lib for better normalization
        soup1 = BeautifulSoup(content1, 'html5lib')
        soup2 = BeautifulSoup(content2, 'html5lib')

        # Normalize attribute values before comparison
        normalize_dom(soup1)
        normalize_dom(soup2)

        # Compare the parsed DOM structures using custom comparison
        # This ignores whitespace differences in attributes and formatting
        if elements_equal(soup1, soup2):
            return True, None

        # If different after semantic comparison, generate prettified diff for display
        # html5lib parser already normalizes void elements consistently
        norm1 = soup1.prettify(formatter='html')
        norm2 = soup2.prettify(formatter='html')

        # If prettified versions are identical, the differences were only cosmetic
        if norm1 == norm2:
            return True, None

        diff = difflib.unified_diff(
            norm1.splitlines(keepends=True),
            norm2.splitlines(keepends=True),
            fromfile=label1,
            tofile=label2,
            lineterm=''
        )
        diff_text = ''.join(diff)

        return False, diff_text

    except Exception as e:
        return False, f"Error comparing files: {str(e)}"


def main():
    parser = argparse.ArgumentParser(
        description="Compare HTML files against their git HEAD versions using Beautiful Soup normalization"
    )
    parser.add_argument(
        "--name-only",
        action="store_true",
        help="Only show names of files that differ",
    )
    parser.add_argument(
        "files",
        nargs="*",
        help="Specific files to compare (relative to repo root). If not specified, compares all HTML files.",
    )
    args = parser.parse_args()

    # Use the directory containing this script as the repo root
    repo_root = Path(__file__).resolve().parent
    html_dir = repo_root / "leoauri.com"

    # Check directory exists
    if not html_dir.exists():
        print(f"Error: {html_dir} does not exist", file=sys.stderr)
        sys.exit(1)

    # Determine which files to check
    if args.files:
        # Convert provided file paths to absolute paths
        html_files_working = []
        for file_arg in args.files:
            file_path = repo_root / file_arg
            # Allow checking deleted files (not in working directory)
            if not file_path.suffix == ".html":
                print(f"Warning: {file_arg} is not an HTML file, skipping", file=sys.stderr)
                continue
            html_files_working.append(file_path)
        # When specific files are provided, also check those files in HEAD
        html_files_head = []
        for file_arg in args.files:
            file_path = repo_root / file_arg
            if file_path.suffix == ".html":
                html_files_head.append(file_path)
    else:
        # Find all HTML files in working directory and HEAD
        html_files_working = find_html_files(html_dir)
        html_files_head = get_git_head_html_files(repo_root, html_dir)

    # Create sets for comparison
    working_set = set(html_files_working)
    head_set = set(html_files_head)

    # Find all files to process
    all_files = working_set | head_set

    # Track results
    differences = []
    new_files = []
    deleted_files = []

    # Compare each file
    for file_path in sorted(all_files):
        rel_path = get_relative_path(file_path, repo_root)

        # Check if file exists in HEAD
        in_head = file_path in head_set
        in_working = file_path in working_set

        if in_head and not in_working:
            # File exists in HEAD but not in working directory (deleted)
            deleted_files.append(str(rel_path))
            continue

        if in_working and not in_head:
            # File exists in working directory but not in HEAD (new file)
            new_files.append(str(rel_path))
            continue

        # File exists in both, compare them
        head_content = get_git_head_content(file_path, repo_root)
        working_content = file_path.read_text(encoding="utf-8", errors="ignore")

        # Compare
        are_equal, diff_msg = compare_html_content(
            head_content, working_content, f"HEAD:{rel_path}", f"Working:{rel_path}"
        )

        if not are_equal:
            differences.append((str(rel_path), diff_msg))

    # Only output if there are differences
    has_changes = len(differences) > 0 or len(new_files) > 0 or len(deleted_files) > 0

    if has_changes:
        if args.name_only:
            # Just list file names with status prefix
            for rel_path in deleted_files:
                print(f"D {rel_path}")
            for rel_path in new_files:
                print(f"A {rel_path}")
            for rel_path, _ in differences:
                print(f"M {rel_path}")
        else:
            # Show full output
            if deleted_files:
                print("DELETED FILES (in HEAD but not in working directory):")
                print("=" * 80)
                for rel_path in deleted_files:
                    print(f"  - {rel_path}")
                print()

            if new_files:
                print("NEW FILES (not in HEAD):")
                print("=" * 80)
                for rel_path in new_files:
                    print(f"  + {rel_path}")
                print()

            if differences:
                print("DIFFERENCES FOUND:")
                print("=" * 80)
                for rel_path, diff_msg in differences:
                    print(f"\n{rel_path}:")
                    print(diff_msg)

        sys.exit(1)

    # No output and exit 0 if no differences
    sys.exit(0)


if __name__ == "__main__":
    main()
