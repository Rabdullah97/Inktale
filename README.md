# InkTale — Online Bookstore

A bilingual online bookstore. Browse an Arabic and English catalogue of 46 titles, filter by category and language, search by title or author, read about each book and its author, and build a cart.

**▶ [Open the live demo](https://rabdullah97.github.io/Inktale/)**

---

## About this project

A team project built for a web development course at the College of Computer Science and Information Technology, Imam Abdulrahman Bin Faisal University.

The brief was a working online store with a customer-facing catalogue and an admin dashboard, backed by a relational database. The PHP and MySQL build in [`/server`](server/) is what was submitted.

### My contribution — the application layer

I wrote the PHP behind the store: the database access layer, the shopping cart and checkout logic, and the full admin dashboard with authentication, catalogue search, image upload, and create/edit/delete for every book. A teammate designed the MySQL schema; I built everything that runs against it.

I later added the static version at the root of this repository, so the project could be opened from a link rather than installed.

---

### Pages

| Page | What it does |
|---|---|
| `index.html` | Featured carousel and the full catalogue |
| `browse.html` | Category and language filters plus live search |
| `product.html` | Book detail, author biography, add to cart |
| `cart.html` | Cart with quantity controls and checkout |
| `mylibrary.html` | Purchased books, with search and return |
| `location.html` | Store location and contact details |
| `admin.html` | Explains how to run the admin panel locally |

---

## Opening it

The site is static, so there is nothing to install and nothing to run. Just open the link:

**https://rabdullah97.github.io/InkTale/**

It works in any browser, on desktop or phone. The cart is kept in `localStorage`, so it survives a refresh and is still there when you come back.

---

## About the admin panel

The admin dashboard cannot run on GitHub Pages, which serves static files only. Rather than drop the link, `admin.html` keeps it in place and explains where the panel lives and how to run it.

---


## Tech

**Static** — HTML5, CSS3, vanilla JavaScript. No frameworks, no build step.

**Server** — PHP 8, MySQL 8, Apache.

---

Team course project · CCSIT, Imam Abdulrahman Bin Faisal University
Backend application code and static build by [Rayana Abdullah](https://github.com/Rabdullah97)
