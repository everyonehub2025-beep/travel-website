# Client Handover Guide — SDI Travel Trust

A plain-language guide to the day-to-day tasks you'll actually do on this site. For anything technical (installing plugins, server setup), see `SETUP.md` instead — that one's for your developer.

---

## Approving or rejecting a referral

1. Log in to the WordPress dashboard.
2. In the left menu, go to **SDI Trust → Referrals**.
3. You'll see every referral a member has submitted, with a status: Pending, Approved, Rejected, or Duplicate. This includes referrals a new member's own sign-up filed automatically when they arrived via someone else's referral link.
4. For any **Pending** referral, click **Approve** or **Reject** on that row.
   - **Approve** immediately adds 30 points to that member's account and emails them the good news.
   - **Reject** asks nothing further — it just records the decision (no points are added) and emails the member.
5. To handle several at once: check the boxes next to multiple pending referrals, choose "Approve" or "Reject" from the dropdown above the list, and click Apply.

**Why can't points be awarded automatically?** This is deliberate — a fraud control. A referral never earns points until an administrator reviews it, even one filed automatically by a verified sign-up.

## Activating a new member's account

Sign-up (`/sign-up/`) creates the account and activates it as soon as the member clicks the verification link in their email — no admin step needed for that part. What **does** need an admin, because there's no payment processor connected yet (see `SETUP.md` §9), is confirming that dues were actually paid:

1. Go to **Users**, find the member, and open their profile.
2. Scroll to the **SDI Membership** section (only shown for accounts with the member role).
3. Set **Status** to Active/Lapsed/Cancelled and **Tier** to Individual/Family as appropriate, then save.

Until a payment processor is wired in, treat a new sign-up as "pending payment" and flip Status to Active once you've collected dues by whatever means you're currently using (invoice, phone, etc.).

## Adjusting a member's points manually

Sometimes you'll need to correct a member's balance by hand — a data-entry fix, a goodwill adjustment, or reversing an error.

1. Go to **SDI Trust → Points Ledger**.
2. Under "Manual Adjustment," choose the member, enter the amount (a positive number to add points, a **negative** number to deduct them), and write a short note explaining why.
3. Click **Record Entry**.

A note is required every time — this isn't optional, and it's what lets you (or anyone else) look back at the ledger later and understand why a balance changed. Every entry, automatic or manual, stays in the ledger permanently; nothing is ever deleted or overwritten, so the history is always trustworthy.

## Checking where members stand

**SDI Trust → Members & Tiers** lists every member with their current points balance and scholarship eligibility level. Click **Export CSV** at the top to download the whole list as a spreadsheet — handy for board reports or a scholarship committee meeting.

**SDI Trust → Dashboard** gives you the bird's-eye view: how many referrals are waiting on you right now, total points issued to date, and how many members are at each eligibility level.

## Editing a page

Every content page on the site (Home, About Us, Membership, Programs, Scholarships, Fundraising, Partnerships, Contact) is built in **Elementor**, WordPress's visual page builder — you edit it by clicking directly on the text or image you want to change, not by writing code.

1. Go to **Pages** in the WordPress dashboard, find the page, and click **Edit with Elementor** (or open the page normally and click the blue "Edit with Elementor" button at the top).
2. Click any text on the page to edit it directly, or click a section to see its full settings in the left panel.
3. Click **Update** (bottom left) to publish your change.

A few things to know:
- **Gold buttons are reserved for the main call-to-action on a page** (Join Now, Become a Member, Support Our Mission) — please don't add gold to anything else, it's what makes those buttons stand out.
- **Colors and fonts come from one central place** (Site Settings → Global Colors / Global Fonts in Elementor), so if you ever need to adjust the brand palette, changing it there updates the whole site — you shouldn't need to hand-pick a color on every page.
- **Three pages are not Elementor pages** — Member Login, Sign Up, and Member Dashboard are real functional systems (account creation, sign-in, the points/referral dashboard), built as PHP templates rather than Elementor content, because they need actual account logic Elementor can't provide. Don't switch their page template in the editor or the login/dashboard system stops working.
- **The Legal page's eight sections aren't final** — they're a complete first draft of real policy language, but every section says "Draft" and needs legal review before that label comes off. See `SETUP.md` §9.

## Managing the Travel Directory

Directory listings are a native part of this plugin now — no separate directory plugin required.

1. Go to **Travel Directory** in the left menu (it has its own icon, like Pages or Posts) → **Add New Listing**.
2. Fill in the title (organization name), a description in the content area, and choose one of the 8 categories in the sidebar.
3. In the **Listing Details** box, fill in website, contact email, phone, location, and check "Member-only listing" if it should only show full details to active members.
4. Publish it — it appears in the Travel Directory page's search automatically.

Members can also submit a listing themselves from the **Travel Directory** page's built-in submission form. That still emails you, **and** now automatically creates a draft listing pre-filled with what they submitted — open **Travel Directory → All Listings**, find the draft, fill in anything missing, and publish.

## Reading Contact page messages

The Contact page routes each message to a department mailbox based on the topic the visitor picked (Membership, Scholarships, Directory, Partnerships, Giving, Press) — each one is a separate email address you set in Appearance → Customize → SDI Site Info. If those aren't filled in yet, everything falls back to the site's main contact email or the WordPress admin email, so nothing gets lost — but visitors won't reach the right team until you fill in the department addresses.

## Removing the demo data before launch

This build ships with sample content so every part of the site can be tested before real content is ready. Before the site goes live:

1. **Delete the demo member accounts.** In **Users**, look for accounts starting with `sdi_demo_` (8 of them, all using `@example.com` emails) and delete them. When WordPress asks what to do with their content, choose to delete it (they have no real posts).
2. **Clear demo referrals and points history.** Deleting the demo users does *not* automatically remove their rows from the Referrals or Points Ledger screens (those are intentionally permanent records). Ask your developer to run a one-time cleanup query, or simply leave them — they're clearly dated and clearly demo accounts, so they won't be confusing, but a clean launch usually removes them.
3. **Delete or unpublish the 23 demo Travel Directory listings and 4 demo news posts** the same way you'd delete or edit any other content.
4. **Replace every `[PLACEHOLDER: ...]` marker and every illustrative statistic/name** — see `CONTENT-GAPS.md` for the complete list, organized by page.
5. **Add real photography** for the homepage hero — see `IMAGE-SHOT-LIST.md`.
6. **Get the Legal page reviewed by counsel** before removing its "Draft" label.
7. **Deactivate the One Click Demo Import plugin** (Plugins). It's only needed for the one-time "Appearance → Import Demo Data" step right after activation — safe to switch off once the real content is in place. (Leave it installed if you might ever need to re-import onto a fresh staging site.)

## Getting help

For anything that isn't covered here — a plugin update breaking something, a design change, a new page, connecting a payment processor — that's a developer task. Keep `SETUP.md` handy for them; it has the full technical picture.
