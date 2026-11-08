<p align="center">
  <img src="public/assets/images/sikhaya-logo.png" alt="SIKHAYA" width="280">
</p>

<h1 align="center">SIKHAYA</h1>

<p align="center"><strong>Alisin ang tanikala, lumikha nang malaya.</strong></p>
<p align="center">A local marketplace and community for Filipino art and artists.</p>

SIKHAYA is a school group project for **The Entrepreneurial Mind**. It connects Filipino artists with art enthusiasts, collectors, and buyers through artwork sales, auctions, equipment rentals, custom commissions, and a community feed.

The name combines **sikhay** (hard work, dedication, and passion) and **laya** (freedom). This prototype runs on localhost, saves records in a real SQLite database, and uses fake checkout. No real money is charged.

## Contents

- [Features](#features)
- [Tech stack](#tech-stack)
- [Requirements](#requirements)
- [Getting started](#getting-started)
- [Demo accounts and sample data](#demo-accounts-and-sample-data)
- [Project structure](#project-structure)
- [Database and backups](#database-and-backups)
- [Running checks](#running-checks)
- [Business rules](#business-rules)
- [Project limits](#project-limits)
- [Documentation and credits](#documentation-and-credits)

## Features

| Section | Purpose |
| --- | --- |
| **Subasta** | Timed art auctions, bidding, bid history, winner selection, and winner checkout |
| **Likha Lokal** | Fixed-price artworks, sculptures, local materials, and handmade crafts |
| **Arkilikha** | Equipment rentals, date availability, approval, duration-based fees, pickup, and return |
| **Gawang Hiling** | Custom-art requests, quotes, commission slots, private updates, revisions, and delivery |
| **Kagamit** | Art materials and tools with stock, quantities, cart, and checkout |
| **Museo** | Artist profiles, portfolios, direct-sale artwork, and Sibol/Dalubhasa badges |

Shared features include:

- Buyer, Artist, and Admin accounts; registration and login.
- Artwork feed with image posts, reactions, comments, replies, internal shares, and saves.
- Search, category and price filters, saved collections, and in-app notifications.
- Fake checkout, stored receipts, order history, and artist fulfillment controls.
- Admin moderation, reports, badge reviews, rental fee settings, and eligible order cancellations.

Artists also have Buyer permissions. Admin is not a public registration option.

## Tech stack

| Layer | Technology |
| --- | --- |
| Server | Plain PHP 8.2+ |
| Database | SQLite through PDO |
| Interface | Server-rendered HTML, CSS, and vanilla JavaScript |
| Local server | PHP's built-in development server |
| Optional development checks | Node.js 24+ |

No Composer or npm dependencies need to be installed. MySQL, Apache, and a front-end build step are not required. The application and bundled images can run offline after downloading the repository.

## Requirements

- PHP **8.2 or later**, with `pdo_sqlite`, `mbstring`, `fileinfo`, and session support enabled.
- A current browser such as Chrome, Edge, or Firefox.
- Write access to this project's `storage/` folder.
- A free local port; the Windows launcher uses **8088**.

An existing XAMPP installation can supply PHP. The Windows launcher checks `SIKHAYA_PHP`, then PHP on PATH, then `C:\xampp\php\php.exe`. You do not need to start the XAMPP Apache or MySQL services.

## Getting started

Clone this repository, or choose **Code > Download ZIP** on GitHub and extract it. Open a terminal in the extracted project folder.

### Windows: double-click launcher

1. Double-click **`start-local.cmd`**.
2. Keep its terminal window open.
3. Visit **http://127.0.0.1:8088**.
4. Choose **Sign in** and use a demo account below.

The launcher creates the database and seeds demo data on first use. Starting it again keeps your records. Press **Ctrl+C** in its terminal to stop the site.

If PHP is installed elsewhere, set its path in PowerShell before launching:

```powershell
$env:SIKHAYA_PHP = 'D:\tools\php\php.exe'
.\start-local.cmd
```

Replace the example path with your actual `php.exe` location.

### Windows: manual commands with XAMPP PHP

```powershell
& 'C:\xampp\php\php.exe' scripts/setup.php
& 'C:\xampp\php\php.exe' -d upload_max_filesize=5M -d post_max_size=32M -S 127.0.0.1:8088 -t public
```

### PHP on PATH: Windows, macOS, or Linux

```sh
php scripts/setup.php
php -d upload_max_filesize=5M -d post_max_size=32M -S 127.0.0.1:8088 -t public
```

Always serve the **`public/`** directory. If port 8088 is busy, stop your previous SIKHAYA server or use a free port in the manual command and open that address.

### Common setup issues

| Issue | What to check |
| --- | --- |
| PHP cannot be started | Confirm PHP is installed; use its full path or set `SIKHAYA_PHP`. |
| `could not find driver` | Enable `pdo_sqlite` for the CLI PHP installation. Check with `php -m`. |
| `mb_*` or `finfo` function errors | Enable `mbstring` and `fileinfo` for that PHP installation. |
| Database is missing | Run `scripts/setup.php` from the project folder. |
| Storage cannot be written | Put the project in a writable folder and check `storage/` permissions. |
| Browser cannot connect | Keep the PHP server terminal open and use the port shown there. |

## Demo accounts and sample data

All seeded demo accounts use **`Sikhaya2026!`**. The login page also has Buyer, Artist, and Admin shortcuts.

| Role | Email | Try this |
| --- | --- | --- |
| Buyer | `buyer@sikhaya.example` | Buy, bid, pay an approved rental, accept an art quote, or use the feed |
| Artist | `artist@sikhaya.example` | Publish, approve rentals, quote/deliver custom art, or manage sales |
| Admin | `admin@sikhaya.example` | Review badges/reports, moderate, adjust rental fees, or review transactions |
| Additional Buyer | `bea@sikhaya.example` | Bid against the main Buyer in a separate browser/profile |
| Additional Buyer | `carlo@sikhaya.example` | Try a second buyer flow |

A fresh setup includes:

- 15 accounts: 1 Admin, 8 Artists, and 6 Buyers.
- 24 portfolio artworks and 48 listings across the marketplace sections.
- 6 auctions, plus rental and custom-art request examples.
- 24 feed posts/shares, with comments, reactions, and saved items.
- Seeded orders, notifications, badge reviews, and reports.

All seeded identities, listings, prices, and transactions are fictional. Artwork and equipment samples are generated demo images; other photos are licensed stand-ins. See [asset credits](docs/ASSET_CREDITS.md).

Dates are relative to the **first setup**, so live auctions eventually end. For a live auction demo later, publish a new one from the Artist studio. Setup does not reset your database automatically.

## Project structure

```text
sikhaya/
|-- app/                      PHP logic and server-rendered views
|   |-- bootstrap.php         Sessions, database helpers, validation, uploads
|   |-- commerce.php          Orders, auctions, rentals, cancellations
|   |-- actions.php           POST actions and permission checks
|   |-- views.php             Shared layout and marketplace pages
|   |-- pages.php             Accounts, checkout, feed, saved items
|   `-- workspace.php         Artist and Admin workspaces
|-- database/
|   |-- schema.sql            26-table relational schema
|   `-- seed.php              Repeatable fictional sample data
|-- docs/
|   |-- ASSET_CREDITS.md       Image sources and license information
|   |-- DEMO_GUIDE.md          Group presentation walkthrough
|   |-- VERIFICATION.md        Recorded checks and known limits
|   `-- planning/             Phase 1 and Phase 2 notes
|-- public/                   The only web-accessible directory
|   |-- index.php             Page router using ?p=...
|   |-- image.php             Controlled image responses
|   |-- api.php               Auction polling endpoint
|   `-- assets/               CSS, JavaScript, logo, bundled images
|-- scripts/
|   |-- setup.php             Create and seed a fresh database
|   |-- backup.php            Back up the database and uploads
|   `-- run-tests.mjs         Checks in an isolated local copy
|-- storage/                  Generated local data; excluded from Git
|-- tests/                    HTTP and database checks
|-- .editorconfig             Shared formatting settings
|-- .gitattributes            Text and Windows launcher line endings
|-- .gitignore                Excludes runtime/private/generated files
|-- start-local.cmd           Windows launcher
`-- README.md
```

Pages share one front controller to keep routing and access checks in one place. For example, `/?p=artists` opens Museo, `/?p=feed` opens the feed, and `/?p=studio` opens the Artist workspace.

## Database and backups

The default database is **`storage/database/sikhaya.sqlite`**. It is created from the tracked schema and seed files, so the repository does not need a committed database. Uploads, sessions, logs, backups, and local test data are also excluded from Git.

To create a backup:

```sh
php scripts/backup.php
```

If PHP is not on PATH, use its full executable path as in the setup commands. Backups under `storage/backups/` include a consistent SQLite snapshot, uploaded images, and restore instructions.

To restore, stop PHP, keep a safety copy of your current database/uploads, and follow the backup's `RESTORE.txt`. Keep the bundled `public/assets/` with the app. Never replace a database while the server is running.

`SIKHAYA_DB` can override the database path for development. Leave it unset for normal use.

## Running checks

PHP syntax checks require PHP only. For example:

```sh
php -l public/index.php
php -l app/actions.php
```

The optional integration suite requires **Node.js 24+** and PHP. Run:

```sh
node scripts/run-tests.mjs
```

The runner creates a fresh app copy and seeded test database under ignored `.local/test-runs/`, starts a temporary PHP server on a free loopback port, runs the checks, and stops that server. Your normal database and uploads stay separate. Test-run files are retained for inspection. Set `SIKHAYA_PHP` if PHP cannot be found.

The suite covers role access, CSRF, checkout retries, stock, auction deadlines, rental overlaps, commission capacity/revisions, private images, feed actions, moderation, and database integrity. The previous build passed **57 checks**. See [verification notes](docs/VERIFICATION.md), including the incomplete automated visual review.

## Business rules

- An original artwork has stock 1 and one active sale channel. A sold original cannot sell again.
- Auction bids must arrive before the server deadline. Highest accepted bid wins; winner checkout stays open for 24 hours. Self-bidding and bid withdrawal are blocked.
- Rental dates include pickup day and exclude return day. Rentals last 1-30 days. Overlapping confirmed bookings and active holds are blocked.
- Default rental commission: **10%** for 1-3 days, **8%** for 4-7, **5%** for 8-30, deducted from provider demo proceeds.
- Custom-art quotes expire after 72 hours. Accepted quotes reserve a slot for 24-hour fake checkout. Agreed prices and revision limits are saved.
- New artists receive **Sibol**. **Dalubhasa** requires Admin review of experience and at least three visible portfolio works.
- PHP checks totals and permissions; browser-submitted values are not authoritative.

## Project limits

This is a **local school demonstration** with fake payments and simulated pickup. It has no real payment processor, shipping integration, email password reset, or public identity verification. Demo login shortcuts are intentional.

Auction deadlines remain fixed, but stored results and expired holds reconcile when a request reaches the running PHP server. Nothing runs while the server or laptop is off.

GitHub stores the source repository. Running this PHP application still requires the PHP server and writable SQLite storage described above.

## Documentation and credits

- [Presentation walkthrough](docs/DEMO_GUIDE.md)
- [Asset credits and image licenses](docs/ASSET_CREDITS.md)
- [Verification results](docs/VERIFICATION.md)
- [Phase 1 plan](docs/planning/PHASE1_PLAN.md)
- [Phase 2 build notes](docs/planning/PHASE2_BUILD.md)

**Group:** Dela Cruz, Jay Ar; Dela Cruz, Jhay Ruz; Martin, Thomas; Paraiso Jr, Joven; Sacobo, Queenzel.

**Course:** The Entrepreneurial Mind. **Instructor:** Mr. Naz Eliseo Carcosia.

The logo comes from the group's supplied presentation. Third-party images keep the licenses listed in the asset credits. No project-wide open-source license has been selected.
