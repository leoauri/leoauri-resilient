"""Test that all embedded media (YouTube, Vimeo, Bandcamp) is still available."""

import re
from pathlib import Path
import urllib.request
from urllib.error import URLError, HTTPError
import json


def test_embedded_media_availability():
    """Check that all embedded YouTube, Vimeo, and Bandcamp media is still available."""
    site_dir = Path("leoauri.com")

    # Pattern to extract iframe src attributes
    iframe_pattern = re.compile(r"<iframe[^>]+src=['\"]([^'\"]+)['\"]", re.IGNORECASE)

    # Cache for checked media
    checked_media = {}
    broken_media = []

    for html_file in site_dir.rglob("*.html"):
        content = html_file.read_text()
        matches = iframe_pattern.findall(content)

        for embed_url in matches:
            # Skip if already checked
            if embed_url in checked_media:
                if not checked_media[embed_url]['available']:
                    broken_media.append({
                        'source_file': html_file,
                        'embed_url': embed_url,
                        'status': checked_media[embed_url]['status']
                    })
                continue

            # Check based on provider
            if 'youtube.com' in embed_url or 'youtube-nocookie.com' in embed_url:
                result = check_youtube_embed(embed_url)
            elif 'vimeo.com' in embed_url:
                result = check_vimeo_embed(embed_url)
            elif 'bandcamp.com' in embed_url:
                result = check_bandcamp_embed(embed_url)
            else:
                # Unknown embed type, skip
                continue

            checked_media[embed_url] = result

            if not result['available']:
                broken_media.append({
                    'source_file': html_file,
                    'embed_url': embed_url,
                    'status': result['status']
                })

    # Build error message if broken media found
    if broken_media:
        error_msg = f"\nFound {len(broken_media)} broken embedded media:\n\n"

        for media_info in broken_media[:15]:  # Show first 15
            error_msg += f"  {media_info['source_file']}:\n"
            error_msg += f"    Embed: {media_info['embed_url']}\n"
            error_msg += f"    Status: {media_info['status']}\n\n"

        if len(broken_media) > 15:
            error_msg += f"  ... and {len(broken_media) - 15} more broken embeds\n"

        raise AssertionError(error_msg)


def check_youtube_embed(embed_url):
    """Check if a YouTube video or playlist is available."""
    # Extract video ID or playlist ID
    video_match = re.search(r'/embed/([^?&/]+)', embed_url)
    playlist_match = re.search(r'[?&]list=([^&]+)', embed_url)

    if video_match and video_match.group(1) != 'videoseries':
        video_id = video_match.group(1)
        # Use YouTube oEmbed API
        oembed_url = f"https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v={video_id}&format=json"

        try:
            req = urllib.request.Request(oembed_url)
            req.add_header('User-Agent', 'Mozilla/5.0 (Link Checker)')
            with urllib.request.urlopen(req, timeout=10) as response:
                data = json.loads(response.read().decode())
                return {'available': True, 'status': 'OK'}
        except HTTPError as e:
            if e.code == 404 or e.code == 401:
                return {'available': False, 'status': f'Video unavailable (HTTP {e.code})'}
            return {'available': False, 'status': f'HTTP {e.code}'}
        except Exception as e:
            return {'available': False, 'status': f'Error: {str(e)}'}

    elif playlist_match:
        # For playlists, we can try to fetch the embed URL itself
        # YouTube will show an error page if the playlist is empty/deleted
        try:
            req = urllib.request.Request(embed_url)
            req.add_header('User-Agent', 'Mozilla/5.0 (Link Checker)')
            with urllib.request.urlopen(req, timeout=10) as response:
                html = response.read().decode('utf-8', errors='ignore')
                # Check for error indicators in the page
                if 'unavailable' in html.lower() or 'not available' in html.lower():
                    return {'available': False, 'status': 'Playlist unavailable'}
                return {'available': True, 'status': 'OK'}
        except Exception as e:
            return {'available': False, 'status': f'Error: {str(e)}'}

    return {'available': True, 'status': 'Could not verify'}


def check_vimeo_embed(embed_url):
    """Check if a Vimeo video is available."""
    # Extract video ID
    video_match = re.search(r'/video/(\d+)', embed_url)

    if video_match:
        video_id = video_match.group(1)
        # Use Vimeo oEmbed API
        oembed_url = f"https://vimeo.com/api/oembed.json?url=https://vimeo.com/{video_id}"

        try:
            req = urllib.request.Request(oembed_url)
            req.add_header('User-Agent', 'Mozilla/5.0 (Link Checker)')
            with urllib.request.urlopen(req, timeout=10) as response:
                data = json.loads(response.read().decode())
                return {'available': True, 'status': 'OK'}
        except HTTPError as e:
            if e.code == 404:
                return {'available': False, 'status': 'Video not found (404)'}
            return {'available': False, 'status': f'HTTP {e.code}'}
        except Exception as e:
            return {'available': False, 'status': f'Error: {str(e)}'}

    return {'available': True, 'status': 'Could not verify'}


def check_bandcamp_embed(embed_url):
    """Check if a Bandcamp album/track is available."""
    # Bandcamp embeds are generally stable, but we can check if the URL loads
    try:
        req = urllib.request.Request(embed_url)
        req.add_header('User-Agent', 'Mozilla/5.0 (Link Checker)')
        with urllib.request.urlopen(req, timeout=10) as response:
            html = response.read().decode('utf-8', errors='ignore')
            # Check for specific error indicators (be more precise)
            if 'album not found' in html.lower() or 'track not found' in html.lower():
                return {'available': False, 'status': 'Album/track not found'}
            # Check if the page is actually an error page (HTTP 200 but content indicates error)
            if response.status >= 400:
                return {'available': False, 'status': f'HTTP {response.status}'}
            return {'available': True, 'status': 'OK'}
    except HTTPError as e:
        return {'available': False, 'status': f'HTTP {e.code}'}
    except Exception as e:
        return {'available': False, 'status': f'Error: {str(e)}'}
