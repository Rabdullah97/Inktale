<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$guestName  = guest_name();
$userAvatar = guest_avatar();
function parseDatasetFile($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }

    $content = file_get_contents($filePath);
    if ($content === false || trim($content) === '') {
        return [];
    }

    $blocks = preg_split('/\R-{20,}\R/u', $content);
    $items = [];

    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') continue;

        $item = [
            'Title' => '',
            'Author' => '',
            'Language' => '',
            'Category' => '',
            'JSON' => '',
            'Book Description' => '',
            'Author Bio' => '',
            'Author Image' => '',
        ];

        $lines = preg_split('/\R/u', $block);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            if (preg_match('/^Title:\s*(.+)$/u', $line, $m)) {
                $item['Title'] = trim($m[1], " \t\n\r\0\x0B\"");
            } elseif (preg_match('/^Author:\s*(.+)$/u', $line, $m)) {
                $item['Author'] = trim($m[1], " \t\n\r\0\x0B\"");
            } elseif (preg_match('/^Language:\s*(.+)$/u', $line, $m)) {
                $item['Language'] = trim($m[1], " \t\n\r\0\x0B\"");
            } elseif (preg_match('/^Category:\s*(.+)$/u', $line, $m)) {
                $item['Category'] = trim($m[1], " \t\n\r\0\x0B\"");
            } elseif (preg_match('/^JSON:\s*(.+)$/u', $line, $m)) {
                $item['JSON'] = trim($m[1], " \t\n\r\0\x0B\"");
            } elseif (preg_match('/^Book Description:\s*(.+)$/u', $line, $m)) {
                $item['Book Description'] = trim($m[1], " \t\n\r\0\x0B\"");
            } elseif (preg_match('/^Author Bio:\s*(.+)$/u', $line, $m)) {
                $item['Author Bio'] = trim($m[1], " \t\n\r\0\x0B\"");
            } elseif (preg_match('/^Author Image:\s*(.+)$/u', $line, $m)) {
                $item['Author Image'] = trim($m[1], " \t\n\r\0\x0B\"");
            }
        }

        if ($item['Title'] !== '' || $item['Author'] !== '') {
            $items[] = $item;
        }
    }

    return $items;
}

function normalizeText($text) {
    $text = trim((string)$text);
    $text = mb_strtolower($text, 'UTF-8');
    $text = str_replace(
        ['أ','إ','آ','ى','ؤ','ئ','ة','ـ','’',"'","`","“","”"],
        ['ا','ا','ا','ي','و','ي','ه','','','','','',''],
        $text
    );
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim($text);
}

function findDatasetEntry($dataset, $bookTitle, $authorName) {
    $normTitle = normalizeText($bookTitle);
    $normAuthor = normalizeText($authorName);

    foreach ($dataset as $entry) {
        if (normalizeText($entry['Title'] ?? '') === $normTitle) {
            return $entry;
        }
    }

    foreach ($dataset as $entry) {
        if (normalizeText($entry['Author'] ?? '') === $normAuthor) {
            return $entry;
        }
    }

    return null;
}

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare('SELECT * FROM products WHERE product_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

if (!$book) {
    http_response_code(404);
    die('Product not found');
}

$image = imagePath($book['picture'] ?? '');

$datasetPaths = [
    __DIR__ . '/FINdataset(1).txt',
    __DIR__ . '/FINdataset.txt',
    __DIR__ . '/dataset/FINdataset(1).txt',
    __DIR__ . '/dataset/FINdataset.txt',
];

$dataset = [];
foreach ($datasetPaths as $path) {
    if (file_exists($path)) {
        $dataset = parseDatasetFile($path);
        break;
    }
}

$datasetEntry = findDatasetEntry($dataset, $book['name'] ?? '', $book['author_name'] ?? '');

$authorBio = '';
$authorImage = '';

if ($datasetEntry) {
    $authorBio = $datasetEntry['Author Bio'] ?? '';
    $authorImage = imagePath($datasetEntry['Author Image'] ?? '');
}

if ($authorBio === '') {
    $authorBio = 'No author biography available.';
}

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
    }
    header('Location: cart.php');
    exit;
}

$cartCount = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($book['name']) ?> - InkTale</title>
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
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

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

    .nav-links {
      display: flex;
      gap: 1.5rem;
      list-style: none;
      align-items: center;
    }

    .nav-links a {
      color: var(--warm);
      text-decoration: none;
      font-size: 0.9rem;
      transition: color 0.2s;
    }

    .nav-links a:hover,
    .nav-links a.active {
      color: var(--gold);
    }

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

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2.5rem 2rem;
    }

    .detail-layout {
      display: grid;
      grid-template-columns: 420px 1fr;
      gap: 2rem;
      align-items: start;
    }

    .detail-right {
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
    }

    .detail-card {
      background: var(--white);
      border-radius: 22px;
      border: 1px solid var(--warm);
      box-shadow: 0 10px 24px rgba(59,42,26,0.08);
      padding: 1.5rem;
    }

    .detail-img-wrap {
      width: 100%;
      border-radius: 18px;
      overflow: hidden;
      background: linear-gradient(135deg, #8b5e3c, #3b2a1a);
      min-height: 560px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .detail-img {
      width: 100%;
      height: 560px;
      object-fit: cover;
      display: block;
    }

    .fallback-book {
      font-size: 5rem;
      color: var(--gold);
    }

    .page-title {
      font-family: 'Playfair Display', serif;
      font-size: 2rem;
      margin-bottom: 0.8rem;
      line-height: 1.3;
    }

    .badge-row {
      display: flex;
      gap: 0.6rem;
      flex-wrap: wrap;
      margin-bottom: 1rem;
    }

    .badge {
      display: inline-block;
      background: var(--warm);
      color: var(--brown);
      padding: 0.45rem 0.8rem;
      border-radius: 999px;
      font-size: 0.82rem;
      font-weight: 700;
    }

    .muted {
      color: #5f5142;
      font-size: 0.95rem;
    }

    .price-line {
      font-size: 1.15rem;
      font-weight: 700;
      color: var(--brown);
      margin: 1rem 0 0.8rem;
    }

    .detail-text {
      line-height: 1.9;
      color: var(--mocha);
      margin-top: 1rem;
    }

    .help-box {
      margin-top: 1rem;
      background: #fbf6ee;
      border: 1px solid var(--warm);
      padding: 1rem;
      border-radius: 14px;
      line-height: 1.7;
    }

    .cta-row {
      display: flex;
      gap: 0.8rem;
      flex-wrap: wrap;
      margin-top: 1.5rem;
    }

    .btn {
      border: none;
      border-radius: 999px;
      padding: 0.85rem 1.2rem;
      cursor: pointer;
      font-family: 'Lato', sans-serif;
      font-size: 0.88rem;
      font-weight: 700;
      background: var(--brown);
      color: var(--gold);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 150px;
      transition: 0.2s;
    }

    .btn:hover {
      background: var(--mocha);
      color: white;
    }

    .btn.secondary {
      background: transparent;
      color: var(--brown);
      border: 1px solid var(--soft);
    }

    .btn.secondary:hover {
      background: var(--cream);
      border-color: var(--gold);
      color: var(--brown);
    }

    .author-wide-card {
      padding: 0;
      overflow: hidden;
    }

    .author-wide-header {
      background: linear-gradient(135deg, var(--brown), var(--mocha));
      color: var(--gold);
      padding: 14px 18px;
      font-weight: 700;
      font-size: 0.95rem;
      letter-spacing: 0.4px;
    }

    .author-wide-body {
      display: flex;
      align-items: center;
      gap: 18px;
      padding: 18px;
      background: linear-gradient(180deg, #fffdf8 0%, #fbf6ee 100%);
    }

    .author-wide-photo {
      width: 130px;
      height: 130px;
      min-width: 130px;
      border-radius: 18px;
      overflow: hidden;
      background: linear-gradient(135deg, #d9c2a0, #8b5e3c);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 18px rgba(0,0,0,0.08);
    }

    .author-wide-photo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .author-fallback {
      font-size: 2.3rem;
      font-weight: 700;
      color: var(--gold);
    }

    .author-wide-content {
      flex: 1;
      min-width: 0;
    }

    .author-role {
      font-size: 0.78rem;
      color: #8a7a69;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      margin-bottom: 6px;
    }

    .author-name {
      font-family: 'Playfair Display', serif;
      font-size: 1.45rem;
      color: var(--brown);
      line-height: 1.3;
      margin-bottom: 10px;
    }

    .author-meta {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-bottom: 12px;
    }

    .author-pill {
      background: var(--warm);
      color: var(--brown);
      border-radius: 999px;
      padding: 6px 12px;
      font-size: 0.78rem;
      font-weight: 700;
    }

    .author-desc {
      color: var(--mocha);
      font-size: 0.9rem;
      line-height: 1.8;
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

    @media (max-width: 900px) {
      .detail-layout {
        grid-template-columns: 1fr;
      }

      .detail-img {
        height: 420px;
      }

      .detail-img-wrap {
        min-height: 420px;
      }

      .author-wide-body {
        flex-direction: column;
        align-items: flex-start;
      }

      .author-wide-photo {
        width: 110px;
        height: 110px;
        min-width: 110px;
      }

      .author-name {
        font-size: 1.2rem;
      }

      .author-desc {
        font-size: 0.86rem;
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
    <li><a href="mylibrary.php">My Library</a></li>
    <li><a href="cart.php">🛒 Cart<?= $cartCount > 0 ? ' (' . (int)$cartCount . ')' : '' ?></a></li>
    <li><a href="admin_login.php">Are you admin?</a></li>
  </ul>

  <div class="nav-user">
    <div class="avatar"><?= h($userAvatar) ?></div>
    <span><?= h($guestName) ?></span>
  </div>
</nav>

<div class="container">
  <section class="detail-layout">

    <div class="detail-card">
      <div class="detail-img-wrap">
        <?php if ($image && file_exists($image)): ?>
          <img class="detail-img" src="<?= h($image) ?>" alt="<?= h($book['name']) ?>">
        <?php else: ?>
          <div class="fallback-book">📚</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="detail-right">

      <div class="detail-card">
        <h1 class="page-title"><?= h($book['name']) ?></h1>

        <div class="badge-row">
          <?php if (!empty($book['category'])): ?>
            <div class="badge"><?= h($book['category']) ?></div>
          <?php endif; ?>

          <?php if (!empty($book['language'])): ?>
            <div class="badge"><?= h($book['language']) ?></div>
          <?php endif; ?>
        </div>

        <p class="muted" style="margin-bottom:8px;">
          <strong>Author:</strong> <?= h($book['author_name'] ?: 'Unknown Author') ?>
        </p>

        <div class="price-line">
          Price: <?= number_format((float)$book['price'], 2) ?> SAR
        </div>

        <p class="muted" style="margin-bottom:8px;">
          <strong>Quantity available:</strong> <?= (int)($book['stock'] ?? 0) ?>
        </p>

        <div class="help-box">
          <strong>Help:</strong> Need support?  <a href="tel:+966500000000">Contact us: +966 50 000 0000</a>
        </div>

        <p class="detail-text">
          <?= h($book['description'] ?: 'No description available for this book.') ?>
        </p>

        <div class="cta-row">
          <form method="post" style="margin:0;">
            <input type="hidden" name="add_to_cart" value="1">
            <input type="hidden" name="product_id" value="<?= (int)$book['product_id'] ?>">
            <button type="submit" class="btn">Add to cart</button>
          </form>

         
        </div>
      </div>

      <div class="detail-card author-wide-card">
        <div class="author-wide-header">About the Author</div>

        <div class="author-wide-body">
          <div class="author-wide-photo">
            <?php if (!empty($authorImage) && file_exists($authorImage)): ?>
              <img src="<?= h($authorImage) ?>" alt="<?= h($book['author_name']) ?>">
            <?php else: ?>
              <div class="author-fallback">
                <?= h(strtoupper(substr((string)($book['author_name'] ?: 'A'), 0, 1))) ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="author-wide-content">
            <div class="author-role">Author</div>
            <div class="author-name"><?= h($book['author_name'] ?: 'Unknown Author') ?></div>


            <div class="author-desc">
              <?= h($authorBio) ?>
            </div>
          </div>
        </div>
      </div>

    </div>

  </section>
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

</body>
</html>