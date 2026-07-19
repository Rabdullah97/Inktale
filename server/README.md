# InkTale — PHP + MySQL version

A bookstore web application built with **PHP** and **MySQL**, offering a bilingual (Arabic / English) catalogue, a guest shopping cart, and a protected admin panel for managing inventory.

Developed as a university project at the College of Computer Science and Information Technology, Imam Abdulrahman Bin Faisal University.

---

## Features

**Storefront**
- Browse a catalogue of 40+ Arabic and English titles
- Filter by category and language, with live client-side search
- Detail page for every book, including author biography and cover art
- Session-backed shopping cart — add, update quantity, remove, checkout
- Purchase history kept per visitor ("My Library")
- Store location page with contact details

**Admin panel**
- Password-protected login with hashed credentials
- Add, edit and delete books
- Cover image upload with type and size validation
- Search across the inventory

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8 |
| Database | MySQL 8 (utf8mb4) |
| Frontend | HTML5, CSS3, vanilla JavaScript |
| Server | Apache (XAMPP / WAMP) |

No frameworks or build step — the project runs on a stock LAMP/XAMPP stack.

---

## Getting Started

### Requirements
- PHP 8.0 or newer
- MySQL 5.7 or newer
- Apache (XAMPP, WAMP, MAMP, or a system install)

### Installation

**1. Place the *repository root* in your web root**

Point Apache at the repository root, not at this folder — the book covers live at
the root and are shared with the static version of the site.

```bash
git clone https://github.com/Rabdullah97/InkTale.git
# XAMPP on Windows:  C:\xampp\htdocs\InkTale
# macOS / Linux:     /opt/lampp/htdocs/InkTale
```

The PHP app then runs at `http://localhost/InkTale/server/`.

**2. Create the database**

Open phpMyAdmin at `http://localhost/phpmyadmin`, create a database named `inktale_simple_db`, then import:

```
server/database/inktale_simple_db.sql
```

Or from the command line:

```bash
mysql -u root -p -e "CREATE DATABASE inktale_simple_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p inktale_simple_db < server/database/inktale_simple_db.sql
```

**3. Configure the connection**

```bash
cp config.example.php config.php
```

Then edit `config.php` with your MySQL credentials. This file is git-ignored, so your credentials stay local.

**4. Start Apache and MySQL, then open**

```
http://localhost/InkTale/server/index.php
```

### Demo admin account

| Username | Password |
|---|---|
| `admin` | `Admin@123` |

Change this before using the project anywhere public. To generate a replacement hash:

```bash
php -r "echo password_hash('YourNewPassword', PASSWORD_DEFAULT), PHP_EOL;"
```

Then update the `password` column of the `admins` table with the output.

---

## Project Structure

```
InkTale/
├── index.php              # Landing page and featured books
├── browse.php             # Catalogue with filters and search
├── product.php            # Book detail page
├── cart.php               # Shopping cart and checkout
├── mylibrary.php          # Purchase history
├── location.php           # Store location and contact
│
├── admin_login.php        # Admin authentication
├── admin.php              # Inventory dashboard
├── admin_save.php         # Create / update a book
├── admin_delete.php       # Delete a book
├── admin_logout.php
│
├── add_to_cart.php        # Cart endpoints
├── update_cart.php
├── clear_cart.php
│
├── functions.php          # Shared bootstrap: session, escaping, cart, auth
├── db.php                 # Database connection
├── config.example.php     # Credential template
├── footer.php             # Shared footer partial
│
├── database/              # SQL schema and seed data
├── dataset/               # Book metadata source file
├── bookIMG/               # Book cover images
└── images/authors/        # Author portraits
```

---

## Architecture Notes

**Single shared bootstrap.** `functions.php` is included by every page and owns the session, the guest cookie, HTML escaping (`h()`), image path resolution (`imagePath()`), the cart API, and admin authentication. Pages contain no duplicated helper definitions.

**Cart storage.** The cart lives in `$_SESSION['cart']` as `[product_id => quantity]` and is sanitised to positive integers on every read, so a tampered session cannot inject arbitrary values into queries.

**Security measures.**
- All database access uses prepared statements with bound parameters
- Admin passwords are stored as bcrypt hashes and checked with `password_verify()`
- The session id is regenerated on login to prevent session fixation
- Uploaded images are validated by inspecting actual file contents via `getimagesize()`, not by the client-supplied filename or MIME type, and are renamed to a safe slug
- All dynamic output is escaped with `h()` before rendering

---

## License

Released for educational purposes.
