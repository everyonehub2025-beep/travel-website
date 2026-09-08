# Image Shot List — SDI Travel Trust

No demo/stock photography ships in this build. The real SDI logo is included and already wired into the header, footer, login screen, and favicon (see `SETUP.md` §4) — the gaps below are photography only.

## Tracked placeholder slots (full photography needed)

| # | Page | What Belongs Here | Aspect Ratio | Suggested Dimensions | Stock Search Terms (fallback only) |
|---|---|---|---|---|---|
| 1 | Home | Hero — member family, portrait crop | 9/10 | 1600x1800px | family airport departure, travelers with luggage smiling, group boarding plane |

**To replace:** in Elementor, select the block on the Home page (it's an HTML widget), delete it, and add Elementor's native Image widget in its place at the same aspect ratio so the layout doesn't reflow.

## Smaller inline placeholders (not full photo slots, but worth knowing about)

The new design treats most other "photo" moments as compact secondary elements — small circular avatar/headshot placeholders and logo tiles built directly into their section's markup, rather than large photography slots. They render as simple labeled circles/boxes today. Each can be swapped for a real image the same way (delete the placeholder element in Elementor, add an Image widget sized to fit):

| Where | What | Suggested shape |
|---|---|---|
| About Us — Leadership cards (3) | Headshots for the Executive Director, Board Chair, Programme Director | 66×66px circle |
| Programs — Recipient spotlight | Photo of the featured recipient | 64×64px circle |
| Scholarships — Recipient story mini-cards (2) | Photos of the two recipients | 58×58px circle |
| Home, Partnerships — Partner logo strips | Real partner organization logos (6-7 tiles each) | ~160×96px, logo on transparent/white |

## Notes

- Every photo should feature real SDI Travel Trust members/leadership/recipients once available, rather than stock imagery, for authenticity — the stock search terms above are a placeholder-fill fallback only, for launch if real photography isn't ready in time.
- Confirm model releases / usage rights for any photo of an identifiable person before publishing, especially member and recipient photos — several of the placeholder names above (recipient stories, testimonials, leadership) are themselves illustrative and need real, consented people; see `CONTENT-GAPS.md`.
- Icons throughout the site use Elementor's bundled Font Awesome icon set — a core, free part of Elementor, not a licensing concern.
- Decorative graphics (`assets/svg/route-line.svg`, `assets/svg/route-line-navy.svg`, `assets/svg/dot-grid.svg`) are original, hand-authored SVGs in brand colors — no stock/demo asset dependency.
