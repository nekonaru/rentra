<div align="center">
  <img src="assets/rentra-mark.png" alt="Rentra logo" width="120" />
  <h1>Rentra: Online Booking for Small Rentals</h1>
  <p><b>Turn "Is it still available?" into one public link with live availability,<br/>
  automatic pricing, and down payment proof verification.</b></p>

  <a href="https://github.com/nekonaru/rentra/actions/workflows/ci.yml"><img src="https://github.com/nekonaru/rentra/actions/workflows/ci.yml/badge.svg" alt="CI" /></a>
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/Platform-Web-0F766E?style=flat-square" alt="Platform" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License" />
  <img src="https://img.shields.io/badge/Status-Active-brightgreen?style=flat-square" alt="Status" />
</div>

<br />

**English** | [Bahasa Indonesia](README.id.md)

> [!NOTE]
> Rentra ships its UI in Bahasa Indonesia. The codebase, README, and commits are in English.

---

## Table of Contents

- [About](#about)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Configuration](#configuration)
- [Project Structure](#project-structure)
- [Development](#development)
- [Testing](#testing)
- [Security](#security)
- [Continuous Integration](#continuous-integration)
- [FAQ](#faq)
- [Author](#author)
- [Disclaimer](#disclaimer)
- [License](#license)

---

## About

**Rentra** gives small and medium rental businesses (cameras, lenses, and other gear) a single public link with unit availability, a live price calculator, and a booking flow with down payment proof upload. The owner gets a dashboard to handle units, bookings, and payment verification, replacing the daily "kak, masih ready?" chat.

> _"One link, no more 'is it available?' messages."_

---

## Features

### Public link and booking

| Feature | Description |
|---------|-------------|
| **Public unit pages** | One link per rental showing active units, photos, and rates |
| **Availability calendar** | Monthly calendar per unit: available, partially booked, fully booked |
| **Live price calculator** | Price breakdown with full days, remaining hours capped at the day rate, tolerated minutes, and minimal rent |
| **Hold with countdown** | Units are held for a configurable time (default 60 minutes) after booking |
| **DP proof upload** | Renters upload JPG, PNG, or PDF proof (max 5 MB), stored privately |

### Owner dashboard

| Feature | Description |
|---------|-------------|
| **Onboarding in minutes** | Guided wizard: rental profile, then unit and rate in one flow |
| **Unit and rate CRUD** | Name, category, photo, active/inactive toggle, daily and hourly rates |
| **DP verification queue** | Accept or reject proofs with a reason; rejected bookings get a fresh hold |
| **Unit blocks** | Block date ranges with a reason; clashes with active bookings require confirmation |
| **Booking lifecycle** | Hand over, mark returned, or cancel; late rentals are flagged automatically after the due time |
| **Late reminders** | One tap opens WhatsApp with a prefilled reminder message |
| **Anti-clash guarantee** | Bookings run in a transaction with a row lock, so two renters can never take the same slot |

### Platform

| Feature | Description |
|---------|-------------|
| **Multi-tenant** | One database; every owner sees only their own data, enforced by policies and tests |
| **Indonesian UI** | Built for Indonesian small businesses, mobile first, works on 360px wide screens |
| **Offline-friendly setup** | No external services: runs on XAMPP (Apache, MySQL/MariaDB) |

---

## Requirements

| Dependency | Minimum Version | Notes |
|------------|-----------------|-------|
| **PHP** | 8.2 | With `pdo_mysql`, `pdo_sqlite`, `gd`, `zip`, `mbstring`, `curl`, `fileinfo`, `openssl` |
| **Composer** | 2.x | Dependency manager for PHP |
| **Node.js** | 20.19 | For Vite and Tailwind builds |
| **MySQL or MariaDB** | 10.4 / 8.0 | As bundled with XAMPP; utf8mb4 |
| **Git** | any recent | To clone the repository |

> [!TIP]
> Running XAMPP? PHP and MySQL/MariaDB are already included. You only need Composer and Node.js.

### Never used a terminal? Start here

<details>
<summary><b>How to open a terminal</b></summary>

On Windows, press `Win + R`, type `cmd`, and press Enter. Or open the Start menu, type `PowerShell`, and press Enter. Then run every command below in that window, from the folder where you put the project.

</details>

---

## Installation

Rentra is a web app. There is no installer; you run it locally or on your own server.

### 1. Get the code

<details>
<summary><b>I have Git installed</b></summary>

```bash
git clone https://github.com/nekonaru/rentra.git
cd rentra
```

</details>

<details>
<summary><b>I don't have Git</b></summary>

Download the ZIP from https://github.com/nekonaru/rentra/archive/refs/heads/main.zip, extract it, then open a terminal inside the extracted folder (not the parent folder).

</details>

### 2. Start Apache and MySQL in XAMPP

Open the XAMPP Control Panel and click **Start** next to **Apache** and **MySQL**.

### 3. Create the database

Open http://localhost/phpmyadmin, click **New**, name it `rentra`, pick `utf8mb4_unicode_ci`, and click **Create**. Or run this in the MySQL console:

```sql
CREATE DATABASE rentra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Install dependencies and prepare the app

```bash
composer install
npm install
cp .env.example .env   # on Windows: copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
```

> [!IMPORTANT]
> `php artisan migrate --seed` must finish without errors. It creates the demo account `demo@rentra.id` with password `password` and a demo rental at `http://localhost:8000/demo-kamera`.

### 5. Run the app

```bash
php artisan serve
```

Then open http://localhost:8000. Log in with `demo@rentra.id` / `password`, or register a fresh account.

### 6. Keep holds and late statuses fresh

The app needs a scheduler run every minute to expire held bookings and flag late returns. In development:

```bash
php artisan schedule:work
```

For a local demo or a production-like setup on Windows, use Task Scheduler:

1. Open **Task Scheduler** and create a task that runs every 1 minute.
2. Set the action to `C:\xampp\php\php.exe` with arguments `C:\path\to\rentra\artisan schedule:run`.

Without the scheduler the app still works: availability checks also ignore holds whose timer has expired.

---

## Usage

### First launch

1. Run `php artisan migrate --seed` (see Installation).
2. Open http://localhost:8000 and register, or log in with `demo@rentra.id` / `password`.
3. Complete the onboarding wizard (rental name, public link slug, WhatsApp number).
4. Add your first unit with its rates.
5. Share `http://localhost:8000/{your-slug}` with your customers.

> [!NOTE]
> The public page only shows slot status, never renter names or contact details.

### Everyday use

- **Share the link.** Customers pick a unit, see the calendar, calculate the price, and book.
- **Verify proofs.** Open **Verifikasi**, review the DP proof, then **Terima** or **Tolak** with a reason.
- **Hand over.** On the **Booking** page, mark **Serah terima** when the unit goes out and **Unit kembali** when it returns.
- **Block dates.** Use **Blok** for maintenance or personal use; overlapping bookings ask for confirmation.
- **Chase late returns.** Late rentals are flagged automatically; **Kirim pengingat WA** opens WhatsApp with the message prefilled.

---

## Configuration

| Variable | Default | Purpose |
|----------|---------|---------|
| `APP_TIMEZONE` | `Asia/Jakarta` | All datetimes are stored and shown in WIB |
| `APP_LOCALE` | `id` | UI language (Bahasa Indonesia) |
| `DB_DATABASE` | `rentra` | MySQL database name |
| `QUEUE_CONNECTION` | `database` | Database queue, no Redis required |
| `FILESYSTEM_DISK` | `local` | Private disk for DP proofs |

Optional but supported: `MAIL_MAILER=log` in `.env` captures mail locally in `storage/logs`.

---

## Project Structure

```
rentra/
├── app/
│   ├── Console/Commands/    # bookings:sweep (scheduler entry point)
│   ├── Enums/               # BookingStatus enum with labels and colors
│   ├── Http/Controllers/    # Web, public, and dashboard controllers
│   ├── Http/Middleware/     # EnsureRentalExists (onboarding guard)
│   ├── Http/Requests/       # Form Request validation
│   ├── Models/              # Rental, Unit, PriceRule, Booking, UnitBlock
│   ├── Policies/            # UnitPolicy, BookingPolicy (tenant isolation)
│   └── Services/            # PriceCalculator, AvailabilityService, BookingService, UnitManager
├── assets/                  # Logo masters and README images
├── public/                  # favicon.svg, apple-touch-icon.png, storage symlink
├── resources/views/         # Blade views (public, dashboard, auth)
├── routes/                  # web.php, auth.php, console.php (schedule: every minute)
├── storage/app/private/     # DP proofs (never public)
├── tests/                   # PHPUnit/Pest feature and unit tests
├── .env.example             # Sample environment file
├── AGENTS.md                # Project variables and contributor notes
├── README.md                # English readme (source of truth)
└── README.id.md             # Bahasa Indonesia readme
```

---

## Development

### Making changes

1. Create a branch and edit the code.
2. Keep business logic in `app/Services`, not controllers or Blade.
3. Run `vendor/bin/pint` to format, then `php artisan test` and make sure everything passes.
4. Commit with `type: short description` (`feat:`, `fix:`, `docs:`, `chore:`, ...).

> [!WARNING]
> Do not edit `public/build` by hand. It is generated by `npm run build` and ignored by git.

### Adding a booking status

1. Add the case to `app/Enums/BookingStatus.php` with its label and color.
2. Update `slotReserve()` if the status holds a slot.
3. Update `bookings:sweep` in `app/Console/Commands/BookingsSweep.php` if it can transition automatically.

---

## Testing

```bash
php artisan test        # feature and unit tests (SQLite in-memory)
vendor/bin/pint --test  # code style check
npm run build           # frontend builds without errors
```

| Check | Requirement |
|-------|-------------|
| Test suite | All tests pass (currently 61 tests) |
| Pint | Zero style issues |
| Frontend build | `npm run build` completes without errors |
| Security | `composer audit` reports no known advisories |

---

## Security

- Authentication is session based (Laravel Breeze) and all state changes use CSRF tokens.
- DP proofs are validated by MIME and size, stored on a private disk, and served only through an authorized route.
- Public booking and upload endpoints are rate limited.
- Every dashboard query is scoped to the owner's rental; cross-tenant access is covered by tests.
- Dependencies are audited with `composer audit`.

> [!NOTE]
> Report vulnerabilities privately via the process described in [`SECURITY.md`](SECURITY.md). Do not open public issues for security reports.

---

## Continuous Integration

| Workflow | Purpose |
|----------|---------|
| `.github/workflows/ci.yml` | Install dependencies, build assets, run the full test suite on every push and PR to `main` |

The CI runs on `main` and `v*` tags. Tests use the same SQLite in-memory setup as local runs, so CI is the source of truth when local tooling is unavailable.

---

## FAQ

<details>
<summary><b>Do I need a server and a database?</b></summary>

No paid services. XAMPP gives you Apache and MySQL/MariaDB locally. For anything small, a cheap VPS or shared hosting with PHP 8.2 is enough.

</details>

<details>
<summary><b>How do I extend the hold time?</b></summary>

The hold duration is set once during onboarding. To change it later, update the `durasi_hold_menit` column on the `rentals` table, or adjust it in the seeder before your next `migrate:fresh`. Default is 60 minutes.

</details>

<details>
<summary><b>Does Rentra send WhatsApp messages for me?</b></summary>

No. It opens `wa.me` links with the message prefilled, so you review and press send yourself. No WhatsApp API keys are needed.

</details>

<details>
<summary><b>Where do DP proofs go?</b></summary>

They are stored in `storage/app/private/bukti` and are only reachable through an authenticated, authorized route. They are never in the public web root.

</details>

<details>
<summary><b>Does the demo account really work?</b></summary>

Yes. `php artisan migrate --seed` creates `demo@rentra.id` with password `password` and a demo rental at `/demo-kamera` with three units.

</details>

<details>
<summary><b>Can I build this into mobile apps?</b></summary>

No. Rentra is a mobile-first web app; keep it a website or wrap it in a webview yourself if you need one.

</details>

---

## Author

<div align="center">

| [![Nicolas Dwi Dharma](https://github.com/nekonaru.png?size=100)](https://github.com/nekonaru) |
|:---:|
| **Nicolas Dwi Dharma** |
| [github.com/nekonaru](https://github.com/nekonaru) |

</div>

---

## Disclaimer

Rentra is a tool for managing bookings. Rental agreements, identity checks, and payment disputes stay between the owner and the renter. WhatsApp messages open in your own account; no message is sent without you pressing send.

---

## License

Distributed under the **MIT License**. See [`LICENSE`](LICENSE) for more information.
