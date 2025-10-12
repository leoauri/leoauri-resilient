"""Test that internal links don't explicitly reference index.html files."""

import re
from pathlib import Path


def test_no_internal_index_html_links():
    """Verify that no internal links end with /index.html."""
    site_dir = Path("leoauri.com")

    # Pattern to match internal links only (not starting with http:// or https://)
    pattern = re.compile(r'href="((?!https?://)[^"]*\/index\.html)"')

    violations = []

    for html_file in site_dir.rglob("*.html"):
        content = html_file.read_text()
        matches = pattern.findall(content)

        if matches:
            violations.append({
                'file': html_file,
                'matches': matches
            })

    # Build error message if violations found
    if violations:
        error_msg = f"\nFound {len(violations)} files with internal links to */index.html:\n\n"

        for v in violations[:5]:  # Show first 5 files
            error_msg += f"  {v['file']}:\n"
            for match in v['matches'][:3]:  # Show first 3 matches per file
                error_msg += f"    - {match}\n"

        if len(violations) > 5:
            error_msg += f"\n  ... and {len(violations) - 5} more files\n"

        raise AssertionError(error_msg)
