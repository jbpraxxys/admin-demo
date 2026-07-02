# PRAXXYS Demo Platform

A demo-hosting platform for sharing client website demos. PMs create projects, upload demo files, and share password-protected links with clients. Built with Laravel, Inertia.js, and Vue 3.

## Features

- **Project management** — create projects with a URL `slug`, client name, and a `demo_password`.
- **File manager** — upload and delete demo files (HTML, images, assets) per project. Files are served directly from `public/projects/{slug}/`.
- **Protected demos** — each project is gated behind HTTP Basic Auth via an `.htpasswd` file generated from the project's demo password.
- **Roles** — `admin` manages all projects and users; `pm` manages only their own projects.
- **Admin area** — user management (create / delete users).

## Tech Stack

- PHP 8.3+ / Laravel 13
- Inertia.js + Vue 3
- Tailwind CSS
- Vite

## Requirements

- PHP >= 8.3 (with the usual Laravel extensions)
- Composer
- Node.js & npm
- A database (MySQL recommended; SQLite works for local dev)

## Installation

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# Configure your DB credentials in .env, then:
php artisan migrate

npm run dev         # frontend (run in its own terminal)
php artisan serve   # serves the app at http://localhost:8000
```

## Usage

1. Log in (or register) as a PM/admin.
2. Create a project — choose a unique **slug** and a **demo password**.
3. From the project page, upload your demo files.
4. Share the demo link with the client: `https://<host>/projects/{slug}/index.html`. The client enters the demo password to view it.

## Notes

- Uploaded demo files live in `public/projects/{slug}/` and are gitignored (runtime content, not version controlled).
- `server.php` normalizes request routing for `php artisan serve` so admin routes under `/projects/{slug}/...` don't collide with the on-disk demo-files directory.

## License

Proprietary — © PRAXXYS.
