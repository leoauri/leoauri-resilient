#!/usr/bin/env -S uv run --script
#
# /// script
# requires-python = ">=3.13"
# dependencies = [
#     "beautifulsoup4",
#     "lxml",
# ]
# ///

"""
Compare canonical versions of HTML files in leoauri.com/ against their HEAD versions
using Beautiful Soup for normalized comparison.
"""

from pathlib import Path
from bs4 import BeautifulSoup
from typing import Tuple, List, Optional
import difflib
import subprocess
import sys


def find_html_files(directory: Path) -> List[Path]:
    """Recursively find all HTML files in a directory."""
    return sorted(directory.rglob("*.html"))


def get_relative_path(file_path: Path, base_dir: Path) -> Path:
    """Get the relative path from the base directory."""
    return file_path.relative_to(base_dir)


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


def compare_html_content(
    content1: str, content2: str, label1: str, label2: str
) -> Tuple[bool, Optional[str]]:
    """
    Compare two HTML content strings using Beautiful Soup normalization.
    Returns (are_equal, diff_message)
    """
    try:
        # Normalize both files
        norm1 = normalize_html(content1)
        norm2 = normalize_html(content2)

        if norm1 == norm2:
            return True, None

        # Generate diff for display
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
    # Use the directory containing this script as the repo root
    repo_root = Path(__file__).resolve().parent
    html_dir = repo_root / "leoauri.com"

    # Check directory exists
    if not html_dir.exists():
        print(f"Error: {html_dir} does not exist", file=sys.stderr)
        sys.exit(1)

    # Find all HTML files
    html_files = find_html_files(html_dir)

    # Track results
    differences = []
    new_files = []

    # Compare each file
    for file_path in html_files:
        rel_path = get_relative_path(file_path, repo_root)

        # Get HEAD version
        head_content = get_git_head_content(file_path, repo_root)

        if head_content is None:
            # File doesn't exist in HEAD (new file)
            new_files.append(str(rel_path))
            continue

        # Get working tree version
        working_content = file_path.read_text(encoding="utf-8", errors="ignore")

        # Compare
        are_equal, diff_msg = compare_html_content(
            head_content, working_content, f"HEAD:{rel_path}", f"Working:{rel_path}"
        )

        if not are_equal:
            differences.append((str(rel_path), diff_msg))

    # Only output if there are differences
    has_changes = len(differences) > 0 or len(new_files) > 0

    if has_changes:
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
