Migrated leoauri.com from WordPress to static HTML: mirrored site with wget to leoauri.com/. Converted html to pug (in src/), implemented build system (pug & scss). tests/ contains pytest suite: link hygiene (no /index.html refs), internal link integrity, external link checking, and embedded media availability (YouTube, Vimeo, Bandcamp).

Pug spacing: Use inline interpolation `#[a(href='...') link text]` to preserve spaces around links in text. Multiline link syntax strips whitespace.

Pages: each page is `src/<slug>/index.pug` (never `<slug>.pug`, URLs have no .html), extending `src/_layout.pug` (head, nav, footer). Set `title`, optionally `description`, `bodyClass`, `current` (menu key) in `block vars`; content in `block content`. Use root-relative links (`/recent/`). Build (`make build`) exits non-zero on pug/scss errors; `make test` runs pytest via uv.
