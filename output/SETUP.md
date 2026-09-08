# SETUP — SDI Travel Trust

## ⚠️ Verification status — read this first

This build has now been uploaded and activated on a live WordPress install once, which surfaced a real bug (Elementor's own container CSS was winning a specificity tie against this theme's grid layout classes, collapsing multi-column sections to a single stacked column and stretching the hero image placeholder) — that's now fixed (`!important` added to the grid rules in `assets/css/base.css`; see §0 point 6). Everything else below was written and validated the ways that are possible without a full click-through: every PHP file passes `php -l` (no syntax errors), the WXR file is well-formed XML and every `_elementor_data` payload inside it parses as valid JSON, and the CSS/JS/PHP file structure was checked for naming collisions.

**The guided "Install Required Plugins → Import Demo Data" flow (§1) is new and has not yet been clicked through on a live install** — TGM Plugin Activation and One Click Demo Import are both mature, widely-used libraries (the same mechanism most premium themes use), and the integration code follows their standard, documented hooks, but this specific wiring (which plugins are required, which file gets imported, what the after-import step configures) hasn't been visually verified end-to-end yet. Budget time for the first real run-through to surface anything that needs adjusting, and don't represent this build to a client as tested until someone has done exactly that on a staging site.

---

## 0. Troubleshooting a "broken" first import

If pages look narrow/off-center, the admin screens look unstyled, or the menus/footer are missing right after import, check these **in order** — they account for nearly every "looks bad" symptom on a fresh import:

1. **Elementor's "Container" experiment must be Active.** Go to Elementor → Settings → Experiments and confirm **Container** (or **Flexbox Container**, depending on your Elementor version) is set to **Active**, not "Inactive" or "Default" pointing at the legacy Section/Column engine. Every page in `sdi-demo-content.xml` is built entirely from the modern `elType: "container"` structure — on an Elementor install where this experiment is off, that data won't render or edit correctly. This is the single most likely cause of a badly broken layout. (Elementor versions from roughly 2023 onward ship this Active by default for new sites, but an existing install may still have it off.)
2. **If you imported manually via Tools → Import instead of Appearance → Import Demo Data**, the menu locations, static front page, and permalinks are not assigned automatically — do §4 below by hand. (The guided **Import Demo Data** flow in §1 does all of this for you; use it unless you have a specific reason not to.)
3. **Clear any page cache / object cache** (and Elementor's own CSS cache: Elementor → Tools → Regenerate CSS) after import — a stale cache can serve pre-import broken output even after the real issue is fixed.
4. **Confirm the theme's CSS files are actually loading** — view page source and confirm `wp-content/themes/sdi-travel/assets/css/*.css` requests return 200, not 404. A theme installed by uploading the zip's *inner* folder incorrectly nested (e.g. `sdi-travel-theme/sdi-travel/` instead of `sdi-travel/` directly under `/wp-content/themes/`) will 404 every asset and look completely unstyled — if that happened, move the `sdi-travel` folder up one level so `style.css` sits directly inside `/wp-content/themes/sdi-travel/`.
5. **Every generated page container sets an explicit `content_width: full`** so this build's own CSS (`.sdi-container`, `.sdi-section`) is the sole source of page width — this rules out Elementor's Kit-level default container width (usually "boxed," ~1140px) nesting inside this theme's own max-width wrapper and producing a double-boxed, too-narrow page.
6. **Grid layouts (pricing cards, category tiles, stat rows, etc.) collapsing into a single stacked column, or an image placeholder stretching far taller/wider than intended** — this was a real bug in earlier builds: Elementor's own frontend CSS sets `display: flex` on every container's `.e-con` class at the same specificity as this theme's `.sdi-grid { display: grid }` utility class, so the winner depended on stylesheet load order. Fixed by making the grid-defining rules in `assets/css/base.css` (`.sdi-grid`, `.sdi-grid--2/3/4/7`) `!important`, so they always win regardless of load order. If you're on a build older than this fix, re-download the current `sdi-travel-theme.zip` and re-upload the theme.
7. If a page still looks unexpectedly narrow, unstyled, or broken after all of the above, that's a real bug — screenshot it, it needs a code fix, not a settings fix.

---

## 1. Install order — guided, like a premium theme

This theme now bundles the same "install required plugins → import demo data" flow used by premium themes (Astra, Avada, OceanWP, etc.), via the standard **TGM Plugin Activation** library and the free **One Click Demo Import** plugin — no manual XML upload needed for a normal install.

1. **WordPress** — a fresh install, PHP 8.0+, WordPress 6.4+.
2. **Theme** — install and activate `sdi-travel-theme.zip` (Appearance → Themes → Add New → Upload Theme). Activation immediately shows an admin notice: *"SDI Travel Trust requires the following plugins: Elementor, One Click Demo Import."*
3. **Click through that notice** — "Begin installing plugins" bulk-installs and activates both. (If your host requires FTP credentials for plugin installs, WordPress will prompt for them here — a shared-host quirk, not specific to this theme.)
4. **Appearance → Import Demo Data** appears once both plugins are active. Click it, then click **Import Demo Data** on the "SDI Travel Trust — Complete Site" card. This single click:
   - Imports all 14 pages (Home, About Us, Membership, Travel Directory, Programs, Scholarships, Fundraising, Partnerships, News & Resources, Contact, Legal, plus the Member Login / Sign Up / Member Dashboard system pages), the header, footer, and all 3 navigation menus — fully laid out and Elementor-editable, exactly like the design.
   - Assigns all 3 menu locations (Primary, Footer — Organization, Footer — Policies) automatically.
   - Sets Home as the static front page and News & Resources as the posts page automatically.
   - Flushes permalinks automatically.
   - Creates sample Travel Directory listings and news posts for testing (delete before launch — see `CLIENT-HANDOVER.md`).
5. **SDI Trust Core** — install and activate `sdi-trust-core.zip` (Plugins → Add New → Upload Plugin). Activating creates its two database tables (points ledger, referrals), registers the `sdi_member` role and the Travel Directory custom post type, and adds the `sdi_manage_referrals` / `sdi_manage_points` / `sdi_manage_settings` capabilities to the Administrator role. (This plugin isn't part of the TGMPA/import flow above because it's this build's own custom plugin, not something installable from WordPress.org — it still has to be uploaded manually, same as the theme.)
6. **Demo members & referrals** (optional, for testing only) — via WP-CLI: `wp eval-file sdi-demo-content-seed.php`. Skip this on a production launch; see §9 below.
7. **Remaining settings** — §4 below (logo/favicon, SDI Site Info, SDI Trust settings — the parts that genuinely need a human decision and can't be automated).

**Prefer the old manual route instead?** Tools → Import → WordPress → upload `demo-data/sdi-demo-content.xml` (inside the theme folder) or `sdi-demo-content.xml` (in this deliverable) still works — you'll just need to do the menu/front-page/permalink steps in §4 by hand, same as before. Both files are identical.

## 2. Plugins — there is exactly one, and it's free

This build was rebuilt to run without any paid membership, directory, or donation plugin. Everything that used to require MemberPress, Directorist, Charitable, or GiveWP is now native to the `sdi-trust-core` plugin and the theme:

| What it used to need | What it uses now |
|---|---|
| MemberPress (membership sign-up, billing, tiers) | Native sign-up (`/sign-up/`), email verification, and login (`/member-login/`) — see §5. No payment gateway is wired up yet; see §9. |
| Directorist (Travel Directory search/listings) | A native `sdi_listing` custom post type + `[sdi_directory]` shortcode — see §6. |
| Charitable / GiveWP (donations) | A "gift intent" form (`[sdi_inquiry_form type="donation_interest"]`) that routes to the giving team — no payment processing is connected yet; see §9. |
| Contact Form 7 (every form) | Native shortcodes in `inc/class-sdi-forms.php` — see §7 (unchanged from the original build). |

**Two plugins are required, both free, both from WordPress.org:**

- **Elementor** (free tier — no Elementor Pro dependency). Every page in `sdi-demo-content.xml` is built entirely from Elementor's own free/core widgets (Container, Heading, Text Editor, Button, Icon Box, Accordion, Testimonial, HTML, Shortcode) plus this theme's CSS classes for section chrome — no `careox-addon` or any other widget-pack plugin.
- **One Click Demo Import** — powers the guided Appearance → Import Demo Data step in §1. It has no lasting effect on the live site beyond that one-time import; it's safe to deactivate afterward, or leave active in case you ever want to re-import onto a fresh install (e.g. a staging → production migration).

Both are offered automatically via the standard **TGM Plugin Activation** admin notice the moment the theme is activated — see §1. `sdi-trust-core.zip` is this build's own custom plugin (membership, points/referrals, Travel Directory) and is installed separately, the same way the theme itself is.

## 3. Header & footer

Header and footer are Customizer-editable PHP templates (`header.php` / `footer.php`), not Elementor Theme Builder templates — this avoids an Elementor Pro dependency. Edit them via Appearance → Customize → "SDI Site Info" and the three registered nav menus (Primary, Footer — Organization, Footer — Policies). If Elementor Pro is added later, the header/footer can be rebuilt as Elementor templates.

## 4. Post-install configuration, step by step

**If you used Appearance → Import Demo Data (§1), steps 1–3 below are already done for you** — menu locations, the static front page/posts page, and permalinks are all assigned automatically by the importer (`inc/demo-import.php`, hooked to `pt-ocdi/after_import`). They're listed here anyway in case you imported manually via Tools → Import instead, or need to double-check them.

1. **Menus** (Appearance → Menus) — if assigning by hand: the import creates 3 menus (Primary Navigation, Footer — Organization, Footer — Policies); assign each to its matching theme location — Primary Navigation → Primary, the two footer menus → their matching footer locations.
2. **Reading Settings** (Settings → Reading) — if assigning by hand: set "Your homepage displays" to *A static page*, Homepage → **Home**, Posts page → **News & Resources**.
3. **Permalinks** (Settings → Permalinks) — if assigning by hand: click Save once (even without changing anything) to flush rewrite rules, needed for the `sdi_listing` custom post type and the native login/sign-up/dashboard pages.
4. **Logo & favicon** — Appearance → Customize → Site Identity. The real SDI logo ships in the theme (`assets/images/`) and is already wired into the header, footer, login screen, and a generated favicon; upload it as the Custom Logo too so it also appears in the WordPress admin bar and any other core-theme touchpoint that reads the Custom Logo setting directly.
5. **SDI Site Info** (Appearance → Customize → SDI Site Info) — fill in the footer blurb, contact email/phone, mailing address, EIN, and the four department contact-routing emails (Membership, Scholarships, Partnerships, Giving) used by the Contact page's topic router. These currently show as `[PLACEHOLDER: ...]` text — see CONTENT-GAPS.md.
6. **Sync Brand Kit** (Appearance → Sync Brand Kit) — runs automatically once on first admin page load after activation, but if the Elementor Global Kit colors/fonts don't look right in Site Settings → Global Colors / Global Fonts, click "Sync Now" here to re-push them. **This has not been verified against a live Elementor install** — spot-check this after import and adjust by hand in Elementor's Site Settings if anything didn't take.
7. **SDI Trust settings** (SDI Trust → Settings) — confirm points-per-referral (30), tier thresholds (300/600/900/1,200 → $3k/$6k/$9k/$12k), and the referral-notification recipient email.
8. **Travel Directory categories** — the 8 categories (Travel Organizations, Travel Agencies & Service Providers, Travel Equipment & Accessories, Transportation & Lodging Resources, Travel Technology & Financial Services, Federal Government Agencies, State Government Agencies, Community & Nonprofit Resources) are created automatically the first time the plugin runs `init` after activation. The WXR's 23 demo listings import into them directly — no separate category setup needed.

## 5. Native membership system (sign-up, login, dashboard)

`includes/class-sdi-auth.php` implements real account creation without a membership plugin:

- **Sign Up** (`/sign-up/`) collects name, email, password, and tier (Individual $180/yr or Family $300/yr — the design's own published pricing), creates a WordPress user with the `sdi_member` role and `sdi_membership_status = pending_verification`, and emails a verification link (24-hour expiry).
- **Verification** activates the account (`sdi_membership_status = active`), logs the member in, and — if they arrived via another member's referral link (`?ref=CODE` on the Sign Up URL) — files a pending referral on that member's behalf automatically (`SDI_Auth::maybe_record_referral()`), which then goes through the same manual admin-approval fraud control as a referral submitted from the dashboard.
- **Login** (`/member-login/`) is a real `wp_signon()` form; `wp_lostpassword_url()` powers "Forgot your password?" using WordPress's own reset-email flow.
- **Member Dashboard** (`/member-dashboard/`, shortcode `[sdi_member_dashboard]`) has 4 tabs — Overview, Referrals & Points, Partner Offers, Account Settings — all reading real plugin data (points balance, tier progress, referral list, dependents, email preferences). No fabricated financial or activity figures anywhere in it; empty states say so honestly (e.g., "No gifts recorded yet").
- An admin can activate, renew, or cancel a membership by hand from that user's normal WordPress profile screen (Users → the member → "SDI Membership" section) until a payment processor is connected — see §9.

## 6. Native Travel Directory

`public/class-sdi-directory.php` registers `sdi_listing` (a standard custom post type — Add New Listing works exactly like Add New Page) and `sdi_listing_category` (its taxonomy, pre-populated with the 8 categories on activation). `[sdi_directory]` renders a client-side filtered search (keyword, category, location, member-only checkbox) over published listings — no REST endpoint or AJAX needed, so it degrades gracefully with JavaScript off. A member-only listing hides its contact details behind an active-membership check on both the search grid and the single-listing page. The Travel Directory page's submission form still emails the program team **and** now drops a draft listing for them to review/edit/publish, instead of requiring manual re-entry from the email.

## 7. Forms — a deliberate simplification

`inc/class-sdi-forms.php` implements every form on the site as one native shortcode (`[sdi_inquiry_form type="..."]`) — Contact (topic-routed to a department mailbox), Partnership Inquiry, Directory Submission, Scholarship Interest, and Donation Interest — reusing the same nonce + honeypot pattern as the newsletter signup form. One fewer plugin to license and keep patched. The tradeoff: no conditional field logic, no file uploads, no built-in spam scoring beyond the honeypot. If the client later needs a scholarship application with document uploads, that's the point to add a dedicated forms plugin (Gravity Forms, WPForms) rather than extending this system.

## 8. How to replace the placeholder photo

Only one image slot ships as a placeholder — the homepage hero photo. Every other visual element in the new design (leadership headshots, recipient photos, partner logos, avatar circles) is a small inline decorative placeholder built directly into that section's HTML, since the design itself treats those as compact secondary elements rather than full photography slots — see `IMAGE-SHOT-LIST.md` for the complete list including these.

To replace the hero photo: in Elementor, select the placeholder block on the Home page (it's an HTML widget), delete it, and add an Image widget in its place at a 9:10 aspect ratio so the layout doesn't reflow.

## 9. Known limitations — read before launch

- **No payment gateway is connected.** Sign-up (`/sign-up/`) creates a real account and activates it on email verification, but does not collect payment — membership dues are confirmed manually by the membership team (via the user's profile screen in wp-admin, which now shows Membership Status/Tier fields). The donation form on the Fundraising page and homepage similarly captures gift intent rather than processing a card. When a payment processor (Stripe, or a nonprofit-specific processor) is selected, wiring it in is the next real integration step — the account/membership data model is already built to support it (`sdi_membership_status`, `sdi_membership_tier` user meta).
- **The fintech/eligible-purchase integration is a stub, on purpose.** `SDI_Fintech_Null_Provider` connects to nothing — the partner (referred to as "TrekPay Financial" in the design's own illustrative copy) and their API are unconfirmed. A real provider drops in later via the `sdi_fintech_provider` filter without touching core plugin files.
- **Partner Offers is an empty state until offers are issued.** The Member Dashboard's Partner Offers tab reads from the `sdi_partner_offers` filter, which returns nothing by default — it never fabricates sample codes or partners. Populate it once a real fintech/offer-issuance flow exists.
- **Demo members/referrals aren't in the WXR.** WordPress's WXR format can't create real user accounts (no password data) or rows in this plugin's custom database tables. `sdi-demo-content-seed.php` creates them separately by calling the plugin's own APIs (`SDI_Auth`, `SDI_Points`, `SDI_Referrals`) — run it via `wp eval-file sdi-demo-content-seed.php` **after** SDI Trust Core is active. It's idempotent (checks an option flag) and creates 8 clearly-fake `sdi_demo_*` / `@example.com` accounts, already activated as members so the Member Dashboard renders real data for them. Delete them before launch — see `CLIENT-HANDOVER.md`.
- **The Elementor Kit sync is unverified.** See §4, step 6.
- **The guided plugin-install/demo-import flow is unverified end-to-end.** See the verification-status note at the top of this document.
- **Legal page copy is a full first draft, not final policy text.** Unlike the rest of the site, the single `/legal/` page's eight sections (Privacy, Terms, Membership Terms, Referral Program Rules, Scholarship Terms, Donation & Refund Policy, Partner Disclosures, Financial Transparency) are complete, substantive draft text carried over verbatim from the design handoff — not placeholder skeletons. They are explicitly marked "Draft" on the page itself and **must still go through legal, tax, and charitable-solicitation review before publishing**, exactly as that page's own on-page notice says.
- **Business facts throughout the site (address, EIN, department emails, member/scholarship statistics, leadership names, testimonials) come from the design handoff's own illustrative placeholder data**, not confirmed client facts — the design itself flags this in small print on every page that uses them ("placeholders pending client confirmation"). See CONTENT-GAPS.md for the complete list to verify or replace before launch.
- **The five Programs sub-pages from an earlier build no longer exist.** The new design's Programs page is a single page: the Referral Program and Community Engagement programs run directly on it (as anchored sections with a live points calculator), while Fundraising, Partnerships, and Scholarships already have their own top-level pages and are only summarized on Programs with links out. This matches the design handoff exactly.

## 10. What still needs the client's input before launch

1. Every item in `CONTENT-GAPS.md`: contact details for all departments, mailing address, EIN, leadership names/bios/headshots, verified community-impact and financial-allocation statistics, member/recipient testimonial quotes and photos, and real Travel Directory partner listings (23 demo ones ship for testing).
2. Legal review and sign-off on the `/legal/` page's eight policies before removing the "Draft" label.
3. A decision on a payment processor for membership dues and donations (§9) — this is the single largest remaining functional gap.
4. Confirmation of the fintech/debit-card partner, whenever that partnership is finalized, so Phase 2 of the fintech module can be built against a real API.
5. Real photography for the homepage hero (`IMAGE-SHOT-LIST.md`), or sign-off on stock alternatives.
