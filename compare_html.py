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
Compare canonical versions of HTML files between leoauri.com/ and leoauri.com_orig/
using Beautiful Soup for normalized comparison.
"""

from pathlib import Path
from bs4 import BeautifulSoup
from typing import Tuple, List, Optional
import difflib


def find_html_files(directory: Path) -> List[Path]:
    """Recursively find all HTML files in a directory."""
    return sorted(directory.rglob("*.html"))


def get_relative_path(file_path: Path, base_dir: Path) -> Path:
    """Get the relative path from the base directory."""
    return file_path.relative_to(base_dir)


def normalize_html(html_content: str) -> str:
    """
    Parse and normalize HTML using Beautiful Soup.
    Returns prettified, canonical version of the HTML.
    """
    soup = BeautifulSoup(html_content, 'html.parser')
    # Prettify creates consistent formatting
    return soup.prettify()


def compare_files(file1: Path, file2: Path) -> Tuple[bool, Optional[str]]:
    """
    Compare two HTML files using Beautiful Soup normalization.
    Returns (are_equal, diff_message)
    """
    try:
        content1 = file1.read_text(encoding='utf-8', errors='ignore')
        content2 = file2.read_text(encoding='utf-8', errors='ignore')

        # Normalize both files
        norm1 = normalize_html(content1)
        norm2 = normalize_html(content2)

        if norm1 == norm2:
            return True, None

        # Generate diff for display
        diff = difflib.unified_diff(
            norm1.splitlines(keepends=True),
            norm2.splitlines(keepends=True),
            fromfile=str(file1),
            tofile=str(file2),
            lineterm=''
        )
        diff_text = ''.join(diff)

        return False, diff_text

    except Exception as e:
        return False, f"Error comparing files: {str(e)}"


def main():
    # Define base directories
    base_dir = Path("/Users/leoauri/Desktop/website/staticise")
    dir1 = base_dir / "leoauri.com"
    dir2 = base_dir / "leoauri.com_orig"

    # Check directories exist
    if not dir1.exists():
        print(f"Error: {dir1} does not exist")
        return
    if not dir2.exists():
        print(f"Error: {dir2} does not exist")
        return

    # Find all HTML files in first directory
    html_files_1 = find_html_files(dir1)
    print(f"Found {len(html_files_1)} HTML files in {dir1.name}/")

    # Track results
    total_files = 0
    identical_files = 0
    different_files = 0
    missing_files = 0
    differences = []

    # Compare each file
    for file1 in html_files_1:
        rel_path = get_relative_path(file1, dir1)
        file2 = dir2 / rel_path

        total_files += 1

        if not file2.exists():
            missing_files += 1
            print(f"✗ MISSING in {dir2.name}/: {rel_path}")
            differences.append(f"Missing: {rel_path}")
            continue

        are_equal, diff_msg = compare_files(file1, file2)

        if are_equal:
            identical_files += 1
            print(f"✓ IDENTICAL: {rel_path}")
        else:
            different_files += 1
            print(f"✗ DIFFERENT: {rel_path}")
            differences.append((str(rel_path), diff_msg))

    # Summary
    print("\n" + "="*80)
    print("COMPARISON SUMMARY")
    print("="*80)
    print(f"Total files compared:  {total_files}")
    print(f"Identical files:       {identical_files}")
    print(f"Different files:       {different_files}")
    print(f"Missing in _orig:      {missing_files}")

    # Show ALL differences
    if different_files > 0:
        print("\n" + "="*80)
        print("DIFFERENCES FOUND")
        print("="*80)
        for item in differences:
            if isinstance(item, tuple):
                rel_path, diff_msg = item
                print(f"\n{rel_path}:")
                print(diff_msg)


if __name__ == "__main__":
    main()
