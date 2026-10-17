# Vibe App Laravel Starter

Vibe App is a clean Laravel 13 starter project for a future AI-powered business generator. It includes a homepage, a CSRF-protected form, basic server-side validation, and a placeholder generation response. The frontend requires no Node.js build step: Tailwind CSS is loaded from its browser CDN, while project CSS and JavaScript are served directly from `public/`.

## Requirements

- PHP 8.3 or newer
- Composer 2
- MySQL 8 or a compatible MariaDB release
- PHP extensions required by Laravel: Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, and XML

## Install a new copy from scratch

Create a fresh Laravel project with the latest stable release:

```bash
composer create-project laravel/laravel vibe-app
cd vibe-app
```

This repository is already created and customized. To install its PHP dependencies after cloning or uploading it, run:

```bash
composer install
```

Create the local environment file on Windows PowerShell:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

On macOS or Linux:

```bash
cp .env.example .env
php artisan key:generate
```

Update the MySQL placeholders in `.env` before using database-backed features:

```dotenv
APP_NAME="Vibe App"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

Start the local development server:

```bash
php artisan serve
```

Open `http://localhost:8000` in a browser. No `npm install`, `npm run dev`, or Vite build is required.

## Application routes

| Method | URI | Controller method | Result |
| --- | --- | --- | --- |
| GET | `/` | `HomeController@index` | Renders `resources/views/home.blade.php` |
| POST | `/generate` | `HomeController@generate` | Validates `business_type` and returns `Generated successfully` |

The form includes Blade's `@csrf` directive. The `business_type` field is required, must be text, and may contain at most 100 characters.

## Project folder structure

Generated dependency internals under `vendor/` and runtime cache files are intentionally collapsed, but every application-level directory and key file is shown below.

```text
vibe-app/
|-- app/
|   |-- Http/Controllers/
|   |   |-- Controller.php
|   |   `-- HomeController.php
|   |-- Models/User.php
|   `-- Providers/AppServiceProvider.php
|-- bootstrap/
|   |-- cache/
|   |-- app.php
|   `-- providers.php
|-- config/
|   |-- app.php
|   |-- auth.php
|   |-- cache.php
|   |-- database.php
|   |-- filesystems.php
|   |-- logging.php
|   |-- mail.php
|   |-- queue.php
|   |-- services.php
|   `-- session.php
|-- database/
|   |-- factories/UserFactory.php
|   |-- migrations/
|   |   |-- 0001_01_01_000000_create_users_table.php
|   |   |-- 0001_01_01_000001_create_cache_table.php
|   |   `-- 0001_01_01_000002_create_jobs_table.php
|   `-- seeders/DatabaseSeeder.php
|-- public/
|   |-- css/app.css
|   |-- js/app.js
|   |-- .htaccess
|   |-- favicon.ico
|   |-- index.php
|   `-- robots.txt
|-- resources/
|   `-- views/home.blade.php
|-- routes/
|   |-- console.php
|   `-- web.php
|-- storage/
|   |-- app/
|   |-- framework/
|   `-- logs/
|-- tests/
|   |-- Feature/Http/Controllers/HomeControllerTest.php
|   `-- TestCase.php
|-- vendor/                         # Composer dependencies
|-- .env                            # Local secrets; never commit
|-- .env.example                    # Safe configuration template
|-- .gitignore
|-- artisan
|-- composer.json
|-- composer.lock
|-- phpunit.xml
`-- README.md
```

## Shared hosting deployment

1. Select PHP 8.3 or newer and enable Laravel's required PHP extensions in the hosting panel.
2. Upload the complete project outside the public web root when possible.
3. Set the domain's document root or web directory to the project's `public/` directory. For example, if the project is `/home/username/vibe-app`, the full document root is `/home/username/vibe-app/public`. In DreamHost's Web directory field, this is typically entered as `vibe-app/public`. Never point the domain at the Laravel project root because `.env` and other private files would become exposed.
4. Create a production `.env` from `.env.example`. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain.example`, a fresh `APP_KEY`, and the real MySQL credentials.
5. Run the following commands from the project root over SSH:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan optimize
chmod -R ug+rw storage bootstrap/cache
```

Generate the application key only during the first deployment. Replacing the key later invalidates encrypted cookies and any data encrypted with the previous key.

The included `public/.htaccess` routes Apache requests through Laravel's `public/index.php`. On DreamHost, the domain web directory can be changed from the website settings panel; set it to the project's `public` folder.

If Composer is unavailable on the host, run the production Composer install locally with a compatible PHP version and upload the resulting `vendor/` directory with the rest of the application.

## Verification commands

Run the automated feature tests:

```bash
php artisan test --compact
```

List the registered routes:

```bash
php artisan route:list --except-vendor
```

Check production configuration before going live:

```bash
php artisan about --only=environment
```

## Tailwind CDN constraint

This starter intentionally follows the no-Node.js, CDN-only requirement. Tailwind's official browser CDN is convenient because it removes the build step, but Tailwind documents it as a development option rather than its preferred production setup. For a high-traffic production site, replace the browser CDN with a precompiled static Tailwind stylesheet when a separate build process becomes available; the Laravel backend and shared-hosting layout do not need to change.

Official references: [Laravel deployment](https://laravel.com/framework/docs/deployment), [DreamHost web directories](https://help.dreamhost.com/hc/en-us/articles/360041534491-Changing-the-web-directory-assigned-to-a-domain), and [Tailwind Play CDN](https://tailwindcss.com/docs/installation/play-cdn).
