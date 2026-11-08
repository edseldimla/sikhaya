Kind: Filipino art marketplace and community website; a working local school prototype.

# SIKHAYA - Phase 1 Plan

Prepared: November 8, 2026. Course: The Entrepreneurial Mind.

**Planning only.** Phase 1 creates this document only. All pages, tables, folders, rules, and commands below describe future work. No website, app source, persistent database, server, or software package was created or installed.

**Location:** `C:\Users\User\Downloads\sikhaya\PHASE1_PLAN.md`. Windows denied creating a folder directly under `C:\Users\User`, so this new sikhaya folder is beside the supplied PDF in Downloads. No permissions were changed. Use this actual project path for later phases.

## 1. Sources and fixed content

The chat brief is the main requirement. All four pages of `C:\Users\User\Downloads\Presentation kay sir business.pdf` were read, and the embedded logo on page 3 was inspected in memory. No extracted images or text files were saved. Pages 2-4 confirm the business idea, name, tagline, logo meaning, vision, mission, objectives, six sections, and badge names. The feed and fake checkout come from the chat brief. Where wording differs, use the chat brief.

### Brand and purpose

- **Name:** SIKHAYA, from *sikhay* (hard work, dedication, passion) and *laya*, from *malaya* (freedom).
- **Tagline:** "Alisin ang tanikala, lumikha nang malaya."
- **Purpose:** Connect Filipino artists with art enthusiasts, collectors, and buyers. Artists can show and sell original artworks, accept commissions, join online art auctions, and access art materials and multimedia equipment. Buyers can find and buy original art while supporting Filipino artists.
- **Logo:** The letter S combines fire and air. Fire means Sikhay; air means Laya. The lower S becomes a paintbrush, with fire and air flowing like paint from its tip. The letters "ikhaya" are brown with a wood texture. Preserve blue for air, orange/red for fire, and dark brown for wood.
- **Vision:** To empower and unite local Filipino artists by providing a space to showcase, sell, and discover local artworks, and to preserve artistic culture.
- **Mission:** To build a home for local artists and creators where artwork gets the spotlight it deserves, and to fill the gap between creative passion and real opportunity by giving people a smooth way to share artworks.

| Objective | Purpose |
| --- | --- |
| Empower Artists | Give local Filipino creators a clear way to display and sell work. |
| Connect Buyers and Creators | Connect art enthusiasts directly with local talent. |
| Promote Culture | Show regional art forms and stories to preserve Filipino heritage. |
| Create Opportunity | Help emerging artists gain visibility and turn creative work into sales. |

PDF page 1 names Dela Cruz, Jay Ar; Dela Cruz, Jhay Ruz; Martin, Thomas; Paraiso Jr, Joven; and Sacobo, Queenzel. The instructor is Mr. Naz Eliseo Carcosia. Use these only as school credits after spelling is confirmed, not as sample sellers/customers.

## 2. Machine check

These are direct checks, not assumptions. No service was started, system setting changed, or existing database opened. One Node SQLite capability probe used memory only; it saved no database file, tables, or app code.

| Tool/item | Observed result | Effect |
| --- | --- | --- |
| Windows | x64, OS release `10.0.26200`, about 8 GB RAM | Prefer a light setup. Windows edition not verified. |
| PHP | **8.2.12**, `C:\xampp\php\php.exe` | Installed, although not on PATH. Use its full path. |
| PHP config | `C:\xampp\php\php.ini` | No config edits in Phase 1. |
| PHP database support | `pdo_sqlite`, `pdo_mysql`, `mysqli`; PDO lists mysql and sqlite | Both database options have a loaded driver. |
| PHP SQLite library | **3.39.2** | Supports the planned schema and transactions. |
| PHP support modules | session, fileinfo, mbstring, openssl, and standard file upload support | Built-in login, text, and image handling available. |
| XAMPP | `C:\xampp` and PHP/Apache/MariaDB/control panel executables found | No XAMPP install needed. |
| Apache | **2.4.58**, `C:\xampp\apache\bin\httpd.exe` | Available; main plan does not need Apache. |
| MariaDB | Client **10.4.32**, `C:\xampp\mysql\bin\mysql.exe`; server executable also found | Backup option; server credentials/access not tested. |
| Separate MySQL folder | `C:\Program Files\MySQL\MySQL Server 26.7` exists | Folder presence only; server version and usability not verified. Do not mix installations. |
| Node.js | **24.20.0**, `C:\Program Files\nodejs\node.exe` | Available for later design checks; not required to run PHP app. |
| Node SQLite | node:sqlite loads; SQLite **3.53.4** | Available capability, not the chosen app driver. |
| npm | **12.2.0**, using npm.cmd | Available; no npm dependencies proposed. |
| Git | **2.56.0.windows.2** | Available; no repository created. |
| Python | python/python3/py not on PATH | Not needed. Possible per-user Python folder could not be checked. |
| SQLite CLI | sqlite3 not on PATH | Not needed; PHP PDO connects directly. |
| PDF utilities | pdftotext/pdfinfo/pdftoppm not on PATH | PDF was read in memory with existing Node tools. |
| Editor | VS Code command entries found | Available for later editing. |
| Browsers | Chrome **156.0.8078.80**, Edge **157.0.4341.42** | Available for later browser verification. |
| Other runtimes | Java found; .NET CLI not on PATH | Neither needed; Java version not checked. |
| PowerShell | Execution policy Restricted | Use full executable paths/.cmd files; no policy change. |
| Ports | Listeners on 80, 3306, 8000 | Their identities were not verified. Do not stop or reuse them blindly. |
| Proposed port | **8088** and alternate **8089** had no TCP listener | Recheck in the phase that starts the app. |
| Project folder | Downloads/sikhaya created successfully | Direct profile-root folder creation was denied. |

Limits: some directory and Windows management queries were denied. A service-name query returned no matches; this does not prove no server exists. A listener on port 3306 does not prove a database login will work. Admin rights were not tested or requested. Installed is different from ready/running.

## 3. One recommended stack and one backup

### Main: plain PHP + SQLite + HTML/CSS/JavaScript

Use installed **PHP 8.2.12**, its local development web server, **PDO SQLite**, server-rendered PHP pages, plain CSS, and small JavaScript files. Store the real database at `storage/database/sikhaya.sqlite`, outside the public web folder.

**Reason:** All needed software is already installed. There are no new runtimes, Composer/npm packages, database services, build steps, cloud accounts, or admin rights required. PHP forms, readable SQL, and shared helper functions suit a second-year IT student. SQLite is a real relational database with tables, foreign keys, indexes, and transactions, suitable for this local prototype.

Use PHP includes and clear action handlers; avoid a custom framework, ORM, React, and a front-end build pipeline. JavaScript handles image previews, auction polling, and feed updates. The server checks every rule and permission. Use local assets so the finished app can run offline.

Later, launch the installed PHP executable by full path, bind to **127.0.0.1:8088**, and use `public` as the document root. This is a planned launch, not an action taken now. Keep database, uploads, logs, and PHP session files inside this project so no write access inside XAMPP is needed. Set per-process upload limits in the later launcher to support the planned 5 MB images and 32 MB request limit, without editing global PHP settings. Recheck storage access and port availability first. PHP's development server is for the local school demo.

### Backup: plain PHP + existing MariaDB

Use the same interface/PHP with PDO MySQL and the installed MariaDB server if the teacher requires MySQL/MariaDB. It adds service startup, credentials, database creation rights, and separate backup steps. Check access without changing existing databases. If a new install becomes necessary, ask first. Do not build both backends. The schema below targets SQLite; choosing this backup requires converting its SQL before implementation.

Technical references checked: [PHP PDO SQLite](https://www.php.net/manual/en/ref.pdo-sqlite.php), [PHP local server](https://www.php.net/manual/en/features.commandline.webserver.php), [SQLite uses](https://www.sqlite.org/whentouse.html), and [SQLite foreign keys](https://www.sqlite.org/foreignkeys.html). Enable foreign keys on every connection.

## 4. Roles

Store one role per account: buyer, artist, or admin. Artist includes Buyer actions. A Buyer can enable an Artist profile later. Admin is assigned through local setup, never public registration.

| Role | Permissions |
| --- | --- |
| Visitor | Browse all six sections, public profiles, feed, search, and About. Sign in for transactions and social actions. |
| Buyer | Register/login/logout; edit own account; buy fixed-price items; fake checkout; bid; request rentals/custom art; track own orders, bids, and requests; post artwork images; react/comment/share/save; report content. |
| Artist | All Buyer actions; manage own Museo profile/artworks; sell originals, crafts, and supplies; create auctions, rental listings, and commission offers; set stock/prices/slots; manage own sales, rental approvals/handover/return, and commission quotes/progress/delivery; request Dalubhasa. Cannot transact with own listings. |
| Admin | Suspend/restore accounts; moderate content; review reports and badges; manage future rental fee tiers; view demo transaction history/totals and overdue work. Cannot see passwords, invent bids, rewrite settled prices, or record real payments. |

Check roles, active status, and ownership on the server. Artists see only their own part of a multi-seller order. Buyers see only their private orders/requests. Public profiles do not expose emails or private project images.

## 5. Pages and features

These are planned PHP paths. Record IDs/filter values are untrusted input. Mutations use POST with permission and CSRF checks. Create/edit forms can share one page.

### Shared pages

| Page | Features |
| --- | --- |
| `/index.php` | Home: purpose, exact tagline, artwork highlights, all six section links, featured artists/auctions, feed link. Main action: Browse artworks. |
| `/search.php` | Search published listings, public artworks, artists; relevant section/category/region/medium/price filters; sorting and pagination. |
| Shared header/footer | Logo, six section names, feed, cart count, saved items, notifications, account menu, About, mobile menu, school prototype label. |
| `/notifications.php` | In-app outbid/result/order/rental/commission/badge updates; unread count and mark read. No email or SMS. |
| `/account/index.php`, `/account/profile.php`, `/account/password.php` | Personal dashboard; edit display name, avatar, bio, region, and password; enable artist profile; links to own activity. |
| Error views | 403, 404, unavailable item, validation, and temporary database errors with useful next steps. |

### 1. Subasta - Online Art Auctions

| Page | Features |
| --- | --- |
| `/subasta/index.php` | Scheduled/live/ended auctions; search/filter by art category; ending-soon/current-price sort. |
| `/subasta/detail.php?id=...` | Images, artwork/artist details, opening price, next minimum bid, highest bid, countdown, start/end times, public-name bid history, bid form, save. Poll about every 5 seconds while visible. |
| `/account/bids.php` | Own leading/outbid/won/lost bids and winner checkout link. |
| `/artist/auctions.php`, `/artist/auction-form.php` | Manage own auctions; select unsold original, opening bid, increment, dates, and view outcomes. Editing depends on auction state. |

### 2. Likha Lokal - Sell Local Materials and Crafts

| Page | Features |
| --- | --- |
| `/likha-lokal/index.php` | Fixed-price artworks, artisan materials, sculptures, crafts; category/region/price/stock filters. |
| `/likha-lokal/detail.php?id=...` | Images, title, description, size/material details, price, stock, seller link, save, add to cart. |
| `/artist/listings.php?section=likha_lokal`, `/artist/listing-form.php` | Create/edit/archive own items; upload images; set prices/quantity; sold-out state. |

### 3. Arkilikha - Equipment and Device Rental

| Page | Features |
| --- | --- |
| `/arkilikha/index.php` | Cameras, photography gear, technical devices; type/daily-price/date filters. |
| `/arkilikha/detail.php?id=...` | Photos, condition/specs, daily price, unavailable dates, start/return dates, duration, fee tier, full quote, save, request rental. |
| `/account/rentals.php`, `/account/rental.php?id=...` | Own request/approval states, quote, fake checkout, permitted cancellation, collection/return state. |
| `/artist/rentals.php`, `/artist/rental.php?id=...`, `/artist/listing-form.php` | Publish equipment, approve/reject requests, booking list/calendar, record collection/return. One listing is one physical unit. |

### 4. Gawang Hiling - Art Commissions

| Page | Features |
| --- | --- |
| `/gawang-hiling/index.php` | Offers, starting prices, medium, estimated days, open slots; availability/type filters. |
| `/gawang-hiling/detail.php?id=...` | Artist examples, terms, capacity/slots, starting price, timing, revision limit, save, request form. |
| `/account/commissions.php`, `/account/commission.php?id=...` | Brief, optional reference image, budget, requested date; quote review/accept/reject; fake checkout; private notes/images; allowed revisions; accept delivery. |
| `/artist/commissions.php`, `/artist/commission.php?id=...`, `/artist/commission-form.php` | Set capacity/terms; approve/reject work; quote final price/due date; post progress/delivery images; handle revisions. |

Private project notes are a simple thread, not a full messaging system. Agreed custom-art price may differ from the advertised starting price.

### 5. Kagamit - Sell Art Materials and Tools

| Page | Features |
| --- | --- |
| `/kagamit/index.php` | Paint, brushes, canvas, paper, supplies, creative tools; type/price/stock filters. |
| `/kagamit/detail.php?id=...` | Images, specifications, price, quantity, seller, save, add to cart. |
| `/artist/listings.php?section=kagamit`, `/artist/listing-form.php` | Publish supplies, update stock/prices, archive own items. |

### 6. Museo - Artist Portfolio

| Page | Features |
| --- | --- |
| `/museo/index.php` | Artist directory with name/region/medium/badge filters. |
| `/museo/artist.php?id=...` | Artist bio, region, specialties, Sibol/Dalubhasa badge, portfolio grid, available/sold works, linked sale/auction/commission offers. |
| `/museo/artwork.php?id=...` | Images, title, artist, medium, size, year, story, sale state; buy through linked direct-sale listing or open auction. Display-only work has no checkout. |
| `/artist/profile.php`, `/artist/artworks.php`, `/artist/artwork-form.php` | Artist setup, profile edits, artwork uploads, direct-sale price or auction link, archive work. |
| `/artist/badge.php` | Current badge, experience/evidence application, admin decision. |

### Community feed

| Page | Features |
| --- | --- |
| `/feed/index.php` | Newest-first paginated feed, composer, artwork images/descriptions, reactions, comment count, share, save. |
| `/feed/post.php?id=...` | Full post, images, comments/replies, reaction totals, share/save/report. |
| `/feed/post-form.php` | Create/edit own post; 1-4 images and description; delete with confirmation. |
| `/account/posts.php` | Own posts/shares; edit originals or remove own share. |

Like/Love/Wow reactions, one active choice per user/post. Comments support one reply level. Users edit/delete their own comments. Share means a stored SIKHAYA repost with an optional note and a separate copy-link action; it does not publish to Facebook. Hidden/deleted originals show unavailable content in shares.

### Login/register

| Page | Features |
| --- | --- |
| `/auth/register.php` | Name, unique email, password/confirmation, Buyer/Artist choice, school-demo agreement. New artist gets Sibol. |
| `/auth/login.php` | Email/password, inline errors, return to intended page after login. |
| `/auth/logout.php` | POST sign-out and clear session. |
| `/account/password.php` | Change password with current password. No email reset in this offline prototype. |

### Cart, fake checkout, and orders

| Page | Features |
| --- | --- |
| `/cart/index.php` | Fixed-price Likha Lokal/Kagamit/Museo items; quantity/remove, seller grouping, current price/stock checks, total. |
| `/checkout/index.php` | Review total, recipient name, simulated pickup notes, clearly labeled Fake checkout. No card/bank fields. |
| `/checkout/special.php?type=...&id=...` | Separate checkout for won auction, approved rental, or accepted commission quote. These do not enter the normal cart. |
| `/checkout/success.php?id=...` | Saved order number, fake receipt, item states, next steps; refresh never duplicates an order. |
| `/account/orders.php`, `/account/order.php?id=...` | Buyer history, receipt, item-level fulfillment/pickup states, allowed cancellation. |
| `/artist/orders.php`, `/artist/order-item.php?id=...` | Own sale lines, buyer pickup name, demo payment state, fulfillment controls. |

### Saved items and About

| Page | Features |
| --- | --- |
| `/saved/index.php` | Saved listings, artworks, artists, feed posts; type tabs, open/remove, unavailable state. |
| `/about.php` | Exact brand meaning/tagline/logo explanation/vision/mission/objectives, six section summaries, school credits. |
| `/help.php` | Auction rules, rental fee tiers, commission steps, badge rules, fake checkout, pickup explanation. |

### Admin pages

| Page | Features |
| --- | --- |
| `/admin/index.php` | Counts, pending reviews, demo sales and rental fees labeled as simulated. |
| `/admin/users.php`, `/admin/user.php?id=...` | Search, suspend/restore with reason; no public admin promotion. |
| `/admin/content.php` | Hide/restore listings, artworks, posts, comments; hide sale access if artwork hidden. |
| `/admin/reports.php` | Resolve/dismiss reports with reasons and recorded actions. |
| `/admin/badges.php` | Review Dalubhasa applications; approve/reject/revoke with reasons. |
| `/admin/rental-rates.php` | Edit future fee tiers without changing accepted quotes. |
| `/admin/transactions.php` | Read/filter orders, auction results, rentals, commissions; expired holds/overdue items. |

Outside default scope: public hosting, real payments, delivery integration, email/SMS, social login, full private inbox, followers, ratings, and identity verification.

## 6. Database design

This is a schema plan, not executable SQL. There are **26 tables**.

### Type/key conventions

- `PK` = primary key, `FK` = foreign key, `UQ` = unique. Every `id INTEGER PK` means SQLite INTEGER PRIMARY KEY. A `?` means NULL allowed; other columns are required.
- `INTEGER` stores whole numbers. `TEXT` stores text. Timestamps are UTC ISO 8601 TEXT, displayed in Asia/Manila. Rental date-only values use local YYYY-MM-DD.
- Money uses nonnegative INTEGER centavos, never floating point. PHP 1,250.50 = 125050 centavos. All currency is PHP.
- Rates use INTEGER basis points: 1000 = 10%, 800 = 8%, 500 = 5%. Booleans use INTEGER 0/1. Use database checks for allowed status values, valid ranges, and counts.
- `created_at` and `updated_at` are required TEXT timestamps. They are included explicitly below. TEXT fields have server length limits. Empty optional display text defaults to an empty string where stated.
- Index all FKs. Enable foreign keys per connection. Store image metadata and private paths, not image bytes. Use prepared PDO queries and transactions.

### Accounts and files

| # / Table | Columns, types, keys |
| --- | --- |
| 1. `users` | `id INTEGER PK`; `display_name TEXT`; `email TEXT UQ` (case-insensitive, trimmed); `password_hash TEXT`; `role TEXT` buyer/artist/admin; `status TEXT` active/suspended; `bio TEXT` default empty; `region TEXT` default empty; `avatar_media_id INTEGER? FK -> media.id`; `status_reason TEXT?`; `status_changed_by INTEGER? FK -> users.id`; `created_at TEXT`; `updated_at TEXT`. |
| 2. `artist_profiles` | `user_id INTEGER PK FK -> users.id`; `specialties TEXT`; `experience_summary TEXT` default empty; `badge TEXT` sibol/dalubhasa, default sibol; `created_at TEXT`; `updated_at TEXT`. Only artist-role users own this row. |
| 3. `badge_reviews` | `id INTEGER PK`; `artist_id INTEGER FK -> artist_profiles.user_id`; `kind TEXT` application/revocation; `statement TEXT`; `evidence_artwork_id INTEGER? FK -> artworks.id`; `status TEXT` pending/approved/rejected; `reviewed_by INTEGER? FK -> users.id`; `decision_reason TEXT?`; `created_at TEXT`; `reviewed_at TEXT?`. One pending application per artist. |
| 4. `media` | `id INTEGER PK`; `owner_id INTEGER FK -> users.id`; `storage_path TEXT UQ`; `original_name TEXT`; `mime_type TEXT`; `byte_size INTEGER`; `width INTEGER`; `height INTEGER`; `alt_text TEXT`; `created_at TEXT`. |

For the optional avatar cycle, insert user without an avatar, insert owned media, then set avatar. Do not require an image at registration.

### Art and listings

| # / Table | Columns, types, keys |
| --- | --- |
| 5. `artworks` | `id INTEGER PK`; `artist_id INTEGER FK -> artist_profiles.user_id`; `title TEXT`; `description TEXT`; `medium TEXT`; `width_mm INTEGER?`; `height_mm INTEGER?`; `year_created INTEGER?`; `region TEXT`; `status TEXT` visible/archived/hidden; `sold_at TEXT?`; `created_at TEXT`; `updated_at TEXT`. |
| 6. `artwork_images` | `artwork_id INTEGER FK -> artworks.id`; `media_id INTEGER FK -> media.id`; `sort_order INTEGER`; **PK (artwork_id, media_id)**; UQ (artwork_id, sort_order). |
| 7. `listings` | `id INTEGER PK`; `seller_id INTEGER FK -> artist_profiles.user_id`; `artwork_id INTEGER? FK -> artworks.id`; `section TEXT` subasta/likha_lokal/arkilikha/gawang_hiling/kagamit/museo; `category TEXT`; `title TEXT`; `description TEXT`; `unit_price_centavos INTEGER`; `stock_qty INTEGER`; `condition_notes TEXT` default empty; `status TEXT` draft/published/paused/sold_out/archived/hidden; `created_at TEXT`; `updated_at TEXT`. |
| 8. `listing_images` | `listing_id INTEGER FK -> listings.id`; `media_id INTEGER FK -> media.id`; `sort_order INTEGER`; **PK (listing_id, media_id)**; UQ (listing_id, sort_order). |

Listing unit price means sale unit price, rental daily rate, commission starting price, or auction opening price, based on section. Original artwork images come from artwork_images; other product/offer examples use listing_images. Artwork and listing owners must match. Museo sale/Subasta requires an artwork. A unique original has stock 1 and one active sale channel across all sections. A portfolio work without a listing is display-only. Do not duplicate original artwork records for Museo and Likha Lokal.

### Auctions

| # / Table | Columns, types, keys |
| --- | --- |
| 9. `auctions` | `id INTEGER PK`; `listing_id INTEGER UQ FK -> listings.id`; `starts_at TEXT`; `ends_at TEXT`; `min_increment_centavos INTEGER`; `status TEXT` scheduled/live/ended/cancelled; `winning_bid_id INTEGER? UQ FK -> bids.id`; `finalized_at TEXT?`; `created_at TEXT`; `updated_at TEXT`. |
| 10. `bids` | `id INTEGER PK`; `auction_id INTEGER FK -> auctions.id`; `bidder_id INTEGER FK -> users.id`; `amount_centavos INTEGER`; `request_token TEXT UQ`; `created_at TEXT`. Immutable accepted bids. |

Read highest bid by amount descending, id ascending. Winning bid must belong to the same auction; enforce during finalization and with an appropriate SQL trigger/composite FK. Start with winning_bid_id NULL to allow initial auction insertion.

### Rentals and commission work

| # / Table | Columns, types, keys |
| --- | --- |
| 11. `rental_rate_tiers` | `id INTEGER PK`; `label TEXT`; `min_days INTEGER`; `max_days INTEGER?`; `commission_bps INTEGER`; `is_active INTEGER`; `created_at TEXT`; `updated_at TEXT`. Active ranges must cover allowed durations without gaps/overlap. |
| 12. `rental_bookings` | `id INTEGER PK`; `listing_id INTEGER FK -> listings.id`; `renter_id INTEGER FK -> users.id`; `start_date TEXT`; `end_date TEXT`; `days INTEGER`; `daily_rate_centavos INTEGER`; `rate_tier_id INTEGER FK -> rental_rate_tiers.id`; `commission_bps INTEGER`; `rental_total_centavos INTEGER`; `platform_fee_centavos INTEGER`; `status TEXT` requested/approved/confirmed/collected/returned/rejected/cancelled/expired; `hold_expires_at TEXT?`; `decision_note TEXT?`; `collected_at TEXT?`; `returned_at TEXT?`; `created_at TEXT`; `updated_at TEXT`. |
| 13. `commission_offers` | `listing_id INTEGER PK FK -> listings.id`; `slot_capacity INTEGER`; `estimated_days INTEGER`; `revision_limit INTEGER`; `terms TEXT`; `created_at TEXT`; `updated_at TEXT`. Must link to gawang_hiling. |
| 14. `commission_requests` | `id INTEGER PK`; `offer_listing_id INTEGER FK -> commission_offers.listing_id`; `buyer_id INTEGER FK -> users.id`; `brief TEXT`; `budget_centavos INTEGER?`; `requested_due_date TEXT?`; `reference_media_id INTEGER? FK -> media.id`; `quoted_price_centavos INTEGER?`; `quoted_due_date TEXT?`; `agreed_revision_limit INTEGER?`; `revisions_used INTEGER` default 0; `quote_expires_at TEXT?`; `checkout_expires_at TEXT?`; `status TEXT` requested/quoted/accepted/in_progress/delivered/completed/rejected/cancelled/expired; `decision_note TEXT?`; `accepted_at TEXT?`; `completed_at TEXT?`; `created_at TEXT`; `updated_at TEXT`. |
| 15. `commission_messages` | `id INTEGER PK`; `request_id INTEGER FK -> commission_requests.id`; `author_id INTEGER FK -> users.id`; `kind TEXT` note/progress/delivery/revision_request; `body TEXT`; `attachment_media_id INTEGER? FK -> media.id`; `created_at TEXT`. Private to participants/Admin. |

Snapshot agreed prices, rates, and terms so later edits cannot change accepted work. Available slots = capacity minus accepted/in_progress/delivered requests; do not maintain a separate counter that can drift.

### Feed and saves

| # / Table | Columns, types, keys |
| --- | --- |
| 16. `posts` | `id INTEGER PK`; `author_id INTEGER FK -> users.id`; `shared_post_id INTEGER? FK -> posts.id`; `body TEXT`; `status TEXT` visible/deleted/hidden; `created_at TEXT`; `updated_at TEXT`. Original has NULL shared_post_id; share points directly to original. |
| 17. `post_images` | `post_id INTEGER FK -> posts.id`; `media_id INTEGER FK -> media.id`; `sort_order INTEGER`; **PK (post_id, media_id)**; UQ (post_id, sort_order). |
| 18. `post_reactions` | `post_id INTEGER FK -> posts.id`; `user_id INTEGER FK -> users.id`; `reaction TEXT` like/love/wow; `created_at TEXT`; **PK (post_id, user_id)**. |
| 19. `comments` | `id INTEGER PK`; `post_id INTEGER FK -> posts.id`; `author_id INTEGER FK -> users.id`; `parent_comment_id INTEGER? FK -> comments.id`; `body TEXT`; `status TEXT` visible/deleted/hidden; `created_at TEXT`; `updated_at TEXT`. Parent must be top-level on same post. |
| 20. `saved_items` | `id INTEGER PK`; `user_id INTEGER FK -> users.id`; `listing_id INTEGER? FK -> listings.id`; `artwork_id INTEGER? FK -> artworks.id`; `post_id INTEGER? FK -> posts.id`; `artist_id INTEGER? FK -> artist_profiles.user_id`; `created_at TEXT`. Exactly one target set. |

Use four partial unique indexes on saved user + each non-NULL target. Use partial UQ (author_id, shared_post_id) for shares, allowing one stored share per user/original. Counts come from visible records. Saves use real target FKs, not an unchecked generic type/id pair.

### Cart and orders

| # / Table | Columns, types, keys |
| --- | --- |
| 21. `cart_items` | `id INTEGER PK`; `buyer_id INTEGER FK -> users.id`; `listing_id INTEGER FK -> listings.id`; `quantity INTEGER`; `created_at TEXT`; `updated_at TEXT`; UQ (buyer_id, listing_id). Fixed-price sale listings only. |
| 22. `orders` | `id INTEGER PK`; `order_number TEXT UQ`; `buyer_id INTEGER FK -> users.id`; `source TEXT` cart/auction/rental/commission; `recipient_name TEXT`; `fulfillment_method TEXT` pickup; `pickup_notes TEXT`; `total_centavos INTEGER`; `payment_status TEXT` awaiting_demo_payment/demo_paid/void/demo_refunded; `checkout_token TEXT UQ`; `payment_due_at TEXT?`; `demo_paid_at TEXT?`; `created_at TEXT`; `updated_at TEXT`. |
| 23. `order_items` | `id INTEGER PK`; `order_id INTEGER FK -> orders.id`; `listing_id INTEGER FK -> listings.id`; `seller_id INTEGER FK -> artist_profiles.user_id`; `auction_id INTEGER? UQ FK -> auctions.id`; `rental_booking_id INTEGER? UQ FK -> rental_bookings.id`; `commission_request_id INTEGER? UQ FK -> commission_requests.id`; `item_title TEXT`; `quantity INTEGER`; `unit_price_centavos INTEGER`; `line_total_centavos INTEGER`; `commission_bps INTEGER`; `platform_fee_centavos INTEGER`; `seller_net_centavos INTEGER`; `status TEXT` awaiting_demo_payment/confirmed/ready/completed/cancelled; `cancelled_by INTEGER? FK -> users.id`; `cancellation_reason TEXT?`; `cancelled_at TEXT?`; `created_at TEXT`; `updated_at TEXT`. |

Ordinary sale lines have no special source FK; other lines have exactly one matching auction/rental/commission FK. Match source listing/seller/buyer to order in the same transaction, reinforced with triggers as needed. Unique source links prevent repeated payment for a special transaction. Snapshot title/price/fee/net for history. Rental order quantity is 1 and unit/line price is the full booking total; daily calculation stays in rental_bookings.

One order is one buyer checkout; a cart can include multiple artists. Fulfillment is per line; overall fulfillment is derived from lines. Header total equals line sum. No real payment processor, bank/card data, or wallet table is needed.

### Notifications and moderation

| # / Table | Columns, types, keys |
| --- | --- |
| 24. `notifications` | `id INTEGER PK`; `user_id INTEGER FK -> users.id`; `message TEXT`; `target_path TEXT`; `event_key TEXT UQ`; `read_at TEXT?`; `created_at TEXT`. Only allowlisted internal target paths. Unique event keys prevent duplicate alerts. |
| 25. `reports` | `id INTEGER PK`; `reporter_id INTEGER FK -> users.id`; `reported_user_id INTEGER? FK -> users.id`; `listing_id INTEGER? FK -> listings.id`; `artwork_id INTEGER? FK -> artworks.id`; `post_id INTEGER? FK -> posts.id`; `comment_id INTEGER? FK -> comments.id`; `reason TEXT`; `status TEXT` open/resolved/dismissed; `reviewed_by INTEGER? FK -> users.id`; `resolution_note TEXT?`; `created_at TEXT`; `resolved_at TEXT?`. Exactly one reported target. |
| 26. `moderation_actions` | `id INTEGER PK`; `admin_id INTEGER FK -> users.id`; `report_id INTEGER? FK -> reports.id`; `target_user_id INTEGER? FK -> users.id`; `listing_id INTEGER? FK -> listings.id`; `artwork_id INTEGER? FK -> artworks.id`; `post_id INTEGER? FK -> posts.id`; `comment_id INTEGER? FK -> comments.id`; `action TEXT` hide/restore/suspend/unsuspend; `reason TEXT`; `previous_status TEXT`; `new_status TEXT`; `created_at TEXT`. Exactly one target. Append-only. Badge history stays in badge_reviews. |

PHP sessions use the file handler in private storage/sessions, so no session table is needed. Categories, sections, and reaction choices are small fixed settings lists. Region is text. No separate search server.

### Relationship map

Arrows below mean parent to children, except the marked one-to-one links.

```text
users
  +-- artist_profiles (0 or 1)
  |     +-- badge_reviews -- reviewer user
  |     +-- artworks -- artwork_images -- media
  |     +-- listings -- listing_images -- media
  |           +-- optional artwork (same owner)
  |           +-- auctions (1:1) -- bids -- bidder user
  |           |       +-- winning bid -> one bid in that auction
  |           +-- rental_bookings -- renter user / rental_rate_tiers
  |           +-- commission_offers (1:1) -- commission_requests -- buyer user
  |                                             +-- commission_messages -- author
  +-- media (owned files; optional user avatar link)
  +-- posts -- post_images -- media
  |     +-- optional original post link for shares
  |     +-- post_reactions -- user
  |     +-- comments -- author / optional parent comment
  +-- saved_items -> exactly one listing, artwork, artist, or post
  +-- cart_items -- listings
  +-- orders -- order_items -- listing / seller
  |                  +-- optional unique auction, rental, or commission
  +-- notifications
  +-- reports -> one reported target
  +-- moderation_actions -> admin / optional report / one target
```

### Constraints and indexes

1. Positive prices/increments/quantities/capacities; nonnegative fees; valid date order; supplied dimensions positive. Use one fixed timestamp format for sorting.
2. Enforce one active sale channel per original using a partial unique index plus transaction checks. Published/paused listings reserve it, including scheduled auctions. Sold originals cannot get a new active listing. Cancelled/archived listings stay in history.
3. Index bids by auction/amount/id; auctions by status/end; bookings by listing/status/start/end; commission requests by offer/status.
4. Index listings by section/status/category, art by artist/status, posts by status/time/id, comments by post/status, orders by buyer/time, order lines by seller/status, notifications by user/read/time.
5. Restrict deletion of users, art/listings, bids, referenced media, and order history. Archive/hide instead. Remove unused join rows only when history does not depend on them. Do not delete a media file still in use.
6. Cross-table rules (matching artwork owner, winning bid membership, matching source order) need transaction checks and small database triggers/composite keys. A simple FK alone does not prove these conditions.

## 7. Business rules

The brief fixes the feature names and intent. Numbers/policies labeled **default** below are proposed prototype choices, not facts from the PDF.

### Accounts and content

- Registration accepts Buyer or Artist only. New artist profile gets Sibol. Initial Admin comes from explicit local setup.
- Use PHP password_hash/password_verify, regenerate session ID after login, HTTP-only SameSite cookies, CSRF tokens, and active-status/ownership checks on protected requests. Reference: [PHP password hashing](https://www.php.net/manual/en/function.password-hash.php).
- Escape user text in HTML; descriptions/comments are plain text. Use prepared statements. Keep database, uploads, sessions, and logs outside public.
- **Default images:** JPEG/PNG/WebP, up to 5 MB and 20 megapixels each; at most 4/post and 6/artwork or listing. Verify actual content/type, use random names, reject SVG/executables, serve through a controlled image route. Attach only owned media. Private commission images require participant/Admin access.
- Names, bios, regions, artwork, and listing details are public. Emails, password hashes, session tokens, and private reference images are not.
- Admin hide/restore/suspend actions require reasons and audit records. Suspension blocks new transactions and posts; existing commitments stay recorded for explicit Admin handling. Hidden content cannot start new transactions. Preserve existing participants' order history without exposing it publicly.

### Fixed-price sales and originals

- Likha Lokal: original fixed-price art, artisan materials, sculpture, crafts. Kagamit: supplies/tools. Museo: artist identity/portfolio and direct-sale original art.
- **Default:** Artist role also covers artisans, suppliers, and equipment providers. No fourth Seller role.
- One original = one artwork record, stock 1, one active sale/auction channel. Museo links to that listing. Sold art may remain in the portfolio marked Sold. Display-only works cannot be purchased.
- Repeatable crafts/supplies can have stock above 1. Adding to cart does not reserve stock. No self-buying/bidding/renting/commissioning.
- Checkout rechecks publication, stock, ownership, current price. Changed price requires a review of the new total before confirmation. Deduct stock and save order atomically. If any line fails, save no partial checkout.
- **Default fulfillment:** simulated pickup; no shipping charges, tax engine, real address, or delivery service.

### Subasta

- Artist chooses unsold artwork, opening price, positive increment, start/end. **Default increment:** PHP 50.00; artist may change before publication.
- Accept bids only at server time >= starts_at and < ends_at. First bid meets opening price; later bids meet highest + increment. Equal bids, self-bids, and bid withdrawals are rejected.
- Acquire a write transaction, then recheck time/highest bid before insert. Device clocks and stale browser counters do not decide acceptance. Use unique request tokens for retries.
- **At the timer deadline, highest accepted bid wins.** No bids means unsold. Default has no reserve price, proxy bidding, buy-now, or automatic time extension. Equal amounts are rejected; stable id order provides a defensive tie-break.
- Details freeze after bids; start/end/pricing cannot change while live. Artist cancellation only before start and any bids. Admin can cancel exceptional cases with a recorded reason, preserving bids and preventing payment.
- PHP has no always-on worker in this plan. Visible page polls and shared request setup reconcile due auctions. The first request after expiry finalizes once and creates one winner order. Eligibility ends at the fixed time even if stored status is updated later. If server was off, reconcile on its next request; do not promise activity while laptop is off.
- **Default winner checkout:** within 24 hours of the deadline. Hold artwork during that window. If expired, void the pending order, keep winner history, and allow a new listing/auction; never silently give it to second place. A late finalization must recognize an already expired payment window.
- A transaction and unique order/auction link prevent duplicate winner orders. Show ended state based on time even before reconciliation writes it.

### Arkilikha

- One listing = one physical unit. Multiple units use separate listings.
- Dates use [start, return): start included, return excluded. November 10 to 11 = 1 day. **Default:** 1-30 whole days; start not in past; return after start.
- Requested does not reserve. Provider approval checks conflicts and places a **24-hour default** checkout hold. Fake payment confirms. Expired holds stop blocking even before stored cleanup.
- Overlap means new start < existing return AND new return > existing start. Active approved holds, confirmed bookings, and collected bookings block dates. Returned/rejected/cancelled/expired do not. Adjacent bookings are allowed. Overdue collected equipment blocks new handover until returned and appears to Admin as overdue.
- Approval and checkout check conflicts inside a write transaction so two overlapping requests cannot both confirm.

| Default duration | Platform commission |
| --- | --- |
| 1-3 days | 10% |
| 4-7 days | 8% |
| 8-30 days | 5% |

- Rental total = daily rate x days. Fee = total x tier rate, rounded half up to a centavo using integer arithmetic. Provider net = total - fee. Fee is deducted from provider demo proceeds, not added to renter price.
- Example: PHP 500/day x 5 = PHP 2,500 total; 8% fee = PHP 200; provider net = PHP 2,300. No real transfers.
- Show and snapshot quote/rate/fee. Later tier edits apply to future requests, not existing quotes. Tiers must not overlap or leave allowed days uncovered.
- Flow: requested -> approved -> confirmed after fake payment -> collected -> returned; also reject/cancel/expire before payment as allowed. Paid cancellation requires Admin before collection and records a simulated refund. No deposits, late fees, damage charges, or renewal by default.

### Gawang Hiling

- Artist sets capacity, starting price, estimated days, revision limit, terms. New requests require an open slot but do not reserve one.
- Buyer sends brief, optional reference, budget/date. Artist quotes final price, due date, and revisions. **Default quote expiry:** 72 hours.
- Buyer acceptance rechecks quote expiry/capacity in a transaction and reserves a slot for **24 hours default** to fake checkout. Expired unpaid acceptance releases slot. Paid acceptance becomes in_progress. Agreed terms then stay fixed; changed scope needs a new agreement/request.
- Progress/delivery uses private messages/images. **Default revisions:** 2, adjustable before agreement. A valid revision increments count exactly once and returns delivered to in_progress. Buyer accepts delivery -> completed -> release slot.
- Flow: requested -> quoted -> accepted -> in_progress -> delivered -> completed. Rejection/cancellation before payment allowed. Paid cancellation needs Admin and recorded demo refund. Overdue date shows overdue; it never auto-completes work.
- Capacity counts accepted/in_progress/delivered. Never lower capacity below occupied slots. Multiple images can be separate messages; no arbitrary files/video/ZIP by default.

### Badges

- **Sibol:** automatic on new artist profile. **Default:** remains until Dalubhasa approval; no unsupported age threshold. Buyers without artist profiles do not display artist expertise badges.
- **Dalubhasa:** expert/professional artist. **Default application:** experience summary + at least 3 visible owned portfolio works. Link one as evidence; Admin reviews full profile and approves/rejects with a reason.
- No paid badge or automatic promotion by account age/sales. Approval updates badge and review in one transaction. Revocation with reason returns badge to Sibol and saves a revocation review. These are prototype labels, not identity certification.

### Feed and saves

- All active signed-in roles can post original artwork with 1-4 images and a description. Only owner edits/deletes; Admin can hide with reason.
- Newest-first with pagination. One Like/Love/Wow reaction per user/post; replace/remove. Counts use visible records.
- Share references original, never copies image files or chains through another share. Hide/deletion of original hides its content in shares. Comments/reactions belong to the specific post acted on.
- Replies only one level; same post as parent. Deleted parent uses a removed marker so replies remain understandable.
- Saves are private and unique per user/target. Saves do not reserve anything. Unavailable items remain removable without exposing hidden details.
- Reports require reason. Admin resolves/dismisses; report is not automatic proof of wrongdoing.

### Fake checkout and order integrity

- Payment screen clearly says school prototype, no money charged. No payment APIs or card/bank data.
- **Default:** fixed sales, auction sales, and commission work have zero platform fee. Only rental fee varies by duration.
- Auction amount comes from winning bid; rental amount from approved quote; commission amount from accepted quote. Client totals are never authoritative.
- Check role, owner, stock, deadlines, and state again when confirming. Use write transactions for stock, bids/finalization, booking approval, slot acceptance, payment, and cancellation. Use unique checkout/event tokens for safe retries.
- Show success only after save. Roll back on error. Refresh/repeated click returns existing order, not another sale.
- Sale line: confirmed -> ready -> completed. Rental completes after return; custom work after accepted delivery. Multi-seller fulfillment is per line; derive overall state.
- Auction winner cannot withdraw through an ordinary cancel button. Admin cancellation before fulfillment records reason and demo refund where applicable; releases stock/slots/dates exactly once. Completed originals do not automatically go back for sale.
- For partial multi-seller cancellation, retain original paid total and derive simulated refund from cancelled line totals. Set demo_refunded only when all paid lines cancel. Repeating cancellation must not restore stock/refund twice. Save the actor, reason, and time in each affected order line's cancellation fields; never overwrite an earlier cancellation.
- Reconcile due states through shared request setup. Timestamps, not stale status text alone, decide whether a hold, bid, or quote is still valid.

## 8. Design direction

The user invoked **emil-design-eng** and **taste-skill:design-taste-frontend**. Use their principles for clarity, restraint, consistent layout, real imagery, accessible controls, and feedback. Phase 1 does not run website builders, copy assets, create gate files, or deal visual components. There is no page to inspect yet.

- Art leads. Preserve supplied logo and blue/fire-orange-red/wood-brown identity. Proposed light warm surfaces and dark brown text; final accessible values chosen later.
- One type/spacing system, button family, card family, and aligned content edges. Legible text; decorative lettering stays in logo.
- Home/About can have a strong first screen and art-led sections. Catalogs, feed, forms, and dashboards use natural content height and pagination; do not force data into full-screen marketing sections.
- Keep artwork title, artist, price, description, and transaction state visible where needed for decisions. These are required functional information.
- Use group-owned art first, otherwise credited licensed examples. Do not invent reviews, customer counts, cultural origins, or real seller claims. Fictional demo data remains identifiable as prototype data.
- Minimal motion on repeated tasks. No moving money values, blinking timers, shaders, parallax, or card loops. Optional short Home entrance must not block use. Immediate keyboard actions; respect reduced motion. Small pointer-only hover/press feedback, roughly 100-200 ms, can be added later without a motion library.
- Verify 1440, 1024, 820, 768, 390, and 320 widths. Visible focus, keyboard access, labels, field errors, alt text, contrast, near-44px touch targets, no overflow/stretched images.
- Main actions fit the task: Browse artworks, Add to cart, Place bid, Request rental, Request artwork. Success follows confirmed save. Design empty/loading/error/sold/expired/hidden/missing-image/long-name states.
- Design skill build gates and browser checks apply when implementation begins, within this functional prototype brief. No visual assets generated now.

## 9. Planned folder structure

Only sikhaya/ and PHASE1_PLAN.md are Phase 1 output. Other entries are future plans.

```text
sikhaya/
  PHASE1_PLAN.md
  README.md                  Setup, start/stop, demo accounts, backup guide
  start-local.cmd            Installed PHP, loopback port, public root
  app/
    config.php               Paths and fixed settings
    bootstrap.php            Setup, sessions, due-state reconciliation
    database.php             PDO helper
    auth.php                 Role/ownership checks
    csrf.php
    validation.php
    uploads.php              Image checks and private access
    helpers.php              Escape, pesos, dates, pagination
    services/
      auctions.php
      rentals.php
      commissions.php
      checkout.php
      notifications.php
    views/
      header.php
      footer.php
      form-errors.php
      artwork-card.php
      listing-card.php
      post-card.php
      errors/
  public/                    Only web document root
    index.php
    search.php
    about.php
    help.php
    notifications.php
    image.php                Controlled image serving
    auth/
    account/
    artist/
    admin/
    subasta/
    likha-lokal/
    arkilikha/
    gawang-hiling/
    kagamit/
    museo/
    feed/
    cart/
    checkout/
    saved/
    actions/                 POST handlers by feature
    api/                     Small JS/poll endpoints
    assets/
      css/                   base.css, components.css, pages.css
      js/                    common.js, auctions.js, feed.js, previews.js
      images/                Approved logo and bundled demo images
  database/
    schema.sql               Database phase only
    seed.php
    migrations/
  scripts/
    setup.php                Explicit setup, not on every request
    backup.php
  storage/                   Private, outside public
    database/                sikhaya.sqlite created later
    uploads/
    sessions/
    backups/
    logs/
  tests/
    acceptance.md
    business-rules.php       Focused checks where needed
  docs/
    DEMO_GUIDE.md
    ASSET_CREDITS.md
```

Favor readable functions/SQL, short purpose-based files, and comments explaining rules. No dependencies/build output planned. Later Git ignores runtime databases, uploaded files, sessions, logs, secrets, and backups. Keep schema, seed definitions, approved bundled demo art. Back up database consistently with its uploaded image files; do not copy a live database blindly.

## 10. Sample data and assets

Create in the database phase only. All sample people, claims, and transactions are fictional.

| Area | Target |
| --- | --- |
| Accounts | 1 Admin, 8 Artists, 6 Buyers; fictional names and .example emails; demo login instructions in later README |
| Art | 24 artworks across artists, varied mediums/regions, some display-only, available, sold |
| Badges | 6 Sibol, 2 Dalubhasa with seeded review history |
| Likha Lokal | At least 12 listings across originals, sculpture, crafts, artisan materials |
| Subasta | 6 auctions: 3 live, 1 scheduled, 1 ended with winner, 1 ended without bids; several bidders |
| Arkilikha | 6 equipment listings; approved/confirmed bookings and unavailable dates |
| Gawang Hiling | 6 offers, different capacity/prices, one full offer; quoted/in-progress/delivered/completed work |
| Kagamit | At least 12 supply/tool listings, varied stock, one sold-out |
| Feed | About 20 originals, 4 shares, 30 comments/replies, reactions, saves |
| Orders/Admin | About 8 coherent orders, notifications, open/resolved reports, moderation examples |

Art records overlap listings by reference; never duplicate a unique original. Seed dates relative to setup so auctions/bookings work on presentation day. Seed data must follow totals, stock, ownership, badge, and state rules. Repeat setup must not duplicate rows or erase work. Destructive reset is a separate explicit action with backup/confirmation.

Use the PDF logo later with transparency preserved; its embedded image is 993 x 439. A higher quality original would help but is not required for planning. Group-owned art is preferred; otherwise use licensed images with credits in ASSET_CREDITS.md. Bundle approved assets locally for offline demo. No extraction file, image generation, or asset download in Phase 1.

## 11. Phases and completion goals

Only Phase 1 is currently authorized. Each later phase is a review point, not automatic permission to continue. Any install still needs permission.

| Phase | Work | Completion goal |
| --- | --- | --- |
| **1 - Gather everything** | PDF/brief, machine check, stack, roles/pages/schema/rules/folders/defaults. | Save and check this plan, then stop. |
| **2 - Confirm scope and interface plan** | Apply answers/defaults; navigation, screen layouts/states/fields, color/type, asset list, demo flows. Markdown plans/wireframes only by default. | Group can follow Artist/Buyer flows through all six sections and feed. No database/app code unless next instruction changes this scope. |
| **3 - Local foundation and real database** | Folders, PHP base, storage, schema, repeatable seed/setup, local launch when authorized. | Localhost works; real records survive restart; FKs/checks work; no new installs needed. |
| **4 - Accounts, Museo, fixed-price sales** | Login/roles, profiles, uploads/art, Likha Lokal/Kagamit, search/cart/fake checkout/orders. | Artist publishes; Buyer buys; stock persists; original cannot sell twice. |
| **5 - Subasta** | Creation, timers/polling, bids, finalization, winner checkout/expiry/alerts. | Two bidders work; late/self/retry bids handled; correct winner gets one order; restart expiry works. |
| **6 - Arkilikha and Gawang Hiling** | Dates/quotes/fees/return; commission quotes/slots/thread/revisions/delivery. | Overlaps/capacity enforced; correct rental fee; both workflows complete with saved history. |
| **7 - Feed, saves, Admin** | Posts/reactions/comments/reposts/saves, reports/moderation, badge reviews, About/help. | Actions persist, privacy holds, admin decisions recorded. |
| **8 - Polish and school handoff** | Responsive/accessibility/visual checks, relevant skill gates, focused rule tests, realistic images, demo and backup/restore guides. | Works on this laptop, runs without admin rights, group can explain files/tables/rules and restore demo. |

Later meaningful tests: repeated checkout/stock race; two tabs near auction expiry; server off after deadline; two overlapping rental approvals and adjacent dates; last commission slot; changed price; guessed private IDs/images; CSRF/invalid uploads; deleted original through share; duplicate save/reaction/retry. Do not add a large test framework for simple cosmetic changes.

## 12. Assumptions and questions

Questions are for the next phase, not blockers to this deliverable. Defaults are proposals.

| Short question | Default answer |
| --- | --- |
| Does the teacher require MySQL? | No; SQLite. Use MariaDB backup if required. |
| Can Artists also buy? | Yes, same account. |
| Who supplies tools and rental gear? | Artist accounts, including artisans/providers. |
| Are rental fee tiers okay? | 10% for 1-3 days, 8% for 4-7, 5% for 8-30; deducted from provider proceeds. |
| Are rentals daily? | Yes; 1-30 whole days, one unit/listing. |
| How is Dalubhasa awarded? | Admin review, experience summary, 3 owned visible works; Sibol until approved. |
| Winner payment time? | Fake checkout within 24 hours of deadline; no automatic runner-up award. |
| How many art revisions? | Default 2; agreed before acceptance. |
| Quote and hold limits? | Quote 72 hours; accepted rental/commission checkout hold 24 hours. |
| Is pickup enough? | Yes, simulated pickup with no real address/delivery integration. |
| Which feed actions? | Like/Love/Wow, one reply level, SIKHAYA repost, copy link, save. |
| Which UI language? | Simple US English; Filipino section names and exact tagline preserved. |
| May we use the PDF logo and group art? | Yes later; group-owned art first, credited licensed stand-ins if needed. |
| Show group/instructor on About? | Yes, after PDF spelling is confirmed. |
| Fees beyond rentals? | Zero for fixed sales, auctions, custom-art work. |

Other assumptions: this laptop is the demo host; offline use after asset preparation; no public hosting; the group has image permission; no real settlement/handover is performed by software; badges are manually reviewed prototype labels.

## 13. Completion record

- Read the full PDF and viewed its embedded logo in memory.
- Checked tools, versions, PHP drivers, paths, and ports without installations or server changes.
- Recommended a stack/backup based on actual installed tools.
- Planned roles, pages, 26 tables and connections, business rules, sample content, folders, phases, assumptions, and short questions with defaults.
- Created only this planning file in the new Downloads/sikhaya folder. No app code, persistent database, package install, service launch, deployment, or messages to others.

**Stop here. Wait for the user to say "start Phase 2".**
