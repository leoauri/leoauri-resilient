"""Test that all internal and external links in HTML files point to existing resources."""

import re
from pathlib import Path
from urllib.parse import urljoin, urlparse
import urllib.request
from urllib.error import URLError, HTTPError


def test_no_broken_internal_links():
    """Verify that all internal links point to existing files or directories."""
    site_dir = Path("leoauri.com")

    # Pattern to match href attributes
    href_pattern = re.compile(r'href="([^"]+)"')

    broken_links = []

    for html_file in site_dir.rglob("*.html"):
        content = html_file.read_text()
        matches = href_pattern.findall(content)

        for link in matches:
            # Skip external links, anchors, mailto, tel, etc.
            if (link.startswith(('http://', 'https://', 'mailto:', 'tel:', '#'))
                or link.startswith('//')  # Protocol-relative URLs
                or not link.strip()):  # Empty links
                continue

            # Resolve the link relative to the current file
            if link.startswith('/'):
                # Absolute path from site root
                target_path = site_dir / link.lstrip('/')
            else:
                # Relative path from current file
                target_path = (html_file.parent / link).resolve()

            # Remove URL fragments (anchors)
            if '#' in str(target_path):
                target_path = Path(str(target_path).split('#')[0])

            # Remove query strings
            if '?' in str(target_path):
                target_path = Path(str(target_path).split('?')[0])

            # Check if target exists (file or directory)
            exists = False
            if target_path.exists():
                exists = True
            elif target_path.is_dir() or (target_path / 'index.html').exists():
                # Directory links should have an index.html
                exists = True
            elif not target_path.suffix and (target_path.parent / f"{target_path.name}.html").exists():
                # Link without extension might point to .html file
                exists = True
            elif str(target_path).endswith('/') and (Path(str(target_path).rstrip('/')) / 'index.html').exists():
                # Trailing slash implies directory with index.html
                exists = True

            if not exists:
                broken_links.append({
                    'source_file': html_file,
                    'link': link,
                    'resolved_path': target_path
                })

    # Build error message if broken links found
    if broken_links:
        error_msg = f"\nFound {len(broken_links)} broken internal links:\n\n"

        for link_info in broken_links[:10]:  # Show first 10 broken links
            error_msg += f"  {link_info['source_file']}:\n"
            error_msg += f"    Link: {link_info['link']}\n"
            error_msg += f"    Resolved to: {link_info['resolved_path']}\n\n"

        if len(broken_links) > 10:
            error_msg += f"  ... and {len(broken_links) - 10} more broken links\n"

        raise AssertionError(error_msg)


def test_no_broken_external_links():
    """Verify that all external HTTP/HTTPS links are accessible."""
    site_dir = Path("leoauri.com")

    # Pattern to match href attributes
    href_pattern = re.compile(r'href="([^"]+)"')

    # Cache to avoid checking the same URL multiple times
    checked_urls = {}
    broken_links = []

    for html_file in site_dir.rglob("*.html"):
        content = html_file.read_text()
        matches = href_pattern.findall(content)

        for link in matches:
            # Only check external HTTP/HTTPS links
            if not link.startswith(('http://', 'https://')):
                continue

            # Remove URL fragments for checking
            url = link.split('#')[0]

            # Skip if we've already checked this URL
            if url in checked_urls:
                if not checked_urls[url]:
                    broken_links.append({
                        'source_file': html_file,
                        'link': link,
                        'status': 'Previously failed'
                    })
                continue

            # Try to access the URL
            try:
                # Use HEAD request for efficiency
                req = urllib.request.Request(url, method='HEAD')
                req.add_header('User-Agent', 'Mozilla/5.0 (Link Checker)')

                with urllib.request.urlopen(req, timeout=10) as response:
                    status_code = response.status
                    checked_urls[url] = (200 <= status_code < 400)

                    if not checked_urls[url]:
                        broken_links.append({
                            'source_file': html_file,
                            'link': link,
                            'status': f'HTTP {status_code}'
                        })

            except HTTPError as e:
                # Try GET request as fallback (some servers don't support HEAD)
                try:
                    req = urllib.request.Request(url)
                    req.add_header('User-Agent', 'Mozilla/5.0 (Link Checker)')

                    with urllib.request.urlopen(req, timeout=10) as response:
                        status_code = response.status
                        checked_urls[url] = (200 <= status_code < 400)

                        if not checked_urls[url]:
                            broken_links.append({
                                'source_file': html_file,
                                'link': link,
                                'status': f'HTTP {status_code}'
                            })
                except Exception as fallback_e:
                    checked_urls[url] = False
                    broken_links.append({
                        'source_file': html_file,
                        'link': link,
                        'status': f'HTTP {e.code}'
                    })

            except URLError as e:
                checked_urls[url] = False
                broken_links.append({
                    'source_file': html_file,
                    'link': link,
                    'status': f'Connection error: {e.reason}'
                })

            except Exception as e:
                checked_urls[url] = False
                broken_links.append({
                    'source_file': html_file,
                    'link': link,
                    'status': f'Error: {str(e)}'
                })

    # Build error message if broken links found
    if broken_links:
        error_msg = f"\nFound {len(broken_links)} broken external links:\n\n"

        for link_info in broken_links[:10]:  # Show first 10 broken links
            error_msg += f"  {link_info['source_file']}:\n"
            error_msg += f"    Link: {link_info['link']}\n"
            error_msg += f"    Status: {link_info['status']}\n\n"

        if len(broken_links) > 10:
            error_msg += f"  ... and {len(broken_links) - 10} more broken links\n"

        raise AssertionError(error_msg)
