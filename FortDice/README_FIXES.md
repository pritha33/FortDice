# Secure Bank — Fixed & Consolidated

## What was wrong
The project was three different, unconnected implementations layered on top of each other:

1. **"FortDice" system** — `config.php` + a single `fort_dice_bank.users` table that was never created in `database.sql`.
2. **"Secure Bank" system** — `database.php` + the real, normalized schema (`admins`, `employees`, `customers`, `transactions`, `encryption_logs`) in `database.sql`, but every page used `require_once '../session.php'` / `'../database.php'` as if the files lived one directory below the shared files — while in the zip everything sat flat in one folder, so those `require_once` calls would fatal-error.
3. **A Firebase-based frontend** (`login.html`, `register.html`, `index.html`, `dashboard.html` + their `.js` files) that bypassed PHP/MySQL entirely and talked to a hardcoded Firebase project instead.

On top of that: two incompatible session schemes (`$_SESSION['role']` vs `$_SESSION['user_type']`), a misspelled `columner.php` required as `columnar.php`, a missing `admin/customers.php` that every admin page linked to but that never existed, and CSS paths pointing at folders that didn't exist.

## What I did
Kept the **Secure Bank** architecture (it's the more complete, correctly-normalized one, and it's the one with real cipher-based decryption rather than fake JS stubs) and actually built the folder structure its own code was already assuming:

```
/
├── index.php               (landing page)
├── database.php            (PDO connection class)
├── session.php             (login/session helpers)
├── database.sql            (secure_bank schema — unchanged, already correct)
├── vigenere.php / columnar.php / railfence.php / hill.php   (cipher implementations)
├── test_encryption.php
├── css/       (style.css, dashboard.css, admin.css, employee.css, customer.css)
├── js/        (admin.js, employee.js, index.js)
├── auth/      (login.php, register.php, logout.php)   ← rewritten
├── admin/     (dashboard.php, employees.php, customers.php, transactions.php, logs.php)
├── employee/  (dashboard.php, customers.php, decrypt.php, transactions.php, profile.php)
└── customer/  (dashboard.php, transfer.php, transactions.php, profile.php)
```

Specifically:
- **Rewrote `auth/login.php`, `auth/register.php`, `auth/logout.php`** to query the real `admins` / `employees` / `customers` tables with `password_verify()`, and to set one consistent set of session keys (`user_id`, `user_type`, `full_name`, plus role-specific fields) that match what every dashboard page already expected.
- **Moved every page into the subfolder its own `../` requires and nav links were already pointing at**, so `require_once '../session.php'`, `../database.php`, `../css/*.css`, `../auth/logout.php` now resolve correctly.
- **Fixed 3 files** that required `../config/database.php` (a path that never existed) to `../database.php`.
- **Renamed `columner.php` → `columnar.php`** to match what `decrypt.php` and `test_encryption.php` already required.
- **Built the missing `admin/customers.php`** (list, suspend/activate, delete — wired to the existing `js/admin.js` functions that had no page to call them from).
- **Deleted the Firebase-based duplicate pages** (`login.html`, `register.html`, `index.html`, `dashboard.html`, `login.js`, `register.js`, `auth.js`, `auth.css`) and the old `config.php` / root `dashboard.php` / `ajax_handler.php` from the FortDice system, since they were a separate, disconnected backend.

## To run it
1. Import `database.sql` into MySQL (creates and seeds `secure_bank`).
2. Point your web server document root at this folder.
3. Visit `index.php` → Register a customer, or log in as:
   - **Admin:** username `admin`, password `password`
   - **Employee (seeded):** email `sarah@securebank.com` or `mike@securebank.com`, password `password`
   - **Customer (seeded):** email `john@email.com`, `jane@email.com`, `robert@email.com`, or `alice@email.com`, password `password`
     (all seeded password hashes in `database.sql` correspond to `password`)

## Note on security
This is still a training/coursework project (plaintext-recoverable classical ciphers, employee security keys stored in the DB, no CSRF tokens, no rate limiting). It's now internally consistent and functional, but I wouldn't deploy it as-is for real banking data.
