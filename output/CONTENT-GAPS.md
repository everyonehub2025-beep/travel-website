# Content Gaps — SDI Travel Trust

This build's copy comes almost entirely from the client-supplied design system handoff, which already writes in a finished, publication-ready voice — so very little of it is a blank `[PLACEHOLDER: ...]` marker anymore. Instead, most of what's listed here is **illustrative data the design itself flags as unconfirmed** (it says so directly, in small print, on the pages that use it — e.g. "Figures shown are placeholders pending verified totals"). This file collects every one of those spots in one place, plus the small number of true `[PLACEHOLDER: ...]` markers that remain, so nothing gets missed before launch.

**Nothing below is broken or wrong to launch with as-is for a staging/review site** — it's real, specific, plausible content, exactly the kind a design system uses to show what a page looks like with real content in it. It becomes a problem only if it goes live without being checked against the truth.

---

## Highest priority — real business facts, not design placeholders

These appear on nearly every page (footer, contact panels) and should be the first things confirmed:

| Item | Current value | Where it's set |
|---|---|---|
| Mailing address | `[PLACEHOLDER: mailing address]` (design's own example: "4110 Compass Point Drive, Suite 220, Charlotte, NC 28202" — not used as the live default, see note below) | Customizer → SDI Site Info → Mailing address |
| Main contact email | `[PLACEHOLDER: contact email]` | Customizer → SDI Site Info → Contact email |
| Contact phone | `[PLACEHOLDER: contact phone]` | Customizer → SDI Site Info → Contact phone |
| EIN / 501(c)(3) status | `[PLACEHOLDER: EIN]` (design's own example: "83-1902744") | Customizer → SDI Site Info → 501(c)(3) EIN |
| Department routing emails (Membership, Scholarships, Partnerships, Giving) | Each `[PLACEHOLDER: ... team email]` | Customizer → SDI Site Info → Contact routing — * |

**Note:** the design handoff's own footer and contact panels display a specific-looking address, EIN, and set of `@sditraveltrust.org` emails as its illustrative content. This build deliberately does **not** hard-code those as real defaults anywhere — per the standing rule against inventing client-facing facts, every one of the fields above defaults to a visible `[PLACEHOLDER: ...]` string until you enter the real value in the Customizer. If the example values above are in fact correct, simply enter them in Site Info.

## Statistics shown across the site

Every one of these appears with the design's own "placeholder pending [verified data / audited figures / confirmation]" disclaimer already printed next to it on the page — replace the number, and it's safe to remove that disclaimer sentence too.

| Stat | Shown as | Appears on |
|---|---|---|
| Active members | 2,480+ | Home, About Us, Partnerships |
| Scholarships awarded to date | 86 | Home, About Us, Scholarships |
| Directory listings | 340+ | Home, About Us |
| Partner organizations | 52 | Home, About Us, Partnerships |
| Annual renewal rate / avg. years held / % who refer | 84% / 2.6 / 61% | Home |
| Allocation split (scholarship / programs / operations) | 62¢ / 24¢ / 14¢ | Home, About Us, Fundraising |
| Award levels / cycles per year | 4 / 2 | Scholarships |
| Scoring weights (need / academic / statement / community) | 35% / 25% / 25% / 15% | Scholarships |
| 2026 spring cycle results | 11 awards, $74k disbursed | News & Resources, Home |
| Board size / reviews per year / paid board seats | 5 / 4 / 0 | About Us |

## Names and bios (need real people or explicit removal)

- **Leadership** (About Us): Marlene Ashford (Executive Director), Dominic Reyes (Board Chair), Priya Chandrasekar (Programme Director) — names, titles and bios are the design's placeholder people, with headshot circles left as unlabeled placeholders (not photos).
- **Member testimonials** (Home): "Danielle P.", "Ray O.", "Amara K." with quotes — the page already prints "Sample member quotes — to be replaced with consented testimonials before launch."
- **Recipient stories** (Scholarships): "Jasmine Cole", "Marcus Ihejirika", "Elena Vasquez" with award amounts and quotes — the page already prints its own placeholder disclaimer.
- **Sample events** (Programs): an Austin, TX meetup and a Charlotte, NC volunteer day with specific 2026 dates — real if already scheduled, otherwise replace before launch.

## Sample partner/offer data (never presented as confirmed)

- **23 demo Travel Directory listings** import via the WXR (organization names only, no real websites/contacts/descriptions) — for layout testing. Delete or replace before launch; see `CLIENT-HANDOVER.md`.
- **Sample partner offers** referenced in the design (PackRight Luggage Co., LodgeLine Extended Stay, Meridian Journeys) appear only as static illustrative copy on the Fundraising page — the actual Member Dashboard "Partner Offers" tab correctly shows an honest empty state until real offers exist (see `SETUP.md` §9), so there's no risk of these sample names leaking into a live member's dashboard.
- **Partner logo strips** (Home, Partnerships) are unlabeled placeholder tiles, not fabricated logos.

## True `[PLACEHOLDER: ...]` markers remaining in code/content

- 4 demo News & Resources posts each carry one bracketed placeholder line for a detail not in the design handoff (e.g. "the specific organizations added this cycle," "confirmed autumn volunteer day dates") — cosmetic, demo-only content; delete these posts before launch per `CLIENT-HANDOVER.md`.
- The Sign Up page's "Please note" panel explicitly states payment collection is not yet connected — this is accurate, not a gap to fill in copy, but the underlying integration gap is real; see `SETUP.md` §9.

## The Legal page — an important exception to all of the above

Unlike everything else on this list, the single `/legal/` page's eight policy sections (Privacy Policy, Terms of Use, Membership Terms, Referral Program Rules, Scholarship Terms, Donation & Refund Policy, Partner Disclosures, Financial Transparency) are **not placeholder skeletons** — they're a complete, substantive first draft of real policy language, carried over verbatim from the design handoff. Every section is labeled "Draft" directly on the page, and the page's own sidebar and footer state plainly that these are pending legal, tax, and charitable-solicitation review. **This page must go through that review before the "Draft" label is removed or the site is represented as launched with final terms.**

## What's genuinely new since the last build (not a gap, but worth knowing)

- The site no longer has separate pages for the Member Referral Program and Community Engagement — both run as sections directly on the single Programs page, matching the current design exactly.
- Legal content moved from 8 separate pages to one page with 8 anchored sections (`/legal/#privacy`, `/legal/#terms`, etc.) — every internal link across the site already points to the new anchors.
- The Travel Directory's 7 categories became 8 (added "State Government Agencies" as its own category, split from the old "State & Community Resources").
