# KIKIMO POS Web Management

A Laravel 12 management dashboard for desktop POS sales, terminals, products, cashiers, reports, and synchronization history.

## Database and existing sync table

The application uses Laravel's **single default MySQL connection**. Point `DB_DATABASE` in `.env` at the MySQL database that already contains `desktop_pos_sync_logs` (the current local configuration is `kikimode_dev_app`). Do not configure another connection or create another sync database.

The existing `desktop_pos_sync_logs` table and its records are not created, altered, or deleted by the Kikimo POS migration. The migration adds the business tables and `business_id`, `role`, and `is_active` fields to the existing `users` table (`business_id` is nullable for existing accounts). Its `down()` is intentionally non-destructive because it may adopt tables that existed before this application was installed.

Before deploying, take a database backup. Then run:

```sh
php artisan migrate --force
php artisan config:cache
```

The migrations are guarded against existing table/column names. Existing unrelated tables and records are left in place.

## First business and login

Public business registration is disabled. After configuring the production database, create a business and its first owner from a trusted CLI session:

```sh
php artisan kikimo:create-owner
```

The command prompts for business details and a 12-character-minimum password. Do not run `db:seed` to create production accounts. After signing in, use **Change password** in the sidebar to replace your own password; the current password is required. Team accounts are added from **Team members** by a business owner or administrator and must be assigned to an active terminal in that business. Staff access to sales, products, cashiers, reports, and sync history is restricted to their assigned terminal. Administrators can manage business settings and accounts; passwords are never displayed and can only be replaced.

For local development only, `php artisan db:seed` creates the demo owner `admin@kikimo.local` with password `12345678`. The seeder refuses to run outside local/testing environments. Change this password after signing in; use `php artisan kikimo:create-owner` to provision production accounts instead.

Configure production mail delivery in `.env` for password reset links. Never enable `APP_DEBUG` in production.

## Desktop POS sync

The endpoint is `POST /api/desktop-pos/sync` (not `/api/api/...`). Register each terminal in the dashboard using the exact machine and client IDs sent by the desktop POS. Bearer authentication is disabled: the `Authorization` header can be omitted, and any supplied value is ignored. The API matches both submitted machine and client IDs against an active terminal registered to a business:

```http
Authorization: Bearer <terminal-token>
Idempotency-Key: <unique-key-per-queued-event>
Content-Type: application/json
```

Requests matching an active, registered machine/client pair are imported synchronously. Because those IDs are not secrets, an attacker who learns them could impersonate that terminal; use HTTPS and keep the API rate limit enabled. Unknown or inactive terminals are stored as pending and are not associated with a business; they can be recovered after an administrator registers the exact machine/client pair. Use a unique idempotency key for each queued event, and reuse that same key when retrying that event; repeated keys return the original sync record. Import errors retain the original payload for retry or investigation.

Sales support `create`, `update`, and `delete`. Product sync supports `upsert` and `delete` operations and retains the original flexible JSON payload. The importer recognizes the desktop product's `id`, `name`, `sku`, `barcode`, `price`, and `stock` fields.

The importer runs synchronously for matching registered terminals so successful sales and products are immediately available. No queue worker is required. A recovery command retries pending, interrupted, and failed imports after a terminal with matching machine/client IDs is registered:

```sh
php artisan kikimo:recover-syncs --limit=100
```

For cPanel, configure the scheduler cron once per minute:

```cron
* * * * * cd /absolute/path/to/application && php artisan schedule:run >> /dev/null 2>&1
```

Restrict access to `.env`, logs, and the application storage directory.

## Production web server

Set the domain's document root to the project's `public` directory (for example, in cPanel **Domains → Document Root**). This prevents application source, configuration, dependencies, and logs from being web-accessible. If the host cannot set a separate document root, keep the root `.htaccess` rewrite in place; it routes requests through `public/` and disables directory indexes, but a separate `public` document root remains preferred. Deploy the project-root `.htaccess` alongside the application. Use HTTPS, set `APP_ENV=production` and `APP_DEBUG=false`, and run `php artisan storage:link` only if public storage is needed.

## Tests and assets

```sh
php artisan test
npm run build
```
