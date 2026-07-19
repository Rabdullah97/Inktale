<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$guestName  = guest_name();
$userAvatar = guest_avatar();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = (int)($_POST['product_id'] ?? 0);
    if ($productId > 0) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]++;
        } else {
            $_SESSION['cart'][$productId] = 1;
        }
        $_SESSION['cart_message'] = 'Book added to cart successfully.';
    }
    header('Location: browse.php');
    exit;
}

$cartCount = array_sum($_SESSION['cart']);
$cartMessage = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);

$sql = "SELECT product_id, name, author_name, price, category, language, description, picture FROM products ORDER BY product_id DESC";$result = $conn->query($sql);

$books = [];
$categories = [];
$languages = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;

        $cat = trim((string)($row['category'] ?? ''));
        if ($cat !== '') {
            $categories[$cat] = true;
        }

        $lang = trim((string)($row['language'] ?? ''));
        if ($lang !== '') {
            $languages[$lang] = true;
        }
    }
}

$categories = array_keys($categories);
sort($categories);

$languages = array_keys($languages);
sort($languages);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>InkTale - Browse Books</title>
<link rel="icon" type="image/png" href="inktale_logo.png">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
:root {
   --cream: #f5f0e8;
   --brown: #1b1816;
   --mocha: #130e0a;
   --gold:  #c9a84c;
   --warm:  #e8d9c0;
   --soft:  #d4c4a8;
   --white: #fffdf8;
   --deep-brown: #2c1e12;
   --olive: #8b7a5b;
   --red: #d9534f;
   --green: #5cb85c;
}
.filter-row {
  width: 100%;
  display: flex;
  gap: 0.75rem;
  align-items: center;
  flex-wrap: wrap;
}
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: 'Lato', sans-serif;
  background: var(--cream);
  color: var(--brown);
  min-height: 100vh;
}

nav {
  background: var(--brown);
  padding: 0 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 68px;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 12px rgba(0,0,0,0.3);
}

.nav-logo img {
  height: 50px;
  width: 50px;
  object-fit: cover;
  border-radius: 50%;
  border: 2px solid var(--gold);
  box-shadow: 0 0 10px rgba(201,168,76,0.5);
  background: var(--brown);
}

.nav-links { display: flex; gap: 1.5rem; list-style: none; align-items: center; }
.nav-links a { color: var(--warm); text-decoration: none; font-size: 0.9rem; transition: color 0.2s; }
.nav-links a:hover, .nav-links a.active { color: var(--gold); }

.nav-user {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: var(--warm);
  font-size: 0.9rem;
  text-decoration: none;
}

.avatar {
  width: 34px; height: 34px; border-radius: 50%;
  background: var(--mocha); display: flex; align-items: center;
  justify-content: center; color: var(--gold);
  font-weight: 700; font-size: 0.85rem; border: 2px solid var(--gold);
}

.page-header {
  background: linear-gradient(135deg, var(--brown) 0%, var(--mocha) 100%);
  padding: 2.2rem 2rem 1.8rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.page-header::before {
  content: '';
  position: absolute; inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23c9a84c' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.header-logo {
  display: flex;
  justify-content: center;
  margin-bottom: 0.5rem;
  position: relative;
}

.header-logo img {
  height: 120px;
  width: auto;
  object-fit: contain;
  border-radius: 50%;
  filter: drop-shadow(0 0 25px rgba(201,168,76,0.7)) brightness(1.1);
  animation: glow 3s ease-in-out infinite;
}

@keyframes glow {
  0%, 100% { filter: drop-shadow(0 0 20px rgba(201,168,76,0.6)) brightness(1.1); }
  50% { filter: drop-shadow(0 0 40px rgba(201,168,76,1)) brightness(1.2); }
}

.page-header p { color: var(--soft); font-size: 0.95rem; position: relative; }
.page-header p span { color: var(--gold); font-weight: 700; }

.filters {
  background: var(--white);
  border-bottom: 1px solid var(--warm);
  padding: 1rem 2rem;
  display: flex;
  gap: 0.75rem;
  align-items: center;
  flex-wrap: wrap;
}

.filter-label { color: var(--mocha); font-size: 0.85rem; font-weight: 500; }

.filter-btn {
  padding: 0.42rem 1rem;
  border-radius: 20px;
  border: 1px solid var(--soft);
  background: transparent;
  color: var(--mocha);
  font-family: 'Lato', sans-serif;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
}

.filter-btn:hover { border-color: var(--gold); color: var(--brown); }
.filter-btn.active { background: var(--brown); color: var(--gold); border-color: var(--brown); }

.search-box { margin-left: auto; position: relative; }

.search-box input {
  padding: 0.45rem 1rem 0.45rem 2.2rem;
  border-radius: 20px;
  border: 1px solid var(--soft);
  background: var(--cream);
  font-family: 'Lato', sans-serif;
  font-size: 0.85rem;
  color: var(--brown);
  outline: none;
  width: 230px;
}

.search-box input:focus { border-color: var(--gold); }
.search-box::before {
  content: '🔍';
  position: absolute;
  left: 0.65rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 0.78rem;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.35rem;
  color: var(--brown);
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.section-title::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--soft);
}

.books-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.4rem;
}

.book-card {
  background: var(--white);
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid var(--warm);
  transition: all 0.25s;
  display: block;
}

.book-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(59,42,26,0.12);
  border-color: var(--gold);
}

.book-card.hidden { display: none; }

.book-cover {
  height: 250px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3.2rem;
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #8b5e3c, #3b2a1a);
}

.book-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.book-cover::after {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 8px;
  background: rgba(0,0,0,0.15);
}

.book-info { padding: 1rem; }

.book-info h4 {
  font-family: 'Playfair Display', serif;
  font-size: 1rem;
  margin-bottom: 0.25rem;
  line-height: 1.35;
}

.book-info .author {
  color: var(--mocha);
  font-size: 0.78rem;
  margin-bottom: 0.55rem;
}

.book-info .desc {
  color: var(--mocha);
  font-size: 0.82rem;
  line-height: 1.6;
  min-height: 55px;
}

.book-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.85rem;
  gap: 8px;
}

.price {
  font-weight: 700;
  color: var(--brown);
}

.card-actions {
  display: flex;
  gap: 0.45rem;
  margin-top: 0.85rem;
}

.btn {
  flex: 1;
  border: none;
  border-radius: 999px;
  padding: 0.65rem 0.8rem;
  cursor: pointer;
  text-decoration: none;
  text-align: center;
  font-family: 'Lato', sans-serif;
  font-size: 0.82rem;
  transition: 0.2s;
}

.btn-primary {
  background: var(--brown);
  color: var(--gold);
}

.btn-primary:hover {
  background: var(--mocha);
  color: white;
}

.btn-outline {
  background: transparent;
  color: var(--brown);
  border: 1px solid var(--soft);
}

.btn-outline:hover {
  border-color: var(--gold);
  color: var(--brown);
  background: var(--cream);
}

.no-results {
  display: none;
  text-align: center;
  padding: 3rem;
  color: var(--mocha);
  font-size: 0.95rem;
}

.notice-success {
  background: #edf8ed;
  color: #2d6a2d;
  border: 1px solid #b7ddb7;
  padding: 14px 16px;
  border-radius: 12px;
  margin-bottom: 22px;
}

footer {
  background: var(--brown);
  color: var(--soft);
  text-align: center;
  padding: 2rem;
  font-size: 0.8rem;
  margin-top: 3rem;
}

.footer-logo {
  height: 50px;
  width: auto;
  object-fit: contain;
  mix-blend-mode: screen;
  filter: drop-shadow(0 0 8px rgba(201,168,76,0.5));
  margin-bottom: 0.5rem;
  display: block;
  margin-left: auto;
  margin-right: auto;
}

.footer-links {
  margin-top: 12px;
  display: flex;
  justify-content: center;
  gap: 16px;
  flex-wrap: wrap;
}

.footer-links a {
  color: var(--gold);
  text-decoration: none;
}

@media (max-width: 780px) {
  .search-box {
    margin-left: 0;
    width: 100%;
  }
  .search-box input {
    width: 100%;
  }
}
</style>
</head>
<body>

<nav>
  <div class="nav-logo">
    <img src="inktale_logo.png" alt="InkTale">
  </div>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="browse.php" class="active">Browse</a></li>
    <li><a href="mylibrary.php">My Library</a></li>
    <li><a href="cart.php">🛒 Cart<?= $cartCount > 0 ? ' (' . (int)$cartCount . ')' : '' ?></a></li>
    <li><a href="admin_login.php">Are you admin?</a></li>
  </ul>
  <div class="nav-user">
    <div class="avatar" id="userAvatar"><?= h($userAvatar) ?></div>
    <span id="userName"><?= h($guestName) ?></span>
  </div>
</nav>

<div class="page-header">
  <div class="header-logo">
    <img src="inktale_logo.png" alt="InkTale">
  </div>
  <p>Explore books by <span>category</span> and discover your next read</p>
</div>

<div class="filters">
  <div class="filter-row">
    <span class="filter-label">Categories:</span>
    <button class="filter-btn active" data-filter="all">All</button>
    <?php foreach ($categories as $category): ?>
      <button class="filter-btn" data-filter="<?= h($category) ?>"><?= h($category) ?></button>
    <?php endforeach; ?>

    <div class="search-box">
      <input type="text" id="searchInput" placeholder="Search books...">
    </div>
  </div>

  <div class="filter-row">
    <span class="filter-label">Languages:</span>
    <button class="filter-btn active" data-language="all">All Languages</button>
    <?php foreach ($languages as $language): ?>
      <button class="filter-btn" data-language="<?= h($language) ?>"><?= h($language) ?></button>
    <?php endforeach; ?>
  </div>
</div>

<div class="container">
  <?php if ($cartMessage): ?>
    <div class="notice-success"><?= h($cartMessage) ?></div>
  <?php endif; ?>

  <h2 class="section-title">Browse Collection</h2>

  <div class="books-grid" id="booksGrid">
    <?php if (!empty($books)): ?>
      <?php foreach ($books as $index => $book): ?>
        <?php
          $category = trim((string)($book['category'] ?? 'General'));
          if ($category === '') $category = 'General';
          $title = trim((string)($book['name'] ?? 'Untitled Book'));
          $author = trim((string)($book['author_name'] ?? 'Unknown Author'));
          $desc = trim((string)($book['description'] ?? ''));
          $img = imagePath($book['picture'] ?? '');
        ?>
     <div class="book-card"
     data-category="<?= h($category) ?>"
     data-language="<?= h(trim((string)($book['language'] ?? 'Unknown'))) ?>"
     data-title="<?= h(mb_strtolower($title)) ?>"
     data-author="<?= h(mb_strtolower($author)) ?>">

          <div class="book-cover">
            <?php if (file_exists($img)): ?>
              <img src="<?= h($img) ?>" alt="<?= h($title) ?>">
            <?php else: ?>
              📚
            <?php endif; ?>
          </div>

          <div class="book-info">
            <h4><?= h($title) ?></h4>
            <p class="author"><?= h($author) ?></p>
            <p class="desc"><?= h($desc !== '' ? $desc : 'No description available for this book.') ?></p>

            <div class="book-meta">
              <span class="price"><?= number_format((float)$book['price'], 2) ?> SAR</span>
              <span><?= h($category) ?></span>
            </div>

            <div class="card-actions">
              <a href="product.php?id=<?= (int)$book['product_id'] ?>" class="btn btn-outline">Details</a>

              <form method="post" style="flex:1; margin:0;">
                <input type="hidden" name="add_to_cart" value="1">
                <input type="hidden" name="product_id" value="<?= (int)$book['product_id'] ?>">
                <button type="submit" class="btn btn-primary" style="width:100%;">Add to Cart</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <p class="no-results" id="noResults">No books found.</p>
</div>

<footer>
  <img src="inktale_logo.png" alt="InkTale" class="footer-logo">
  <p>InkTale — Where stories find you ✨ © <?= date('Y') ?></p>
  <div class="footer-links">
    <a href="tel:+966500000000">Contact: +966 50 000 0000</a>
    <a href="location.php">Location</a>
    <a href="admin_login.php">Are you admin?</a>
  </div>
</footer>

<script>
let currentFilter = 'all';
let currentLanguage = 'all';
let currentSearch = '';

document.querySelectorAll('.filter-btn[data-filter]').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.filter-btn[data-filter]').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    currentFilter = this.dataset.filter;
    applyFilters();
  });
});

document.querySelectorAll('.filter-btn[data-language]').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.filter-btn[data-language]').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    currentLanguage = this.dataset.language;
    applyFilters();
  });
});

document.getElementById('searchInput').addEventListener('input', function() {
  currentSearch = this.value.toLowerCase().trim();
  applyFilters();
});

function applyFilters() {
  const cards = document.querySelectorAll('.book-card');
  let visibleCount = 0;

  cards.forEach(card => {
    const category = card.dataset.category;
    const language = card.dataset.language;
    const title = card.dataset.title;
    const author = card.dataset.author;

    const matchFilter = currentFilter === 'all' || category === currentFilter;
    const matchLanguage = currentLanguage === 'all' || language === currentLanguage;
    const matchSearch = !currentSearch || title.includes(currentSearch) || author.includes(currentSearch);

    if (matchFilter && matchLanguage && matchSearch) {
      card.classList.remove('hidden');
      visibleCount++;
    } else {
      card.classList.add('hidden');
    }
  });

  document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
}

applyFilters();
</script>

</body>
</html>