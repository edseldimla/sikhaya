# Integration checks

From the project root, run:

```sh
node scripts/run-tests.mjs
```

Requirements: Node.js 24+ and PHP 8.2+ with PDO SQLite, mbstring, and fileinfo. There is no npm install step. If PHP is not found, set `SIKHAYA_PHP` to its executable path.

The runner copies the application into ignored `.local/test-runs/run-...`, creates a fresh seeded `test.sqlite`, and starts a PHP server on a free loopback port. HTTP checks run against that copy. Its database, uploads, sessions, and logs stay inside the copy; your normal app data is untouched. Each run uses a new directory so it can be repeated without resetting a previous database. The temporary server stops when the checks finish; files are kept for debugging.

`http-smoke.mjs` checks authentication, permissions, CSRF, fake checkout, stock, auctions, rentals, commission workflows, private media, feed actions, moderation, and database integrity. It changes only the test copy.

Recorded visual-check limits are in [the verification notes](../docs/VERIFICATION.md). HTTP checks are not a substitute for inspecting the interface in a browser.
