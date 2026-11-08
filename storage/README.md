# Runtime storage

The application creates these folders on first setup:

| Folder | Contents |
| --- | --- |
| `database/` | SQLite database and temporary database files |
| `uploads/` | Images uploaded by local users |
| `sessions/` | PHP login sessions |
| `logs/` | Local PHP and application logs |
| `backups/` | Database snapshots and uploaded-image copies |

Only this README belongs in Git. The root `.gitignore` excludes all runtime folders and their contents.

Run `scripts/setup.php`, or double-click `start-local.cmd` on Windows, to create a database with demo accounts and sample records. Existing populated databases are kept. Keep the server's document root set to `public/` so storage is not web-accessible.

To transfer saved work between computers, use `scripts/backup.php` and follow its restore notes. Backups and personal uploads are separate from the source repository.
