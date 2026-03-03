# CarnetPro

CarnetPro is a Laravel MVC monolith for flatshare management. It handles invitations, shared expenses, balance calculation, simplified settlements, payment history, reputation changes and a global admin dashboard.

## Stack

- Laravel monolith with MVC controllers, Blade views, Form Requests and Eloquent
- Tailwind CSS through Vite
- MySQL application database named `carnetpro`

## Features

- Login, register and profile pages
- First registered user becomes the global admin
- Flatshare CRUD with active/cancelled statuses
- Invitation by email token
- One active flatshare at a time per user
- Shared expenses with categories and month filtering
- Settlement calculation with recorded payments
- Reputation updates on leave or removal
- Debt transfer to the owner through the `adjustments` table
- Global admin dashboard with ban/unban actions

## Installation

```bash
git clone <your-repo-url> CarnetPro
cd CarnetPro
composer install
npm install
cp .env.example .env
php artisan key:generate
```

## MySQL configuration

Create a database named `carnetpro`, then use:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=carnetpro
DB_USERNAME=root
DB_PASSWORD=
```

## Database setup

```bash
php artisan migrate
php artisan db:seed
```

## Run locally

```bash
npm run dev
php artisan serve
```

Open `http://127.0.0.1:8000`.

## Local mail configuration

For local development, the safest default is to log emails instead of trying to reach a real SMTP server:

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@carnetpro.test"
MAIL_FROM_NAME="CarnetPro"
```

With this setup, invitation emails are written to `storage/logs/laravel.log`.

If you want real inbox delivery, switch to SMTP in `.env`. Example with Gmail:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_gmail_app_password
MAIL_FROM_ADDRESS="your_gmail@gmail.com"
MAIL_FROM_NAME="CarnetPro"
```

Then clear Laravel config cache:

```bash
php artisan config:clear
```

Important:

- You must use a Gmail app password, not your normal Gmail password.
- The Gmail account should have 2-Step Verification enabled before creating the app password.
- If the credentials are valid, invitations sent from the flatshare page will go to the real recipient inbox.
- You can also test SMTP quickly with `http://127.0.0.1:8000/mail-test?email=you@example.com`.

## Demo accounts

- `admin@carnetpro.test` / `password`
- `owner@carnetpro.test` / `password`
- `member1@carnetpro.test` / `password`
- `member2@carnetpro.test` / `password`

## Commands

```bash
php artisan migrate
php artisan db:seed
php artisan test
```

## Business notes

- A user cannot create or accept a second active flatshare.
- The owner cannot leave. They must cancel or delete the flatshare.
- Removing a member with debt creates an internal adjustment so the owner takes that debt.
- Payments are stored and fed back into the settlement calculation.

## Tests

Feature tests cover:

- invitation flow
- multi-flatshare blocking
- settlements
- ban middleware
- owner permissions
- mark paid flow
