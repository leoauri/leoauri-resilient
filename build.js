#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const pug = require('pug');

const SRC_DIR = 'src';
const BUILD_DIR = 'leoauri.com';

// Clean and create build directory
if (fs.existsSync(BUILD_DIR)) {
  fs.rmSync(BUILD_DIR, { recursive: true });
}
fs.mkdirSync(BUILD_DIR, { recursive: true });

// Recursively process directory
function processDirectory(srcPath, destPath) {
  const entries = fs.readdirSync(srcPath, { withFileTypes: true });

  for (const entry of entries) {
    const srcFullPath = path.join(srcPath, entry.name);
    const destFullPath = path.join(destPath, entry.name);

    if (entry.isDirectory()) {
      // Create directory and recurse
      fs.mkdirSync(destFullPath, { recursive: true });
      processDirectory(srcFullPath, destFullPath);
    } else if (entry.isFile()) {
      if (entry.name.endsWith('.pug')) {
        // Convert .pug to .html using Pug API
        const htmlFileName = entry.name.replace(/\.pug$/, '.html');
        const destHtmlPath = path.join(destPath, htmlFileName);

        console.log(`Converting ${srcFullPath} -> ${destHtmlPath}`);

        try {
          const html = pug.renderFile(srcFullPath, {
            pretty: false
          });
          fs.writeFileSync(destHtmlPath, html);
        } catch (error) {
          console.error(`Error converting ${srcFullPath}:`, error.message);
        }
      } else {
        // Copy file as-is
        console.log(`Copying ${srcFullPath} -> ${destFullPath}`);
        fs.copyFileSync(srcFullPath, destFullPath);
      }
    }
  }
}

console.log('Building site...');
processDirectory(SRC_DIR, BUILD_DIR);
console.log('Build complete!');
