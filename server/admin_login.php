<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$error = '';

if (is_admin_logged_in()) {
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    // Look the admin up by username only, then verify the hash in PHP.
    // Never compare passwords inside the SQL query.
    $stmt = $conn->prepare(
        'SELECT admin_id, username, password, full_name FROM admins WHERE username = ? LIMIT 1'
    );
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($admin && password_verify($password, $admin['password'])) {
        // New session id on privilege change, to block session fixation.
        session_regenerate_id(true);

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id']        = $admin['admin_id'];
        $_SESSION['admin_name']      = $admin['full_name'] ?: $admin['username'];

        header('Location: admin.php');
        exit;
    }

    // Same message for a bad username and a bad password, so the form
    // cannot be used to discover which usernames exist.
    $error = 'Invalid admin username or password.';
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>InkTale - Admin Login</title>
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

.hero {
  background: linear-gradient(135deg, var(--brown) 0%, var(--mocha) 100%);
  padding: 2.6rem 2rem 2.2rem;
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
  margin-bottom: 0.6rem;
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
  margin-bottom: 0.35rem;
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
  max-width: 1200px;
  margin: 0 auto;
  padding: 2.2rem 2rem;
}

.login-wrap {
  max-width: 540px;
  margin: 0 auto;
}

.login-card {
  background: var(--white);
  border-radius: 24px;
  border: 1px solid var(--warm);
  box-shadow: 0 12px 28px rgba(59,42,26,0.08);
  padding: 2rem;
}

.login-card h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.8rem;
  margin-bottom: 0.5rem;
  text-align: center;
}

.login-card .sub {
  text-align: center;
  color: #6a5a4a;
  margin-bottom: 1.5rem;
}

.notice-err {
  background: #fdeeee;
  color: var(--red);
  border: 1px solid #e3bcbc;
  padding: 12px 14px;
  border-radius: 12px;
  margin-bottom: 14px;
}

.form-grid {
  display: grid;
  gap: 14px;
}

.field label {
  display: block;
  margin-bottom: 6px;
  font-size: 0.9rem;
  color: #5f5142;
  font-weight: 600;
}

.field input {
  width: 100%;
  padding: 14px 16px;
  border-radius: 16px;
  border: 1px solid var(--soft);
  background: #fcf8f1;
  font-family: 'Lato', sans-serif;
  font-size: 0.95rem;
  color: var(--brown);
  outline: none;
  transition: 0.2s;
}

.field input:focus {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(201,168,76,0.12);
}

.btn {
  width: 100%;
  border: none;
  border-radius: 999px;
  padding: 14px 18px;
  cursor: pointer;
  font-family: 'Lato', sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  background: var(--brown);
  color: var(--gold);
  transition: 0.2s;
  margin-top: 6px;
}

.btn:hover {
  background: var(--mocha);
  color: white;
}

.helper-box {
  margin-top: 1rem;
  background: #fbf6ee;
  border: 1px solid var(--warm);
  border-radius: 16px;
  padding: 1rem;
  line-height: 1.7;
  font-size: 0.9rem;
  color: #5e4f40;
}

.helper-box a {
  color: var(--brown);
  font-weight: 700;
  text-decoration: none;
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

@media (max-width: 700px) {
  .container {
    padding: 1.4rem 1rem;
  }

  nav {
    padding: 0 1rem;
  }

  .nav-links {
    gap: 0.9rem;
    font-size: 0.82rem;
  }

  .hero h1 {
    font-size: 1.7rem;
  }

  .login-card {
    padding: 1.4rem;
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
    <li><a href="cart.php"> 🛒 Cart</a></li>
    <li><a href="admin_login.php" class="active">Are you admin?</a></li>
  </ul>

  <div class="nav-user">
    <div class="avatar">A</div>
    <span>Admin</span>
  </div>
</nav>

<section class="hero">
  <div class="hero-logo">
    <img src="inktale_logo.png" alt="InkTale">
  </div>
  <h1>Admin Access</h1>
  <p>Manage the <span>catalog and dashboard</span> from one place</p>
</section>

<div class="container">
  <div class="login-wrap">
    <div class="login-card">
      <h2>Admin Login</h2>
      <p class="sub">Sign in using your admin account</p>

      <?php if ($error): ?>
        <div class="notice-err"><?= h($error); ?></div>
      <?php endif; ?>

      <form method="post" class="form-grid">
        <div class="field">
          <label for="username">Username</label>
          <input id="username" type="text" name="username" placeholder="Enter admin username" required>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <input id="password" type="password" name="password" placeholder="Enter password" required>
        </div>

        <button class="btn" type="submit">Log in</button>
      </form>

      <div class="helper-box">
        This page uses the admins table from your database.<br>
        Need help? <a href="tel:+966500000000">Contact support</a>
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