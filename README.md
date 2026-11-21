# Ember Café Complete Point of Sale

IT0049 Midterm Project · CodeIgniter 4

- Live website: https://ember-cafe-midterm.site.je/
- GitHub: https://github.com/carrilloklyde-a11y/IT0049-MIDTERM

Developed by **Maverick John Klyde L. Carrillo**. This is the complete CodeIgniter café POS, using the Ember theme from the earlier activities. It is a separate project from the Laravel formative assessments.

## Demo access

- Username: **admin**
- Password: **admin**

These credentials are also displayed on the login page, as requested. Use fictional classroom data in the public demonstration.

## Run the prepared local project

Double-click **START.cmd**, then open <http://127.0.0.1:8004>. Keep the launcher window open while using the app. The launcher detects XAMPP PHP, loads Intl, GD, and SQLite3 if needed, runs migrations, and seeds a new installation. Existing stock, passwords, and sales are preserved.

## Install from GitHub

Requirements: PHP 8.2 or later; Composer 2; PHP extensions Intl, mbstring, GD with WebP, SQLite3, fileinfo, OpenSSL, DOM, and XML. MySQLi is needed only when selecting MySQL instead of the default SQLite connection.

```sh
composer install
```

Copy `.env.local.example` to `.env`, then run:

```sh
php spark migrate
php spark db:seed DemoSeeder
php spark serve --host 127.0.0.1 --port 8004
```

On this Windows computer, `C:\xampp\php\php.exe` is available. The launcher loads its missing extensions without changing the global PHP installation. There is no frontend build or external font dependency.

## Midterm requirements checklist

| Requirement | Implementation |
| --- | --- |
| CodeIgniter application with MVC structure | Explicit routes, controllers, models, views, libraries, and filters |
| Relational products, customers, users, and sales schema | `app/Database/Migrations/2026-11-21-000001_CreatePos.php` |
| Product list, add, edit, delete or archive | Product management, stock and price fields, archive and restore |
| Uploaded display-ready product image | Validated image upload, resized to fit 1000 × 800, re-encoded as WebP |
| Customer full CRUD | List, create, edit, archive, and restore |
| Staff full CRUD with avatar and hashed password | Staff management, square 320 × 320 avatar, `password_hash` and `password_verify` |
| Authentication protects management pages | `AuthFilter` on every management and uploaded-media route |
| Record a sale with optional customer and quantity | `/pos`, product selection, walk-in option, quantity, optional cash amount |
| Decrease stock and reject overselling | Transaction with a guarded atomic stock update in `SaleService` |
| Sales history with product, customer, staff, quantity, total, date | Joined sales history at `/sales` and printable receipts |
| Validation and safe upload storage | Server validation, restricted formats and sizes, random filenames, private writable storage |
| Raw project with migrations and seeder | Composer lockfile, app source, schema migration, DemoSeeder |
| Clear setup documentation | This README, local launcher, and deployment guide |

## Pages and actions

- `/login`: demo credentials and staff login.
- `/`: dashboard, today's sales, active products/customers, and low-stock count.
- `/products`: menu products, prices, stock, image, edit, archive, restore.
- `/customers`: customer contact details and CRUD.
- `/staff`: usernames, names, avatar uploads, and password updates.
- `/pos`: product menu and Record Sale form.
- `/sales`: sales history with all required relationships.
- `/sales/{id}`: printable receipt.
- `/about`: project description and developer credit.

Archives implement reversible deletion while keeping foreign-key references and historical sales intact. Archived products cannot be sold, archived customers cannot be selected, and archived staff cannot stay signed in. An account cannot archive itself. Blank passwords on staff edits retain the existing hash; changing a password invalidates other sessions for that account.

## Database and money handling

The migration retains all specified fields and relationships. Additional fields support categories, reversible archival, session invalidation, duplicate-checkout protection, cash received, and snapshots of product name and unit price.

All sale calculations use integer centavos and are stored in `DECIMAL(10,2)` fields. Client-submitted totals or prices are never used. Staff identity comes from the authenticated session. Checkout starts a database transaction, validates active records, conditionally reduces stock only when enough remains, inserts the sale, and commits both together. A failed checkout rolls back its changes. A unique checkout token prevents a repeated submission from recording a second sale. Historical product names and prices do not change when the menu is edited.

The default database is `writable/ember.sqlite`, with foreign keys enabled. SQLite is supported both locally and on the selected host. MySQL connection settings are documented in `.env.example`; the same migration supports MySQL with InnoDB foreign keys.

## Upload handling

Only JPG, PNG, and WebP are accepted. Maximum file size is 2 MB; maximum dimensions are 4096 × 4096. The server reads the image content, validates the extension, decodes and re-encodes it through GD, and assigns a random filename. Product images are resized; avatars are cropped to a square. Original uploads are not published. Prepared files are stored in `writable/uploads` and served only through an authenticated controller. The storage folder is not the public uploads directory.

## Security and tests

Forms use session-based CSRF protection. Login regenerates the session identifier, verifies password hashes, and rate-limits repeated attempts. Management filters recheck that the staff account is active and its authentication version is current. Views escape user-supplied text. All management routes are explicit; automatic routing is disabled. Database operations use bound query-builder values.

Run from this project directory:

```sh
php vendor/bin/phpunit --no-coverage
php spark routes
```

The suite includes 16 tests and 95 assertions covering protected and signed-in routes, demo login, all management workflows, hashed and unchanged passwords, invalidated sessions, sale totals and receipts, overselling, duplicate checkout, underpayment, archived records, historical price preservation, image preparation, unsafe uploads, and seed repeatability. Tests use an isolated in-memory SQLite database.

## Demonstration walkthrough

1. Visit a management URL while signed out and confirm redirection to login.
2. Sign in with `admin` / `admin`.
3. Add a product with a photo and stock. Edit it and check the resized image.
4. Create and edit a customer and staff account; upload a staff avatar.
5. Record a sale for a selected product, customer, and quantity. Confirm its receipt and reduced stock.
6. Request more than the available stock and confirm rejection without a new sale.
7. Check Sales History for the product, customer, staff, quantity, total, and date.
8. Archive a record and verify the sale history remains intact; restore the record.
9. Sign out and confirm management pages require login again.

## Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md). Environment settings, the local database, prepared uploads, session files, installed libraries, and private hosting connection details are excluded from Git. Migrations and seeders reproduce the initial database.

## References

- [CodeIgniter models](https://codeigniter4.github.io/userguide/models/model.html)
- [Controller filters](https://codeigniter4.github.io/userguide/incoming/filters.html)
- [Validation](https://codeigniter4.github.io/userguide/libraries/validation.html)
- [Image manipulation](https://codeigniter4.github.io/userguide/libraries/images.html)
- [Database transactions](https://codeigniter4.github.io/userguide/database/transactions.html)
