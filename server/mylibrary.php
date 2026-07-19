<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$guestName  = guest_name();
$userAvatar = guest_avatar();
function getPurchasedBooksFromCookie() {
    $cookie = $_COOKIE['inktale_purchases'] ?? '';
    if ($cookie === '') return [];

    $data = json_decode($cookie, true);
    return is_array($data) ? $data : [];
}

function savePurchasedBooksToCookie($items) {
    setcookie(
        'inktale_purchases',
        json_encode(array_values($items), JSON_UNESCAPED_UNICODE),
        time() + (86400 * 30),
        '/'
    );
}

function normalizePurchases($purchases) {
    $normalized = [];

    foreach ($purchases as $item) {
        $normalized[] = [
            'product_id' => (int)($item['product_id'] ?? 0),
            'name' => (string)($item['name'] ?? 'Untitled Book'),
            'author_name' => (string)($item['author_name'] ?? 'Unknown Author'),
            'picture' => (string)($item['picture'] ?? ''),
            'qty' => (int)($item['qty'] ?? 1),
            'price' => (float)($item['price'] ?? 0),
        ];
    }

    return $normalized;
}

$message = '';
$error = '';

$purchases = normalizePurchases(getPurchasedBooksFromCookie());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);

    if ($action === 'return' && $productId > 0) {
        $found = false;

        foreach ($purchases as $index => &$item) {
            if ((int)$item['product_id'] === $productId && (int)$item['qty'] > 0) {
                $found = true;
                $conn->begin_transaction();

                try {
                    $stmt = $conn->prepare("UPDATE products SET stock = stock + 1 WHERE product_id = ?");
                    $stmt->bind_param('i', $productId);
                    $stmt->execute();
                    $stmt->close();

                    $item['qty'] -= 1;

                    if ($item['qty'] <= 0) {
                        unset($purchases[$index]);
                    }

                    $conn->commit();
                    $purchases = array_values($purchases);
                    savePurchasedBooksToCookie($purchases);
                    $message = 'The book was returned successfully.';
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = 'An error occurred while returning the book.';
                }

                break;
            }
        }
        unset($item);

        if (!$found && $error === '') {
            $error = 'This book is not in your library.';
        }
    }
}

$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

$totalBooks = 0;
foreach ($purchases as $item) {
    $totalBooks += (int)$item['qty'];
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>InkTale - My Library</title>
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
    --red: #d9534f;
    --green: #5cb85c;
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

  .nav-links { display: flex; gap: 1.5rem; list-style: none; }
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
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--mocha);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gold);
    font-weight: 700;
    font-size: 0.85rem;
    border: 2px solid var(--gold);
  }

  .page-header {
    background: linear-gradient(135deg, var(--brown) 0%, var(--mocha) 100%);
    padding: 2.5rem 2rem 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
  }

  .page-header::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23c9a84c' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
  }

  .header-logo {
    display: flex;
    justify-content: center;
    margin-bottom: 0.5rem;
    position: relative;
  }

  .header-logo img {
    height: 150px;
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

  .search-box { margin-left: auto; position: relative; }

  .search-box input {
    padding: 0.4rem 1rem 0.4rem 2.2rem;
    border-radius: 20px;
    border: 1px solid var(--soft);
    background: var(--cream);
    font-family: 'Lato', sans-serif;
    font-size: 0.85rem;
    color: var(--brown);
    outline: none;
    width: 220px;
    transition: border-color 0.2s;
  }

  .search-box input:focus { border-color: var(--gold); }
  .search-box::before {
    content: '🔍';
    position: absolute;
    left: 0.6rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.75rem;
  }

  .library-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
  }

  .section-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
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

  .msg, .err {
    padding: 12px 14px;
    border-radius: 12px;
    margin-bottom: 16px;
  }

  .msg {
    background: #edf8ed;
    color: #2f7d32;
    border: 1px solid #b7ddb7;
  }

  .err {
    background: #fdeeee;
    color: #b94a48;
    border: 1px solid #e3bcbc;
  }

  .books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.5rem;
  }

  .book-card {
    background: var(--white);
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid var(--warm);
    transition: all 0.25s;
    color: inherit;
    display: block;
  }

  .book-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(59,42,26,0.12);
    border-color: var(--gold);
  }

  .book-card.hidden { display: none; }

  .book-cover {
    height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
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

  .book-info { padding: 0.85rem; }

  .book-info h4 {
    font-family: 'Playfair Display', serif;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
    line-height: 1.3;
  }

  .book-info .author {
    color: var(--mocha);
    font-size: 0.75rem;
    margin-bottom: 0.5rem;
  }

  .book-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
  }

  .book-pages {
    color: var(--mocha);
    font-size: 0.72rem;
  }

  .card-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  .open-btn, .return-btn {
    border: none;
    border-radius: 6px;
    padding: 0.45rem 0.7rem;
    font-family: 'Lato', sans-serif;
    font-size: 0.75rem;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s;
    display: inline-block;
  }

  .open-btn {
    background: var(--brown);
    color: var(--gold);
  }

  .open-btn:hover { background: var(--mocha); }

  .return-btn {
    background: transparent;
    border: 1px solid #e4c9c9;
    color: var(--red);
  }

  .return-btn:hover {
    background: #fff2f2;
  }

  .no-results {
    display: none;
    text-align: center;
    padding: 3rem;
    color: var(--mocha);
    font-size: 0.95rem;
    grid-column: 1/-1;
  }

  .empty-library {
    background: var(--white);
    border: 1px solid var(--warm);
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    color: var(--mocha);
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
    margin: 0 auto 0.5rem;
    display: block;
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
    <li><a href="browse.php">Browse</a></li>
    <li><a href="mylibrary.php" class="active">My Library</a></li>
    <li><a href="cart.php">🛒 Cart<?= $cartCount > 0 ? ' (' . (int)$cartCount . ')' : '' ?></a></li>
    <li><a href="admin_login.php">Are you admin?</a></li>
  </ul>

  <div class="nav-user">
    <div class="avatar"><?= h($userAvatar) ?></div>
    <span><?= h($guestName) ?></span>
  </div>
</nav>

<div class="page-header">
  <div class="header-logo">
    <img src="inktale_logo.png" alt="InkTale">
  </div>
  <p>You have <span><?= (int)$totalBooks ?></span> purchased books</p>
</div>

<div class="filters">
  <span class="filter-label">Search:</span>
  <div class="search-box">
    <input type="text" id="searchInput" placeholder="Search your books...">
  </div>
</div>

<div class="library-container">

  <?php if ($message): ?>
    <div class="msg"><?= h($message) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="err"><?= h($error) ?></div>
  <?php endif; ?>

  <h2 class="section-title">Purchased Books</h2>

  <?php if (empty($purchases)): ?>
    <div class="empty-library">
      <h3>No books in your library yet</h3>
      <p style="margin-top:10px;">Books you buy from the store will appear here.</p>
    </div>
  <?php else: ?>
    <div class="books-grid" id="booksGrid">
      <?php foreach ($purchases as $item): ?>
        <?php $img = imagePath($item['picture'] ?? ''); ?>
        <div class="book-card"
             data-title="<?= h(mb_strtolower($item['name'])) ?>"
             data-author="<?= h(mb_strtolower($item['author_name'])) ?>">
          <div class="book-cover">
            <img src="<?= h($img) ?>" alt="<?= h($item['name']) ?>">
          </div>

          <div class="book-info">
            <h4><?= h($item['name']) ?></h4>
            <p class="author"><?= h($item['author_name']) ?></p>

            <div class="book-meta">
              <span class="book-pages">Purchased</span>
              <span class="book-pages">Qty: <?= (int)$item['qty'] ?></span>
            </div>

            <div class="card-actions">
              <a href="product.php?id=<?= (int)$item['product_id'] ?>" class="open-btn">View Book</a>

              <form method="post" style="margin:0;">
                <input type="hidden" name="action" value="return">
                <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                <button class="return-btn" type="submit">Return</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <p class="no-results" id="noResults">No books found.</p>
    </div>
  <?php endif; ?>
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
  let currentSearch = '';

  document.getElementById('searchInput').addEventListener('input', function() {
    currentSearch = this.value.toLowerCase().trim();
    applyFilters();
  });

  function applyFilters() {
    const cards = document.querySelectorAll('.book-card');
    let visibleCount = 0;

    cards.forEach(card => {
      const title = card.dataset.title;
      const author = card.dataset.author;

      const matchSearch = !currentSearch || title.includes(currentSearch) || author.includes(currentSearch);

      if (matchSearch) {
        card.classList.remove('hidden');
        visibleCount++;
      } else {
        card.classList.add('hidden');
      }
    });

    const noResults = document.getElementById('noResults');
    if (noResults) {
      noResults.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  }

  applyFilters();
</script>

</body>
</html>