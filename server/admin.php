<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

require_admin();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$message = trim($_GET['message'] ?? '');
$q = trim($_GET['q'] ?? '');
$editId = (int)($_GET['edit'] ?? 0);
$editProduct = null;
$editError = '';


if ($q !== '') {
    $like = "%{$q}%";
    $stmt = $conn->prepare("
        SELECT * 
        FROM products 
        WHERE name LIKE ? 
           OR author_name LIKE ? 
           OR category LIKE ? 
           OR language LIKE ?
        ORDER BY product_id DESC
    ");

    if ($stmt) {
        $stmt->bind_param('ssss', $like, $like, $like, $like);
        $stmt->execute();
        $products = $stmt->get_result();
    } else {
        $products = $conn->query('SELECT * FROM products ORDER BY product_id DESC');
    }
} else {
    $products = $conn->query('SELECT * FROM products ORDER BY product_id DESC');
}

if ($editId > 0) {
    $stmt = $conn->prepare('SELECT * FROM products WHERE product_id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $editProduct = $stmt->get_result()->fetch_assoc();
        if (!$editProduct) {
            $editError = 'The product you are trying to edit does not exist.';
        }
        $stmt->close();
    } else {
        $editError = 'An error occurred while loading the product data.';
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>InkTale - Admin Dashboard</title>
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
  --red: #b94a48;
  --green: #2f7d32;
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

.hero {
  background: linear-gradient(135deg, var(--brown) 0%, var(--mocha) 100%);
  padding: 2.4rem 2rem 2rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23c9a84c' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.hero-logo {
  position: relative;
  display: flex;
  justify-content: center;
  margin-bottom: 0.5rem;
}

.hero-logo img {
  height: 110px;
  width: auto;
  object-fit: contain;
  border-radius: 50%;
  filter: drop-shadow(0 0 24px rgba(201,168,76,0.7)) brightness(1.1);
}

.hero h1 {
  position: relative;
  font-family: 'Playfair Display', serif;
  color: var(--white);
  font-size: 2rem;
  margin-bottom: 0.3rem;
}

.hero p {
  position: relative;
  color: var(--soft);
  font-size: 0.95rem;
}

.hero p span {
  color: var(--gold);
  font-weight: 700;
}

.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 2rem;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 18px;
}

.topbar-left h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.7rem;
  margin-bottom: 4px;
}

.muted {
  color: #6a5b4d;
}

.btn, .btn-link {
  border: none;
  border-radius: 999px;
  padding: 0.8rem 1.2rem;
  cursor: pointer;
  font-family: 'Lato', sans-serif;
  font-size: 0.9rem;
  font-weight: 700;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: 0.2s;
}

.btn {
  background: var(--brown);
  color: var(--gold);
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
}

.btn.danger {
  background: #fff1f1;
  color: var(--red);
  border: 1px solid #ebc5c5;
}

.btn.danger:hover {
  background: #ffe5e5;
}

.notice {
  padding: 14px 16px;
  border-radius: 12px;
  margin-bottom: 16px;
}

.notice.ok {
  background: #edf8ed;
  color: var(--green);
  border: 1px solid #b7ddb7;
}

.notice.err {
  background: #fdeeee;
  color: var(--red);
  border: 1px solid #e3bcbc;
}

.search-bar {
  margin-bottom: 18px;
}

.search-bar input {
  width: 100%;
  padding: 14px 16px;
  border-radius: 16px;
  border: 1px solid var(--soft);
  background: #fcf8f1;
  font-family: 'Lato', sans-serif;
  font-size: 0.95rem;
  outline: none;
}

.search-bar input:focus {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(201,168,76,0.12);
}

.panel {
  background: var(--white);
  border: 1px solid var(--warm);
  border-radius: 22px;
  box-shadow: 0 10px 24px rgba(59,42,26,0.08);
  padding: 1.5rem;
  margin-bottom: 22px;
}

.panel-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.4rem;
  margin-bottom: 1rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.form-grid input,
.form-grid select,
.form-grid textarea {
  width: 100%;
  padding: 14px 16px;
  border-radius: 16px;
  border: 1px solid var(--soft);
  background: #fcf8f1;
  font-family: 'Lato', sans-serif;
  font-size: 0.95rem;
  color: var(--brown);
  outline: none;
}

.form-grid input:focus,
.form-grid select:focus,
.form-grid textarea:focus {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(201,168,76,0.12);
}

.form-grid textarea {
  min-height: 120px;
  resize: vertical;
}

.full {
  grid-column: 1 / -1;
}

.current-image-box {
  display: flex;
  align-items: center;
  gap: 14px;
  background: #fbf6ee;
  border: 1px solid var(--warm);
  padding: 12px;
  border-radius: 16px;
}

.current-image-box img {
  width: 90px;
  height: 120px;
  object-fit: cover;
  border-radius: 10px;
}

.table-wrap {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 980px;
}

.data-table th,
.data-table td {
  text-align: left;
  padding: 14px 12px;
  border-bottom: 1px solid var(--warm);
  vertical-align: middle;
}

.data-table th {
  background: #faf3e8;
  color: var(--brown);
  font-weight: 700;
  position: sticky;
  top: 0;
}

.table-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.thumb {
  width: 60px;
  height: 80px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #ddd;
}

.empty-row {
  text-align: center;
  color: #776758;
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
  .form-grid {
    grid-template-columns: 1fr;
  }

  .topbar {
    flex-direction: column;
    align-items: flex-start;
  }

  .container {
    padding: 1.2rem 1rem;
  }

  nav {
    padding: 0 1rem;
  }
}
</style>
</head>
<body>

<nav>
  <div class="nav-logo">
    <img src="inktale_logo.png" alt="InkTale">
  </div>

  

  <div class="nav-user">
    <div class="avatar">A</div>
    <span><?= h($_SESSION['admin_name'] ?? 'Admin') ?></span>
  </div>
</nav>

<section class="hero">
  <div class="hero-logo">
    <img src="inktale_logo.png" alt="InkTale">
  </div>
  <h1>Admin Dashboard</h1>
  <p>Manage <span>products, stock, prices, and content</span> from here</p>
</section>

<div class="container">

  <div class="topbar">
    <div class="topbar-left">
      <h2>Welcome, <?= h($_SESSION['admin_name'] ?? 'Admin'); ?></h2>
      <p class="muted">Search, add, modify, and remove products.</p>
    </div>
    <a class="btn danger" href="admin_logout.php">Logout</a>
  </div>

  <?php if ($message): ?>
    <div class="notice ok"><?= h($message); ?></div>
  <?php endif; ?>

  <?php if ($editError): ?>
    <div class="notice err"><?= h($editError); ?></div>
  <?php endif; ?>

  <form class="search-bar" method="get">
    <input type="text" name="q" placeholder="Search by name, author, category, language" value="<?= h($q); ?>">
  </form>

  <div class="panel" id="product-form">
    <h2 class="panel-title"><?= $editProduct ? 'Modify product' : 'Add product'; ?></h2>

    <form class="form-grid" method="post" action="admin_save.php" enctype="multipart/form-data">
      <input type="hidden" name="product_id" value="<?= (int)($editProduct['product_id'] ?? 0); ?>">

      <input name="name" value="<?= h($editProduct['name'] ?? ''); ?>" placeholder="Book name" required>
      <input name="author_name" value="<?= h($editProduct['author_name'] ?? ''); ?>" placeholder="Author name" required>

      <input name="price" type="number" step="0.01" min="0" value="<?= h($editProduct['price'] ?? ''); ?>" placeholder="Price" required>
      <input name="stock" type="number" min="0" value="<?= h($editProduct['stock'] ?? ''); ?>" placeholder="Stock" required>

      <input name="category" value="<?= h($editProduct['category'] ?? ''); ?>" placeholder="Category" required>

      <select name="language" required>
        <option value="">Select language</option>
        <option value="English" <?= ($editProduct['language'] ?? '') === 'English' ? 'selected' : ''; ?>>English</option>
        <option value="Arabic" <?= ($editProduct['language'] ?? '') === 'Arabic' ? 'selected' : ''; ?>>Arabic</option>
      </select>

      <textarea class="full" name="description" placeholder="Description" required><?= h($editProduct['description'] ?? ''); ?></textarea>
      <textarea class="full" name="author_bio" placeholder="Author bio"><?= h($editProduct['author_bio'] ?? ''); ?></textarea>

      <div class="full">
        <label style="display:block;margin-bottom:8px;color:#6a5b4d;">Upload image <?= $editProduct ? '(optional when modifying)' : ''; ?></label>
        <input type="file" name="picture" accept="image/*">
      </div>

      <?php if (!empty($editProduct['picture'])): ?>
        <div class="full current-image-box">
          <img src="<?= h(imagePath($editProduct['picture'])); ?>" alt="">
          <div>
            <p style="font-weight:700;margin-bottom:4px;">Current image</p>
            <p class="muted"><?= h($editProduct['picture']); ?></p>
          </div>
        </div>
      <?php endif; ?>

      <div class="full" style="display:flex;gap:10px;flex-wrap:wrap;">
        <button class="btn" type="submit"><?= $editProduct ? 'Save changes' : 'Add product'; ?></button>
        <?php if ($editProduct): ?>
          <a class="btn secondary" href="admin.php">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <div class="panel">
    <h2 class="panel-title">Products</h2>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Author</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Category</th>
            <th>Language</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($products && $products->num_rows > 0): ?>
          <?php while ($row = $products->fetch_assoc()): ?>
            <tr>
              <td><?= (int)$row['product_id']; ?></td>
              <td>
                <img class="thumb" src="<?= h(imagePath($row['picture'] ?? '')); ?>" alt="">
              </td>
              <td><?= h($row['name']); ?></td>
              <td><?= h($row['author_name']); ?></td>
              <td><?= number_format((float)$row['price'], 2); ?> SAR</td>
              <td><?= (int)$row['stock']; ?></td>
              <td><?= h($row['category']); ?></td>
              <td><?= h($row['language']); ?></td>
              <td>
                <div class="table-actions">
                  <a class="btn secondary" href="admin.php?edit=<?= (int)$row['product_id']; ?>#product-form">Modify</a>

                  <form method="post" action="admin_delete.php" onsubmit="return confirm('Delete this product?');" style="display:inline;">
                    <input type="hidden" name="id" value="<?= (int)$row['product_id']; ?>">
                    <button class="btn danger" type="submit">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" class="empty-row">No products found.</td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<footer>
  <img src="inktale_logo.png" alt="InkTale" class="footer-logo">
  <p>InkTale — Admin Panel © <?= date('Y') ?></p>
  <div class="footer-links">
    <a href="admin.php">Dashboard</a>
    
  </div>
</footer>

</body>
</html>