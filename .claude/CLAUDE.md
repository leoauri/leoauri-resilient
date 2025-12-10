Migrated leoauri.com from WordPress to static HTML: mirrored site with wget to leoauri.com/. Converted html to pug (in src/), implemented build system (pug & scss). tests/ contains pytest suite: link hygiene (no /index.html refs), internal link integrity, external link checking, and embedded media availability (YouTube, Vimeo, Bandcamp).

Pug spacing: Use inline interpolation `#[a(href='...') link text]` to preserve spaces around links in text. Multiline link syntax strips whitespace.
