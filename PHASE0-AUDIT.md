# Phase 0 Audit — SDI Travel Trust WordPress Build

Status: **STOP — awaiting your confirmation before Phase 1 (plugin/theme build) begins**, per the brief's Section 3 instruction.

---

## 0. What was actually in the hub

Three files were uploaded, not the `/theme /logo /content /reference /output` folder layout the brief describes:

| Expected | Provided | Status |
|---|---|---|
| `/theme/` — theme package | `careox100.rar` | ✅ present, audited below |
| `/logo/` — SDI logo files (SVG/PNG, favicon variants) | *nothing* | ❌ **missing** |
| `/content/` — client copy doc, layout doc | `1250_SDI_Travel_EMAIL.docx` + `sdiTravelWebsiteLayout.pdf` | ✅ present, read in full |
| `/reference/` — project guideline `.md` | *nothing* | ❌ **missing** |
| `/output/` | — | created, empty, ready |

**Per your own instruction ("if any folder is empty or a file is missing, tell me before you start building"): no SDI logo assets and no reference guideline file exist anywhere in this session.** I have not invented or guessed at either. This blocks two concrete deliverables until you supply them or tell me to proceed without:
- Header/footer/favicon/login-screen logo placement — I can build the slots and wire them up, but cannot drop in a real mark.
- Anything the missing `/reference/` guideline might have specified that isn't already covered in the brief itself.

I extracted the archive, read the PDF and the DOCX in full, and audited the theme's actual PHP source (not just its marketing description). Findings below.

---

## 1. Theme identification

- **Theme name:** Careox — "a clean professional Non Profit Charity WordPress Theme," by **Bracket Web** (author handle Layerdrops), sold on ThemeForest.
- **Package contents:** `careox.zip` (parent theme), `careox-child.zip` (child theme, currently empty stub — just `style.css` header), `plugins/careox-addon.zip` (the widget/CPT engine, **required**, not optional), plus three small CMB2 helper plugins (`cmb2-conditionals`, `cmb-field-select2`, `cmb2-field-slider` — used for the theme's own admin option panels, not content).
- **Demo content format:** a single WXR file (`demo-data/sample-data.xml`, 4.3 MB) + `customizer.dat` + `widgets.wie`. One combined demo import, not several separate demo packages — but that one import contains **4 homepage layout variants** (Home One–Four) plus dark/RTL/boxed variants, and dozens of inner pages (About, Events, Volunteers, Testimonials, Pricing, Gallery, FAQ, Donations ×9 layouts, News, Contact, Login/Register, etc.) — 64 pages total.
- **Required plugin stack** (from the theme's own TGMPA list in `inc/plugins.php`): CMB2 + 2 helpers, **careox-addon** (bundled), **Elementor** (bundled dependency — free core, not Pro), One Click Demo Import, Contact Form 7, Breadcrumb NavXT, **WooCommerce**, **GiveWP** (`give`), Woo Smart Wishlist, Woo Smart Quick View.

## 2. Critical check — is it real Elementor data?

**Yes.** This is the best-case scenario for the section-reuse plan. Verified directly in the WXR, not assumed:

- 126 occurrences of `_elementor_data` postmeta in the demo import.
- `careox-addon` registers **~60 custom Elementor widget classes** under `includes/Widgets/*.php` (About, Hero/MainSlider, Pricing, Funfact, Testimonials, CallToAction, Features, IconBox, DonationForm, DonationPost, Team, Event, Gallery, Faq, Process, ProgressBar, ContactForm, Blog, Sponsors, Certificate, Award, Video, Counter, Tab, etc.), each with its own render template (`elementor-templates/*.php`) and its own controls file (`elementor-options/*.php`).
- Critically, individual page **sections are also saved as standalone reusable Elementor Library items** (`post_type = elementor_library`, `_elementor_template_type = container`) — e.g. "about one," "funfact one," "testimonial two," "call to action three," "pricing one," "footer one." These are Elementor's modern flex **Container** type (Elementor 3.6+), not the older Section/Column structure. This means sections genuinely are swappable, reusable building blocks in the Elementor Template Library — exactly what "fully editable, no theme-specific shortcode lock-in" requires.
- Global styling lives in an Elementor **Default Kit** (also present in the WXR) — this is where I'll wire the SDI brand colors and fonts as global Kit variables, so Elementor widgets, and Directorist once installed, inherit them from one place, per your instruction.
- The theme's own header/footer/nav are **not** hardcoded PHP templates either — they're custom post types (`header`, `footer`, `megamenu`) that are themselves edited in Elementor and assigned via the customizer. 11 header variants and 1 footer variant ship in the demo. This is how I'll build the SDI header/footer.

Nothing here is locked into theme-specific shortcodes. The section-reuse plan in the brief stands as written.

## 3. Section inventory — Required → Source Demo → Source Section → Adaptation

| Required section (Homepage, in order) | Source | Adaptation needed |
|---|---|---|
| 1. Hero — headline/subhead/2 buttons | `main-slider-one` … `four`, or `home-showcase-one/two` | **Heavy.** Demo sliders are multi-slide carousels with charity copy; SDI needs a single static hero, new copy, gold-filled + outline button pair in brand colors |
| 2. Organizational intro paragraph | `about-one` … `about-seven` (7 variants) | **Light.** Pick simplest variant, swap copy, drop any donation-specific imagery |
| 3. Membership options (2 pricing cards) | `pricing-one`, `pricing-two` | **Heavy.** Demo pricing is ticket/event pricing; needs rebuild as 2-card Individual/Family layout with the correct price points and a link to Membership |
| 4. Travel directory teaser (7-category grid) | `service-one/two/three`, `features-one/two/three`, or `icon-box-one/two` | **Heavy.** No 7-item icon-grid demo exists; will adapt a 4–6 column features/service widget to 7 items or use a 4+3 grid |
| 5. Scholarship & education explainer | `process-one` (step flow) + `counter-one/two` or `progress-bar-one` | **Built largely from scratch.** No demo section explains a points→tier→scholarship ladder; will combine the process widget with the progress-bar widget |
| 6. Fundraising opportunities (3 channels) | `icon-box-one/two` or `features-two/three` | **Moderate.** 3-column icon+text block exists; swap copy/icons for the 3 SDI channels |
| 7. Community impact stats strip | `funfact-one` … `six` (6 variants) | **Light.** Direct reuse of a stat-counter widget; numbers will be placeholder and flagged |
| 8. Partner & sponsor invitation CTA | `call-to-action-one` … `four` | **Light.** Direct reuse, swap copy/link |
| 9. Testimonials carousel | `testimonials-one` … `eight` (8 variants!) | **Light.** Direct reuse, swap copy/photos-as-placeholders |
| 10. Newsletter signup | `footer-subscribe-one` (currently a footer-only widget) | **Moderate.** Repurpose as a standalone in-page section, not just footer |
| 11. Final donation CTA "Support Our Mission" | `donation-form-one` … `four`, `donation-button`, or `call-to-action-*` | **Moderate — needs your input.** The theme's donation widgets are wired for **GiveWP**, not Charitable (see §6). Visual reuse is easy; the functional "Donate" destination depends on which plugin you confirm |

Representative mapping for other required pages (full page-by-page mapping will be finalized during the build, not re-litigated here):

| Page | Source | Adaptation |
|---|---|---|
| About Us (7 anchor subsections incl. FAQ) | `about-*`, `faq-one/two`, `history-one/two`, `team-*` (for Leadership) | Moderate — assemble one long page from several demo sections |
| Programs sub-pages (×5) | `service-details-one` (single-service template) + `icon-box`/`process` widgets | Moderate, repeated 5× with different copy |
| Scholarships (overview/eligibility/process/awards/FAQ/stories/form) | `pricing-*` (award levels), `process-one`, `testimonials-*` (recipient stories), `faq-*`, `contact-form-*` (application form) | Heavy — longest page, assembled from 5+ different demo widgets |
| Contact | `contact-form-one` … `five`, `contact-info-one`, `google-map-one/two` | Light |
| News & Resources | `blog-one` … `six` | Light — theme already has full blog/news layouts |
| Legal pages (×8) | `donation-content` (plain long-form text template) or generic page template | Light structurally; copy is entirely new legal text (built last, per your instruction) |

## 4. Sections with **no** demo equivalent — build from scratch

- 7-category travel directory grid (no 7-column demo layout exists)
- Points → tier → scholarship progress explainer (no gamification/ledger visual in a charity template)
- The entire **Member Dashboard** (`[sdi_member_dashboard]`) — tabbed Overview/Refer/Points/Scholarships/Donations/Offers/Directory/Profile UI. This is plugin-rendered PHP/JS, not an Elementor section, and has no demo analog.
- Referral submission form, points ledger table, tier progress bar shortcodes — same reason, these are plugin UI, not page-builder content
- Directory search/filter UI itself is **Directorist's** own templates once that plugin is installed — outside both the theme and the SDI plugin's scope; we only link into it

## 5. Existing PHP structure — collision check for the plugin

No collisions found. Confirmed by direct source inspection:

- **Theme (parent) function prefix:** `careox_` (e.g. `careox_setup`, `careox_scripts`) — plain functions, no namespace.
- **Theme constant:** `CAREOX_VERSION`.
- **careox-addon plugin:** namespaced `Layerdrops\Careox\...`, constants `CAREOX_ADDON_VERSION`, `CAREOX_ADDON_PATH`, etc. Widget classes live under `Layerdrops\Careox\Widgets\*`.
- **Custom post types already registered:** `header`, `footer`, `megamenu` (theme's own builder CPTs — Elementor-enabled). No `event`, `team`, `service`, or `portfolio` CPTs exist despite widgets of those names existing — those are implemented as regular Pages using custom Page Templates (`post-templates/single-*.php`), not CPTs. So there is no CPT bloat from that angle to strip.
- **Third-party CPTs already in play post-import:** `product` (WooCommerce), `give_forms`/`give_payment` (GiveWP), `wpcf7_contact_form` (Contact Form 7).

Our planned `sdi_` / `SDI_` / `SDI_TC_` prefix, `SDI Trust Core` plugin, and `{prefix}sdi_points_ledger` / `{prefix}sdi_referrals` tables are entirely clear of the above. No renaming needed.

**"Strip demo bloat" scope for Phase 1 (theme):** disable/remove WooCommerce product demo data and the two Woo Smart Wishlist/Quick-View plugins (SDI has no e-commerce catalog), decide GiveWP's fate (§6), and prune the theme down to the page variants actually used (SDI needs ~1 of the 4 home layouts, 1 of 11 headers, 1 footer, not all of them).

## 6. Decisions I need from you before Phase 1

These aren't guesses I'm willing to make silently — flagging per your "tell me before you start" instruction:

1. **Logo & favicon assets** — none provided. Send SVG/PNG + favicon variants, or tell me to build styled placeholder wordmark blocks in brand navy/gold instead (I can start that way and swap later).
2. **Reference guideline file** — the brief references a `/reference/` project guideline `.md` that wasn't uploaded. If it contains requirements beyond this brief, I need it before I can honor it; otherwise I'll proceed on the brief alone.
3. **Donation processor mismatch** — the brief's plugin spec says the Donations dashboard panel reads history from **Charitable**, but the theme's entire donation widget library (9 donation-post layouts, 4 donation-form layouts) is built against **GiveWP**, and GiveWP (not Charitable) is in the theme's own required-plugins list. Options: (a) keep the brief's Charitable requirement and I rebuild the donation-facing sections generically/plugin-agnostically rather than using the theme's GiveWP-bound widgets, or (b) switch the brief to GiveWP so the theme's existing donation sections can be reused directly with far less adaptation work. I'd lean toward (b) given how much of the theme's demo content is GiveWP-specific, but it's your call.
4. **Demo photo licensing** — `Licensing/README_License.txt` inside the package states plainly: the PHP/HTML code is GPL, but "all other parts, **including images**" are licensed only under whatever ThemeForest license tier you purchased, separately from the code. I have no confirmation these demo photographs (people, real-world scenes) are cleared for redistribution in a live client deliverable outside the theme's own demo preview. Per your hard constraint ("do not ship demo images you can't confirm are licensed for redistribution"), my plan is: reuse the theme's non-photographic design assets freely (gradient/shape PNGs, icon sets — these are the theme's own delivered design system, not third-party stock), but treat every photographic demo image as off-limits and replace with a styled placeholder block instead, per Section 4 of the brief. Flag if you have the extended license or a different read on this.
5. **MemberPress / Directorist / Charitable(or GiveWP)** — none of these are bundled in the theme package or mentioned anywhere in its source. They're entirely your responsibility to license/install separately; the plugin will be built to degrade gracefully if they're absent, as specified. Noting this now so it's not a surprise at handover — `SETUP.md` will spell out exactly which plugin and which license tier each integration needs.

## 7. Working-copy note

The extracted theme package (~40MB unpacked) is kept in the local session working directory (`/theme/careox-100/`) for build reference but is **excluded from git** (see `.gitignore`) — it's a large third-party binary bundle whose images/design are separately licensed from its GPL code, so I'm not committing the raw vendor package into version control. Only original SDI work (plugin code, rebuilt theme files, docs, and the final packaged zips) will be committed and land in `/output/`. Say the word if you'd rather have the raw vendor package committed too.

---

**Stopping here per Section 3 of the brief.** Confirm to proceed to Phase 1 (plugin core: schema, points, referrals, tiers), and let me know your calls on items 1–4 above — in particular the Charitable-vs-GiveWP decision, since it changes how much of the theme's existing donation UI I reuse versus build fresh.
