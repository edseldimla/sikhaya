GATE: ERROR (the copy (the page showed no <main>, <section> or <h1> to lint); consistency.mjs could not walk 1440, 390; check.mjs could not run (exit 2: check: timed out))

# Verification record

November 8, 2026. This records actual results, including limits. The visual gate was not rerun after its error, as required by the invoked design skill.

## Functional results

- **57 HTTP/database checks passed** in `tests/http-smoke.mjs` against an isolated SQLite copy and PHP server on port 8089. The production demo database was not used for test transactions.
- Buyer, Artist, and Admin login passed. All public page groups and protected account/workspace pages returned the expected responses. Unauthorized Admin access and guest cart access were blocked.
- CSRF failures were rejected. Registration could not grant the Admin role. Private commission images were denied to a guest and readable by participants. A disguised non-image upload was rejected.
- Fixed-price checkout saved a receipt, reduced stock once, and safely handled a repeated request. A changed price required review. A sold original could not be bought twice.
- Valid bids saved; low, late, and self-bids failed. Repeated deadline reconciliation produced one winner order.
- Five-day rental pricing used the 8% tier correctly. Provider approval, overlap rejection, fake payment, pickup, and return passed.
- Commission quote acceptance, fake payment, private image/message, revision counting, delivery, and completion passed. A second acceptance could not take an occupied last slot.
- Artwork post upload, reaction replacement, comments, deduplicated reposts, Admin moderation, and its history passed.
- Repeated Admin cancellation restored stock once and kept the first cancellation reason.
- SQLite foreign-key checks and order-total checks passed. All PHP files passed `php -l`; application JavaScript passed Node syntax checks.

## Demo data integrity

The real demo database has **26 tables, 15 accounts, 24 artworks, 48 listings, 24 feed posts/shares, and 9 orders** including the seeded auction winner order. It also contains comments, reactions, saved items, rental/commission examples, notifications, reports, and badge reviews.

`PRAGMA integrity_check` returned `ok`. Foreign-key violations: 0. Order total mismatches: 0. Duplicate active original-art sale channels: 0. Missing bundled media files: 0. The normal setup was run again and kept existing records.

The backup helper successfully created a consistent SQLite snapshot plus an uploads directory under `storage/backups`. Startup uses the existing PHP installation, with no dependency or software installs.

## Visual checks and limits

The taste-skill screenshot tool started but did not produce screenshots. Its Chrome diagnostic endpoint could not be reached. The finish gate's consistency walk did not visit either requested width, and the layout checker timed out. Its empty result is **not a visual pass**. This browser startup problem also occurred during asset preparation.

Therefore actual visual layout, tap targets, contrast, keyboard walk, responsive overflow, hover movement, and reduced-motion rendering were **not verified in a browser** at 1440/1024/820/768/390/320. CSS includes responsive layouts, visible focus, reduced-motion handling, larger mobile inputs, and 44px mobile save controls, but these still need an on-screen review. No screenshot or browser performance score is claimed.

The gate's copy step had no browser-rendered document to inspect. A separate HTTP request did return the complete Home HTML with main/section/headings, and that saved response was checked with the copy linter. Initial copy result: 0 FAIL, 2 WARN (two fragment headings and too many eyebrow labels). Those two copy warnings were addressed. The final separate Home copy check returned **0 FAIL, 0 WARN**.

No gate waivers were used. No kit files were edited. There are no known functional failures from the recorded checks; automated visual acceptance remains open because the browser checks could not run.

## Repository preparation check

The project was organized for a source repository after the initial build. Phase notes now live under `docs/planning/`. Machine-specific asset helpers and original generated PNGs are retained only in ignored `.local/asset-sources/`; the app uses the optimized bundled images.

`node scripts/run-tests.mjs` was run successfully after the portable setup changes: **57 checks passed** in a fresh isolated app copy and seeded database. The runner selects a free loopback port and stops its test server afterward. No package installation was needed, and the normal database was kept separate.

Relative documentation links were checked. The Git candidate list includes source, schema/seed data, optimized assets, documentation, and configuration. Runtime databases, uploads, sessions, backups, logs, browser profiles, and local helper files are excluded. The source ZIP follows that same file list; demo accounts and records are recreated by first-run setup.

## Design and content record

The client brand palette replaces the first dealt sky/magenta palette because it conflicts with the supplied fire/air/wood logo. Warm ivory, brown text, and fire-orange actions support the artwork. Offline system fonts are used. The artwork hero shows the actual demo catalog; the Home tagline uses the dealt word-highlight moment. Other functional screens keep simple layouts. The unmodified taste motion layer supplies hover/press feedback and the Home entrance.

Voice: plain, welcoming, art-focused; action labels say what happens. Financial and rental values are demo data, not business claims. Generated artwork/equipment and licensed photo stand-ins are documented in ASSET_CREDITS.md. Real group art or higher-resolution logo files can replace them later; the existing supplied logo is included with transparency. No canonical public domain, contact form delivery, or real payment connection is needed for this localhost prototype.
