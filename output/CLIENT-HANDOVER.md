# Client Handover Guide — SDI Travel Trust

A plain-language guide to the day-to-day tasks you'll actually do on this site. For anything technical (installing plugins, server setup), see `SETUP.md` instead — that one's for your developer.

---

## Approving or rejecting a referral

1. Log in to the WordPress dashboard.
2. In the left menu, go to **SDI Trust → Referrals**.
3. You'll see every referral a member has submitted, with a status: Pending, Approved, Rejected, or Duplicate.
4. For any **Pending** referral, click **Approve** or **Reject** on that row.
   - **Approve** immediately adds 30 points to that member's account and emails them the good news.
   - **Reject** asks nothing further — it just records the decision (no points are added) and emails the member.
5. To handle several at once: check the boxes next to multiple pending referrals, choose "Approve" or "Reject" from the dropdown above the list, and click Apply.

**Why can't points be awarded automatically?** This is deliberate — a fraud control. A member submitting a referral never gets points until an administrator reviews it.

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

Every page on the site (Home, About Us, Membership, Programs, Scholarships, etc.) is built in **Elementor**, WordPress's visual page builder — you edit it by clicking directly on the text or image you want to change, not by writing code.

1. Go to **Pages** in the WordPress dashboard, find the page, and click **Edit with Elementor** (or open the page normally and click the blue "Edit with Elementor" button at the top).
2. Click any text on the page to edit it directly, or click a section to see its full settings in the left panel.
3. Click **Update** (bottom left) to publish your change.

A few things to know:
- **Gold buttons are reserved for the main call-to-action on a page** (Join Now, Donate, Submit) — please don't add gold to anything else, it's what makes those buttons stand out.
- **Colors and fonts come from one central place** (Site Settings → Global Colors / Global Fonts in Elementor), so if you ever need to adjust the brand palette, changing it there updates the whole site — you shouldn't need to hand-pick a color on every page.
- Sections marked with a dashed box and label like "[PLACEHOLDER: hero background photo]" are waiting for real content — see `IMAGE-SHOT-LIST.md` and `CONTENT-GAPS.md` for the full list of what's still needed.

## Adding a directory listing

Directory listings are managed by the **Directorist** plugin, not by this custom plugin. Once Directorist is installed:

1. Go to **Directorist → Add Listing** (or however your Directorist menu is labeled).
2. Fill in the organization's details and choose one of the 7 categories (Travel Organizations, Travel Agencies & Service Providers, Travel Equipment & Accessories, Transportation & Lodging Resources, Travel Technology & Financial Services, Federal Government Agencies, State & Community Resources).
3. Publish it — it will appear in the Travel Directory automatically.

Members can also submit a listing themselves from the **Travel Directory** page's built-in submission form; those come to you as an email you can review before manually adding them in Directorist.

## Removing the demo data before launch

This build ships with sample content so every part of the site can be tested before real content is ready. Before the site goes live:

1. **Delete the demo member accounts.** In **Users**, look for accounts starting with `sdi_demo_` (8 of them, all using `@example.com` emails) and delete them. When WordPress asks what to do with their content, choose to delete it (they have no real posts).
2. **Clear demo referrals and points history.** Deleting the demo users does *not* automatically remove their rows from the Referrals or Points Ledger screens (those are intentionally permanent records). Ask your developer to run a one-time cleanup query, or simply leave them — they're clearly dated and clearly demo accounts, so they won't be confusing, but a clean launch usually removes them.
3. **Delete or unpublish the 23 demo directory listings and 4 demo news posts** the same way you'd delete or edit any other content.
4. **Replace every `[PLACEHOLDER: ...]` marker** — see `CONTENT-GAPS.md` for the complete list, organized by page.
5. **Add real photography** — see `IMAGE-SHOT-LIST.md`.

## Getting help

For anything that isn't covered here — a plugin update breaking something, a design change, a new page — that's a developer task. Keep `SETUP.md` handy for them; it has the full technical picture.
