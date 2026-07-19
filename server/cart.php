<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$guestName  = guest_name();
$userAvatar = guest_avatar();
function money($value) {
    return number_format((float)$value, 2) . ' SAR';
}

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

function addPurchasedBooks($cartItems) {
    $purchases = getPurchasedBooksFromCookie();

    foreach ($cartItems as $item) {
        $pid = (int)$item['product_id'];
        $found = false;

        foreach ($purchases as &$p) {
            if ((int)$p['product_id'] === $pid) {
                $p['qty'] += (int)$item['qty'];
                $p['status'] = $p['status'] ?? 'reading';
                $found = true;
                break;
            }
        }
        unset($p);

        if (!$found) {
            $purchases[] = [
                'product_id' => (int)$item['product_id'],
                'name' => $item['name'],
                'author_name' => $item['author_name'],
                'picture' => $item['picture'],
                'qty' => (int)$item['qty'],
                'price' => (float)$item['price'],
                'status' => 'reading',
                'returned' => 0
            ];
        }
    }

    savePurchasedBooksToCookie($purchases);
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);

    if ($action === 'increase' && $productId > 0) {
        if (isset($_SESSION['cart'][$productId])) {
            $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $stock = (int)($product['stock'] ?? 0);
            $currentQty = (int)$_SESSION['cart'][$productId];

            if ($currentQty < $stock) {
                $_SESSION['cart'][$productId]++;
            } else {
                $errorMessage = 'You cannot increase the quantity beyond available stock.';
            }
        }

        if ($errorMessage === '') {
            header('Location: cart.php');
            exit;
        }
    }

    if ($action === 'decrease' && $productId > 0) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]--;
            if ($_SESSION['cart'][$productId] <= 0) {
                unset($_SESSION['cart'][$productId]);
            }
        }
        header('Location: cart.php');
        exit;
    }

    if ($action === 'delete' && $productId > 0) {
        unset($_SESSION['cart'][$productId]);
        header('Location: cart.php');
        exit;
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        header('Location: cart.php');
        exit;
    }

    if ($action === 'buy') {
        if (empty($_SESSION['cart'])) {
            $errorMessage = 'Your cart is empty.';
        } else {
            $cartItemsForPurchase = [];
            $ids = array_keys($_SESSION['cart']);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids));

            $sql = "SELECT product_id, name, author_name, price, picture, stock FROM products WHERE product_id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$ids);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $pid = (int)$row['product_id'];
                $qty = (int)($_SESSION['cart'][$pid] ?? 1);
                $row['qty'] = $qty;
                $cartItemsForPurchase[] = $row;
            }
            $stmt->close();

            $conn->begin_transaction();

            try {
                foreach ($cartItemsForPurchase as $item) {
                    $pid = (int)$item['product_id'];
                    $qty = (int)$item['qty'];

                    $stmt = $conn->prepare("SELECT product_id, name, stock FROM products WHERE product_id = ? FOR UPDATE");
                    $stmt->bind_param('i', $pid);
                    $stmt->execute();
                    $product = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if (!$product) {
                        throw new Exception('One of the products no longer exists.');
                    }

                    $stock = (int)($product['stock'] ?? 0);
                    if ($stock < $qty) {
                        throw new Exception('Not enough stock for: ' . $product['name']);
                    }
                }

                foreach ($cartItemsForPurchase as $item) {
                    $pid = (int)$item['product_id'];
                    $qty = (int)$item['qty'];

                    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
                    $stmt->bind_param('ii', $qty, $pid);
                    $stmt->execute();
                    $stmt->close();
                }

                $conn->commit();
                addPurchasedBooks($cartItemsForPurchase);
                $_SESSION['cart'] = [];
                $successMessage = 'Your purchase was successful! You can find your books in the store. Thank you for shopping with us.';
            } catch (Exception $e) {
                $conn->rollback();
                $errorMessage = $e->getMessage();
            }
        }
    }
}

$cartItems = [];
$subtotal = 0.0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $sql = "SELECT product_id, name, author_name, price, picture, stock FROM products WHERE product_id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $pid = (int)$row['product_id'];
        $qty = (int)($_SESSION['cart'][$pid] ?? 1);

        if ($qty > (int)$row['stock']) {
            $qty = (int)$row['stock'];
            $_SESSION['cart'][$pid] = max($qty, 1);
        }

        $row['qty'] = $qty;
        $row['line_total'] = ((float)$row['price']) * $qty;
        $cartItems[] = $row;
        $subtotal += $row['line_total'];
    }
    $stmt->close();
}

$tax = $subtotal * 0.15;
$shipping = $subtotal > 0 ? 15 : 0;
$total = $subtotal + $tax + $shipping;
$cartCount = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>InkTale - Cart & Checkout</title>
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

.page-header .hero-top {
  text-align: center;
  position: relative;
  z-index: 1;
  margin-bottom: 0;
}

.page-header .hero-top img {
  height: 110px;
  width: auto;
  object-fit: contain;
  display: block;
  margin: 0 auto 0.6rem;
  border-radius: 50%;
  animation: glow 3s ease-in-out infinite;
}

.page-header .hero-top h1 {
  font-family: 'Playfair Display', serif;
  color: var(--white);
  font-size: 2rem;
  margin-bottom: 0.4rem;
}

.page-header .hero-top p {
  color: var(--soft);
  font-size: 0.95rem;
}

.page-header .hero-top span {
  color: var(--gold);
  font-weight: 700;
}

@keyframes glow {
  0%, 100% {
    filter: drop-shadow(0 0 18px rgba(201,168,76,0.45)) brightness(1.05);
  }
  50% {
    filter: drop-shadow(0 0 34px rgba(201,168,76,0.95)) brightness(1.18);
  }
}

.container {
  max-width: 1250px;
  margin: 0 auto;
  padding: 2rem;
}

.alert {
  padding: 14px 16px;
  border-radius: 12px;
  margin-bottom: 18px;
  font-size: 0.95rem;
}

.alert.success {
  background: #edf8ed;
  color: var(--green);
  border: 1px solid #b7ddb7;
}

.alert.error {
  background: #fdeeee;
  color: var(--red);
  border: 1px solid #e3bcbc;
}

.cart-layout {
  display: grid;
  grid-template-columns: 1.7fr 0.95fr;
  gap: 24px;
  align-items: start;
}

.card {
  background: var(--white);
  border-radius: 20px;
  border: 1px solid var(--warm);
  box-shadow: 0 8px 20px rgba(59,42,26,0.06);
}

.card-header {
  padding: 18px 20px;
  border-bottom: 1px solid var(--warm);
  font-family: 'Playfair Display', serif;
  font-size: 1.3rem;
}

.cart-items {
  padding: 8px 0;
}

.cart-item {
  display: grid;
  grid-template-columns: 92px 1fr auto;
  gap: 16px;
  padding: 18px 20px;
  border-bottom: 1px solid var(--warm);
  align-items: center;
}

.cart-item:last-child {
  border-bottom: none;
}

.item-image {
  width: 92px;
  height: 125px;
  border-radius: 12px;
  overflow: hidden;
  background: linear-gradient(135deg, #8b5e3c, #3b2a1a);
  display: flex;
  align-items: center;
  justify-content: center;
}

.item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.item-image span {
  color: var(--gold);
  font-size: 2rem;
}

.item-info h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem;
  margin-bottom: 4px;
}

.item-info .author {
  color: #6d5b49;
  font-size: 0.88rem;
  margin-bottom: 8px;
}

.item-info .stock {
  font-size: 0.82rem;
  color: #7a6a58;
  margin-bottom: 10px;
}

.item-price {
  font-weight: 700;
  color: var(--brown);
  margin-bottom: 10px;
}

.qty-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.qty-form {
  margin: 0;
}

.qty-btn,
.icon-btn,
.action-btn {
  border: none;
  cursor: pointer;
  font-family: 'Lato', sans-serif;
  transition: 0.2s;
}

.qty-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: var(--warm);
  color: var(--brown);
  font-size: 1rem;
  font-weight: 700;
}

.qty-btn:hover {
  background: var(--gold);
}

.qty-value {
  min-width: 28px;
  text-align: center;
  font-weight: 700;
}

.item-side {
  text-align: right;
}

.item-total {
  font-weight: 700;
  margin-bottom: 10px;
}

.icon-btn {
  background: transparent;
  color: var(--red);
  border: 1px solid #ebd3d3;
  border-radius: 999px;
  padding: 8px 12px;
}

.icon-btn:hover {
  background: #fff1f1;
}

.summary-box {
  padding: 20px;
  position: sticky;
  top: 88px;
}

.summary-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem;
  margin-bottom: 18px;
}

.summary-line {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  color: #5e4f40;
}

.summary-total {
  border-top: 1px solid var(--warm);
  margin-top: 14px;
  padding-top: 14px;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--brown);
}

.action-btn {
  width: 100%;
  padding: 14px;
  border-radius: 999px;
  font-weight: 700;
  font-size: 0.95rem;
  margin-top: 12px;
}

.action-btn.primary {
  background: var(--brown);
  color: var(--gold);
}

.action-btn.primary:hover {
  background: var(--mocha);
  color: white;
}

.action-btn.secondary {
  background: transparent;
  color: var(--brown);
  border: 1px solid var(--soft);
}

.action-btn.secondary:hover {
  background: var(--cream);
}

.empty {
  padding: 40px 20px;
  text-align: center;
  color: #6f5d4d;
}

.empty h3 {
  font-family: 'Playfair Display', serif;
  margin-bottom: 8px;
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

@media (max-width: 980px) {
  .cart-layout {
    grid-template-columns: 1fr;
  }

  .summary-box {
    position: static;
  }
}

@media (max-width: 700px) {
  .cart-item {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .item-image {
    margin: 0 auto;
  }

  .item-side {
    text-align: center;
  }

  .qty-row {
    justify-content: center;
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
    <li><a href="cart.php" class="active">🛒 Cart<?= $cartCount > 0 ? ' (' . (int)$cartCount . ')' : '' ?></a></li>
    <li><a href="admin_login.php">Are you admin?</a></li>
  </ul>

  <div class="nav-user">
    <div class="avatar"><?= h($userAvatar) ?></div>
    <span><?= h($guestName) ?></span>
  </div>
</nav>

<div class="page-header">
  <div class="hero-top">
    <img src="inktale_logo.png" alt="InkTale">
    <h1>Checkout</h1>
    <p>Review your <span>books</span> and complete your order</p>
  </div>
</div>

<div class="container">

  <?php if ($successMessage): ?>
    <div class="alert success"><?= h($successMessage) ?></div>
  <?php endif; ?>

  <?php if ($errorMessage): ?>
    <div class="alert error"><?= h($errorMessage) ?></div>
  <?php endif; ?>

  <div class="cart-layout">

    <div class="card">
      <div class="card-header">Your Cart</div>

      <?php if (empty($cartItems)): ?>
        <div class="empty">
          <h3>Your cart is empty</h3>
          <p style="margin-bottom:16px;">Browse books and add something you love.</p>
          <a href="browse.php" class="action-btn primary" style="display:inline-flex; width:auto; padding:12px 22px; text-decoration:none;">Go to Browse</a>
        </div>
      <?php else: ?>
        <div class="cart-items">
          <?php foreach ($cartItems as $item): ?>
            <?php $img = imagePath($item['picture'] ?? ''); ?>
            <div class="cart-item">
              <div class="item-image">
                <?php if ($img && file_exists($img)): ?>
                  <img src="<?= h($img) ?>" alt="<?= h($item['name']) ?>">
                <?php else: ?>
                  <span>📚</span>
                <?php endif; ?>
              </div>

              <div class="item-info">
                <h3><?= h($item['name']) ?></h3>
                <div class="author"><?= h($item['author_name'] ?: 'Unknown Author') ?></div>
                <div class="stock">Available stock: <?= (int)($item['stock'] ?? 0) ?></div>
                <div class="item-price"><?= money($item['price']) ?></div>

                <div class="qty-row">
                  <form method="post" class="qty-form">
                    <input type="hidden" name="action" value="decrease">
                    <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                    <button type="submit" class="qty-btn">−</button>
                  </form>

                  <div class="qty-value"><?= (int)$item['qty'] ?></div>

                  <form method="post" class="qty-form">
                    <input type="hidden" name="action" value="increase">
                    <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                    <button type="submit" class="qty-btn">+</button>
                  </form>
                </div>
              </div>

              <div class="item-side">
                <div class="item-total"><?= money($item['line_total']) ?></div>

                <form method="post">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                  <button type="submit" class="icon-btn">Delete</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="card">
      <div class="summary-box">
        <div class="summary-title">Order Summary</div>

        <div class="summary-line">
          <span>Subtotal</span>
          <span><?= money($subtotal) ?></span>
        </div>

        <div class="summary-line">
          <span>Tax (15%)</span>
          <span><?= money($tax) ?></span>
        </div>

        <div class="summary-line">
          <span>Shipping</span>
          <span><?= money($shipping) ?></span>
        </div>

        <div class="summary-line summary-total">
          <span>Total</span>
          <span><?= money($total) ?></span>
        </div>

        <?php if (!empty($cartItems)): ?>
          <form method="post">
            <input type="hidden" name="action" value="buy">
            <button type="submit" class="action-btn primary">Confirm Purchase</button>
          </form>

          <form method="post">
            <input type="hidden" name="action" value="clear">
            <button type="submit" class="action-btn secondary">Delete All</button>
          </form>

          <a href="browse.php" class="action-btn secondary" style="display:inline-flex; justify-content:center; text-decoration:none;">Continue Shopping</a>
        <?php else: ?>
          <a href="browse.php" class="action-btn primary" style="display:inline-flex; justify-content:center; text-decoration:none;">Browse Books</a>
        <?php endif; ?>
      </div>
    </div>

  </div>
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