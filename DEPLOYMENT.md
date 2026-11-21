# Hosting Ember Café

The app is prepared for free PHP hosting with writable persistent storage. The selected InfinityFree account supports PHP 8.4, SQLite3, Intl, GD, and MySQLi.

## Standard hosting layout

On a host with a configurable document root, point the website to `public/` and keep `app/`, `vendor/`, `.env`, and `writable/` outside that public directory. Install production dependencies with `composer install --no-dev --optimize-autoloader`, configure `.env`, run migrations and the seeder, and make `writable/` writable by the PHP process.

## Fixed document-root hosting

The deployment package places the public assets and front controller directly in `htdocs/`, adjusts the front controller's app path, and includes strict deny rules for `app/`, `vendor/`, `writable/`, hidden files, and development files. Each protected directory also has its own denial file. Only prepared images served by the authenticated `/media/{filename}` endpoint are accessible.

The production environment uses HTTPS, an empty `indexPage`, and production error handling. SQLite is stored under the protected writable directory. An initialized demonstration database may be transferred privately on first deployment. Subsequent deployments preserve the live database, sessions, and uploaded files.

Hosting credentials and production settings are kept in a private deployment location and are not committed. The repository includes `.env.example` with placeholder values only.

## Verify before submission

Check the login redirect, admin demo login, CRUD forms, image uploads, successful stock deduction, insufficient-stock rejection, sales history, and logout. Verify that requesting `.env`, the database file, or a private app/vendor/writable path returns a forbidden or not-found response. Submit the GitHub repository URL and the live HTTPS website URL.

Free hosting has provider resource and availability limits. Keep a local copy of the application and back up its database and uploads before changes to the hosting account.
