#!/usr/bin/env python3
"""
Clean query parameters from filenames and update all references.
"""
from pathlib import Path
import re
import shutil

def main():
    site_dir = Path("src")

    # Find all files with ? in their names
    files_with_queries = []
    for file_path in site_dir.rglob("*"):
        if file_path.is_file() and "?" in file_path.name:
            files_with_queries.append(file_path)

    print(f"Found {len(files_with_queries)} files with query parameters")

    # Create mapping of old paths to new paths
    rename_map = {}
    for old_path in files_with_queries:
        # Remove everything from ? onwards
        new_name = old_path.name.split("?")[0]
        new_path = old_path.parent / new_name
        rename_map[old_path] = new_path
        print(f"Will rename: {old_path.relative_to(site_dir)}")
        print(f"        to: {new_path.relative_to(site_dir)}")

    # Rename the files
    print("\nRenaming files...")
    for old_path, new_path in rename_map.items():
        if new_path.exists():
            print(f"Warning: {new_path} already exists, skipping {old_path}")
            continue
        shutil.move(str(old_path), str(new_path))
        print(f"Renamed: {old_path.name} -> {new_path.name}")

    # Update references in pug files
    print("\nUpdating references in pug files...")
    for pug_file in site_dir.rglob("*.pug"):
        content = pug_file.read_text()
        original_content = content

        # For each renamed file, update references
        for old_path, new_path in rename_map.items():
            old_rel = old_path.relative_to(site_dir)
            new_rel = new_path.relative_to(site_dir)

            # Replace references with both the original and URL-encoded versions
            old_str = str(old_rel)
            new_str = str(new_rel)

            # Direct replacement
            content = content.replace(old_str, new_str)

            # URL-encoded version (? becomes %3F)
            old_str_encoded = old_str.replace("?", "%3F")
            content = content.replace(old_str_encoded, new_str)

        # Write back if changed
        if content != original_content:
            pug_file.write_text(content)
            print(f"Updated: {pug_file.relative_to(site_dir)}")

    print("\nDone!")

if __name__ == "__main__":
    main()
