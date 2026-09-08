# SETUP — SDI Travel Trust

## ⚠️ Verification status — read this first

**None of this has been executed against a live WordPress install.** It was written and validated the ways that are possible without a running WordPress: every PHP file passes `php -l` (no syntax errors), the WXR file is well-formed XML and every `_elementor_data` payload inside it parses as valid JSON, and the file/class structure was checked for naming collisions against the base theme package. It has **not** been activated, clicked through, or visually verified in a browser. Treat the first import as a rehearsal, not a known-good deployment — budget time for it to surface fixes, the way any first import does. Do not represent this build to a client as tested until someone has done exactly that on a staging site.

---

## 0. Troubleshooting a "broken" first import

If pages look narrow/off-center, the admin screens look unstyled, or the menus/footer are missing right after import, check these **in order** — they account for nearly every "looks bad" symptom on a fresh import:

1. **Elementor's "Container" experiment must be Active.** Go to Elementor → Settings → Experiments and confirm **Container** (or **Flexbox Container**, depending on your Elementor version) is set to **Active**, not "Inactive" or "Default" pointing at the legacy Section/Column engine. Every page in `sdi-demo-content.xml` is built entirely from the modern `elType: "container"` structure — on an Elementor install where this experiment is off, that data won't render or edit correctly. This is the single most likely cause of a badly broken layout. (Elementor versions from roughly 2023 onward ship this Active by default for new sites, but an existing install may still have it off.)
2. **The 3 manual settings in §4 below** (menu locations, static front page, permalinks) — none of these happen automatically on WXR import; skipping them looks exactly like "no menu / no footer / wrong homepage."
3. **Clear any page cache / object cache** (and Elementor's own CSS cache: Elementor → Tools → Regenerate CSS) after import — a stale cache can serve pre-import broken output even after the real issue is fixed.
4. **Confirm the theme's CSS files are actually loading** — view page source and confirm `wp-content/themes/sdi-travel/assets/css/*.css` requests return 200, not 404. A theme installed by uploading the zip's *inner* folder incorrectly nested (e.g. `sdi-travel-theme/sdi-travel/` instead of `sdi-travel/` directly under `/wp-content/themes/`) will 404 every asset and look completely unstyled — if that happened, move the `sdi-travel` folder up one level so `style.css` sits directly inside `/wp-content/themes/sdi-travel/`.
5. **Every generated page container now sets an explicit `content_width: full`** so this build's own CSS (`.sdi-container`, `.sdi-section`) is the sole source of page width — this was tightened after an earlier round specifically to rule out Elementor's Kit-level default container width (usually "boxed," ~1140px) nesting inside this theme's own max-width wrapper and producing a double-boxed, too-narrow page. If a page still looks unexpectedly narrow after all of the above, that's a real bug — screenshot it and it needs a code fix, not a settings fix.

---

## 1. Install order

Follow this order exactly — each step depends on the one before it.

1. **WordPress** — a fresh install, PHP 8.0+, WordPress 6.4+.
2. **Theme** — install and activate `sdi-travel-theme.zip` (Appearance → Themes → Add New → Upload Theme).
3. **Required plugins** — install and activate everything in the table below, in the order listed.
4. **SDI Trust Core** — install and activate `sdi-trust-core.zip` (Plugins → Add New → Upload Plugin). Activating creates its two database tables and adds the `sdi_manage_referrals` / `sdi_manage_points` / `sdi_manage_settings` capabilities to the Administrator role.
5. **Content import** — Tools → Import → WordPress → upload `sdi-demo-content.xml`. When prompted, **assign the imported content to your admin user** and check "Download and import file attachments" (there are none, but leaving it checked is harmless).
6. **Demo members & referrals** (optional, for testing only) — via WP-CLI: `wp eval-file sdi-demo-content-seed.php`. Skip this on a production launch; see §7 below.
7. **Post-import configuration** — §4 below.
8. **Settings** — SDI Trust menu → Settings; Customizer → SDI Site Info.

## 2. Required plugins

| Plugin | Why | License needed |
|---|---|---|
| **Elementor** (free) | Page builder every page is built in. | Free — no paid tier required for anything in this build. |
| **MemberPress** | Membership sign-up, billing, and the Individual/Family plans. The plugin's membership bridge (`class-sdi-membership.php`) is wired to MemberPress's API but degrades gracefully if it's absent. | Paid — any tier that supports two membership levels + recurring billing (Basic tier and up). |
| **Directorist** | The Travel Directory's actual search/filter/listing engine. This build links into it and seeds 23 demo listings across its `at_biz_dir` post type, but never modifies its templates, per the project's integration rules. | Free tier works for a single directory; check whether the 7 custom categories and any member-only-listing visibility rule need the paid tier. |
| **Charitable** | Donation processing + the Member Portal's Donations history panel (`SDI_Public::get_donation_history()` reads it read-only). | Free tier is enough for basic donations; a paid add-on may be worth it for recurring donations. |
| **GiveWP** *(optional — see note)* | The base Careox theme package's own donation UI (9 donation-post layouts, 4 donation-form layouts) is built for GiveWP, not Charitable. This build does **not** use those theme-bundled donation widgets — it uses plain buttons/links to a donation page instead, so GiveWP is not required. If you'd rather use the theme's existing donation section designs, install GiveWP instead of (or alongside) Charitable and adapt the Fundraising/homepage donation buttons to point at a GiveWP form. | Free, unless a paid GiveWP add-on is wanted. |

**Not required**, despite being in the base theme package's own recommended-plugins list: WooCommerce, Woo Smart Wishlist, Woo Smart Quick View (no e-commerce catalog on this site), Contact Form 7 (all forms are built natively — see §5), CMB2 + its two helper plugins (only needed by the original Careox theme's own option panels, which this build doesn't use), Breadcrumb NavXT, One Click Demo Import (useful for re-importing the WXR through a UI instead of Tools → Import, but not required).

## 3. Elementor note

This is a genuinely custom, original theme — it does **not** ship or depend on the base Careox theme package's `careox-addon` plugin or its ~60 custom Elementor widgets. Every page in `sdi-demo-content.xml` is built entirely from Elementor's own free/core widgets (Container, Heading, Text Editor, Button, Icon Box, Counter, Testimonial, Accordion, Divider, HTML, Shortcode) plus this theme's CSS classes for section chrome. That means:

- No `careox-addon` plugin install needed, ever.
- Every page stays editable with nothing but free Elementor — no Elementor Pro dependency either.
- The tradeoff: header and footer are plain, Customizer-editable PHP templates (`header.php` / `footer.php`), not Elementor Theme Builder templates — replicating the base package's proprietary header/footer/megamenu CPT system would need Elementor Pro's Theme Builder, which wasn't confirmed as licensed for this project. If Elementor Pro gets added later, the header/footer can be rebuilt as Elementor templates; until then, edit them via the WordPress Customizer ("SDI Site Info" panel) and the four registered nav menus.

## 4. Post-install configuration, step by step

1. **Menus** (Appearance → Menus) — the import creates 4 menus (Primary Navigation, Footer — Quick Links, Footer — Programs, Footer — Legal) but WordPress's importer does not auto-assign menu *locations*. Assign each imported menu to its matching theme location: Primary Navigation → Primary, and the three footer menus → their matching footer locations.
2. **Reading Settings** (Settings → Reading) — set "Your homepage displays" to *A static page*, Homepage → **Home**, Posts page → **News & Resources**.
3. **Permalinks** (Settings → Permalinks) — click Save once (even without changing anything) to flush rewrite rules, needed for the custom post types Directorist/MemberPress/Charitable register.
4. **Logo & favicon** — Appearance → Customize → Site Identity. Upload the SDI logo as the Custom Logo (also powers the header, footer, and login screen automatically) and a Site Icon for the favicon.
5. **SDI Site Info** (Appearance → Customize → SDI Site Info) — fill in the footer blurb, contact email/phone, mailing address, and social links. These currently show as `[PLACEHOLDER: ...]` text.
6. **Sync Brand Kit** (Appearance → Sync Brand Kit) — runs automatically once on first admin page load after activation, but if the Elementor Global Kit colors/fonts don't look right in Site Settings → Global Colors / Global Fonts, click "Sync Now" here to re-push them. **This has not been verified against a live Elementor install** — Elementor's internal kit-settings schema is stable but not a formally documented public API; spot-check this after import and adjust by hand in Elementor's Site Settings if anything didn't take.
7. **SDI Trust settings** (SDI Trust → Settings) — confirm points-per-referral (30), tier thresholds (300/600/900/1,200 → $3k/$6k/$9k/$12k), notification recipient email, and MemberPress product ID mapping once your Individual/Family MemberPress products exist.
8. **Membership product IDs** — after creating the Individual ($180/yr) and Family ($300/yr) products in MemberPress, copy their product IDs into SDI Trust → Settings → Membership Bridge, so `[sdi_member_dashboard]` can tell members' plans apart.
9. **Directorist categories** — the 7 directory categories referenced by the WXR (Travel Organizations, Travel Agencies & Service Providers, Travel Equipment & Accessories, Transportation & Lodging Resources, Travel Technology & Financial Services, Federal Government Agencies, State & Community Resources) need to exist as Directorist categories before importing the 23 demo listings, or import the WXR again after Directorist is active if the listings were skipped the first time (see §7).
10. **Member Login link** — the header's "Member Login" link points at `wp-login.php` by default. If MemberPress's own login page is preferred, hook the `sdi_member_login_url` filter (see `header.php`) to point at it instead.

## 5. Forms — a deliberate simplification

The base theme package recommends Contact Form 7 for every form on the site. This build doesn't use it — instead, `inc/class-sdi-forms.php` implements Contact, Partnership Inquiry, Directory Submission, and Scholarship Interest as one native shortcode (`[sdi_inquiry_form type="..."]`), reusing the same nonce + honeypot pattern as the newsletter signup form. One fewer plugin to license and keep patched. The tradeoff: no conditional field logic, no file uploads, no built-in spam scoring beyond the honeypot. If the client later needs a scholarship application with document uploads, that's the point to add a dedicated forms plugin (Gravity Forms, WPForms) rather than extending this system.

## 6. How to replace every placeholder image

See `IMAGE-SHOT-LIST.md` for the full list (9 slots) with dimensions and suggested stock search terms. Short version: every placeholder is an Elementor HTML widget rendering a `.sdi-placeholder` block. In Elementor, delete the HTML widget and add an Image widget in its place, matching the aspect ratio listed so the layout doesn't reflow.

## 7. Known limitations

- **Demo members/referrals aren't in the WXR.** WordPress's WXR format can't create real user accounts (no password data) or rows in this plugin's custom database tables. `sdi-demo-content-seed.php` creates them separately by calling the plugin's own APIs — run it via `wp eval-file sdi-demo-content-seed.php` **after** SDI Trust Core is active. It's idempotent (checks an option flag) and creates 8 clearly-fake `sdi_demo_*` / `@example.com` accounts. Delete them before launch — see `CLIENT-HANDOVER.md`.
- **Directory listings depend on Directorist being active first.** The WXR's 23 `at_biz_dir` items will be silently skipped by the WordPress Importer if Directorist isn't installed yet (the post type doesn't exist for it to import into). If you imported the WXR before installing Directorist, re-run the import afterward — WordPress's importer skips items that already exist, so this is safe to do twice.
- **The Elementor Kit sync is unverified.** See §4, step 6.
- **"Testimonials" demo content lives inline, not in a CPT.** The brief's "4-5 testimonials" are the 3 homepage member-story placeholders + 2 Scholarships-page recipient-story placeholders, hand-authored directly into those pages' Elementor content rather than a separate Testimonials custom post type (this theme deliberately doesn't register one, to avoid CPT bloat the site doesn't otherwise need). If a dynamic, reusable testimonials CPT is wanted later (e.g. to pull the same quotes into multiple pages), that's a small, contained addition.
- **The fintech/eligible-purchase integration is a stub, on purpose.** `SDI_Fintech_Null_Provider` connects to nothing — the partner and their API are undefined per the brief. The Settings screen shows "Integration pending — Phase 2." A real provider drops in later via the `sdi_fintech_provider` filter without touching core plugin files.
- **No demo photography ships anywhere.** See `PHASE0-AUDIT.md` for why (the base package's bundled images are licensed for its own demo preview, not confirmed for redistribution) and `IMAGE-SHOT-LIST.md` for what to shoot or source instead.
- **Legal page copy is a structural skeleton, not real policy text.** See `CONTENT-GAPS.md`. These 8 pages must go through legal review before publishing, per the brief's own instruction.

## 8. What still needs the client's input before launch

1. The actual SDI Travel Trust logo and favicon (none were supplied to this build — see `PHASE0-AUDIT.md`).
2. Every item in `CONTENT-GAPS.md` (126 placeholders): contact details, leadership names/bios/headshots, verified community-impact statistics, member/recipient testimonial quotes and photos, and — most importantly — all 8 legal pages' substantive text after legal review.
3. A decision on the GiveWP-vs-Charitable donation question (§2) if the theme's own bundled donation section designs are wanted instead of the simpler button/link approach this build uses.
4. Real photography for the 9 image slots in `IMAGE-SHOT-LIST.md`, or sign-off on stock alternatives.
5. Confirmation of the fintech/debit-card partner, whenever that partnership is finalized, so Phase 2 of the fintech module can be built against a real API.
