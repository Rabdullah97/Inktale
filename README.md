# InkTale — Online Bookstore

A bilingual online bookstore. Browse an Arabic and English catalogue of 46 titles, filter by category and language, search by title or author, read about each book and its author, and build a cart.

**▶ [Open the live demo](https://rabdullah97.github.io/InkTale/)**

<!-- TODO: add a screenshot as docs/screenshot.png, then uncomment -->
<!-- ![InkTale catalogue](docs/screenshot.png) -->

---

## Two versions in one repository

| Folder | What it is | Runs on |
|---|---|---|
| **`/`** (root) | Static site — catalogue from JSON, cart in `localStorage` | Any static host, including GitHub Pages |
| **`/server`** | The original PHP + MySQL application, with the admin panel | Apache + MySQL (XAMPP) |

Both read the same catalogue: `data/books.json` is generated from the MySQL dump by `tools/export_books.py`, so the two can't drift apart.

---

## Pages

| Page | What it does |
|---|---|
| `index.html` | Featured carousel and the full catalogue |
| `browse.html` | Category and language filters plus live search |
| `product.html` | Book detail, author biography, add to cart |
| `cart.html` | Cart with quantity controls and checkout |
| `mylibrary.html` | Purchased books, with search and return |
| `location.html` | Store location and contact details |
| `admin.html` | Explains how to run the admin panel locally |

### About the admin panel

The admin dashboard — login, add, edit and delete books, image upload — is part of the PHP build and cannot run on GitHub Pages, which serves static files only.

Rather than drop the link, `admin.html` keeps it in place and explains where the panel lives and how to run it. The source is in [`/server`](server/).

---

## Running it locally

```bash
git clone https://github.com/Rabdullah97/InkTale.git
cd InkTale
python -m http.server 8000
```

Then open `http://localhost:8000`.

> Opening `index.html` straight from disk won't work — browsers block `fetch()` on `file://` URLs, so the catalogue can't load. Use a local server.

### Regenerating the catalogue

```bash
python tools/export_books.py
```

Reads `server/database/inktale_simple_db.sql` and rewrites `data/books.json`.

---

## The PHP version

Setup instructions are in [`server/README.md`](server/README.md). Demo account: `admin` / `Admin@123`.

The PHP build uses prepared statements throughout, stores admin passwords as bcrypt hashes, regenerates the session id on login, and validates uploaded images by inspecting file contents rather than trusting the filename.

---

## Why it's built this way

The catalogue is 46 books that essentially never change. Running a database in production to serve fixed data means paying for hosting, uptime and maintenance in exchange for flexibility the project doesn't use.

So the deployed version reads a JSON file and keeps the cart in the browser. Nothing to keep alive, and the page opens instantly for anyone who clicks the link.

The PHP version stays in the repository because the admin panel, the authentication and the schema design are a real part of the work. Keeping both makes the trade-off visible rather than hiding it.

---

## Tech

**Static** — HTML5, CSS3, vanilla JavaScript. No frameworks, no build step.
**Server** — PHP 8, MySQL 8, Apache.

---

Built by [Rayana Abdullah](https://github.com/Rabdullah97) · CCSIT, Imam Abdulrahman Bin Faisal University
