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

The command prompts for business details and a 12-character-minimum password. Do not run `db:seed` to create production accounts. Team accounts are added from **Team members** by a business owner or administrator and must be assigned to an active terminal in that business. Staff access to sales, products, cashiers, reports, and sync history is restricted to their assigned terminal. Administrators can manage business settings and accounts; passwords are never displayed and can only be replaced.

Configure production mail delivery in `.env` for password reset links. Never enable `APP_DEBUG` in production.

## Desktop POS sync

The endpoint is `POST /api/desktop-pos/sync` (not `/api/api/...`). Register each terminal in the dashboard using the exact machine and client IDs sent by the desktop POS. A random bearer token is displayed once during registration; store it securely in the desktop app and send:

```http
Authorization: Bearer <terminal-token>
Idempotency-Key: <unique-request-key>
Content-Type: application/json
```

The API validates that the terminal is active, its bearer token matches, and the submitted client ID matches the registered terminal. A machine ID alone never grants access. Requests are stored in the existing sync-log table and sale/product imports are applied transactionally before a successful response. Repeated idempotency keys return the original sync record. Import errors retain the original payload and are logged for investigation/retry.

Sales support `create`, `update`, and `delete`; products are stored with their original flexible JSON payload because no product sync records existed in the database at implementation time. The importer recognizes common product IDs/names and retains all supplied product fields. Confirm the desktop product payload contract before relying on additional product-specific fields.

The importer runs synchronously during the API request so successful sales are immediately available. No queue worker is required for sale imports. A recovery command retries pending, interrupted, and failed imports:

```sh
php artisan kikimo:recover-syncs --limit=100
```

For cPanel, configure the scheduler cron once per minute:

```cron
* * * * * cd /absolute/path/to/application && php artisan schedule:run >> /dev/null 2>&1
```

Terminal bearer tokens are stored as hashes; rotate a token immediately if it is exposed. Restrict access to `.env`, logs, and the application storage directory.

## Production web server

Set the web server document root to the project's `public` directory, use HTTPS, set `APP_ENV=production` and `APP_DEBUG=false`, and run `php artisan storage:link` only if public storage is needed. Keep the project root and `.env` outside the public document root.

## Tests and assets

```sh
php artisan test
npm run build
```
