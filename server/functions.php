<?php
/**
 * InkTale — shared bootstrap.
 *
 * Every page includes this file. It starts the session, exposes the guest
 * identity cookie, and provides the escaping, image-path and cart helpers
 * that used to be copy-pasted into each page.
 */

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ------------------------------------------------------------------ *
 * Output escaping
 * ------------------------------------------------------------------ */

/** Escape a value for safe output inside HTML. */
function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* ------------------------------------------------------------------ *
 * Guest identity
 * ------------------------------------------------------------------ */

/** Give every visitor a stable anonymous id (no user accounts in this build). */
function ensure_guest_cookie(): void
{
    if (empty($_COOKIE['inktale_guest'])) {
        try {
            $guestId = 'guest_' . bin2hex(random_bytes(6));
        } catch (Exception $e) {
            $guestId = 'guest_' . uniqid();
        }
        setcookie('inktale_guest', $guestId, [
            'expires'  => time() + (86400 * 30),
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE['inktale_guest'] = $guestId;
    }
}

/** Display name shown in the navbar. */
function guest_name(): string
{
    return $_COOKIE['guest_user'] ?? 'Guest';
}

/** Single-letter avatar for the navbar. */
function guest_avatar(): string
{
    return strtoupper(mb_substr(guest_name(), 0, 1, 'UTF-8'));
}

/* ------------------------------------------------------------------ *
 * Images
 * ------------------------------------------------------------------ */

/**
 * Resolve a picture path coming from the database to a real file.
 * Falls back to the placeholder when nothing matches.
 */
function imagePath(?string $file, string $fallback = '../images/default-book.jpg'): string
{
    $file = trim((string)$file);
    if ($file === '') {
        return $fallback;
    }

    // Never let a database value climb out of the project directory.
    if (str_contains($file, '..')) {
        return $fallback;
    }

    // Images live at the repository root and are shared with the static
    // version of the site, so they sit one level above this folder.
    $root = dirname(__DIR__);

    $candidates = [
        $file,
        'bookIMG/' . basename($file),
        'images/authors/' . basename($file),
        'images/' . basename($file),
    ];

    foreach ($candidates as $candidate) {
        if (is_file($root . '/' . $candidate)) {
            return '../' . $candidate;
        }
    }

    return $fallback;
}

/* ------------------------------------------------------------------ *
 * Cart  (session-backed — one source of truth for every page)
 * ------------------------------------------------------------------ */

/** Current cart as [product_id => qty], always sanitised. */
function get_cart(): array
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $clean = [];
    foreach ($_SESSION['cart'] as $productId => $qty) {
        $productId = (int)$productId;
        $qty       = (int)$qty;
        if ($productId > 0 && $qty > 0) {
            $clean[$productId] = $qty;
        }
    }

    $_SESSION['cart'] = $clean;
    return $clean;
}

function save_cart(array $cart): void
{
    $clean = [];
    foreach ($cart as $productId => $qty) {
        $productId = (int)$productId;
        $qty       = (int)$qty;
        if ($productId > 0 && $qty > 0) {
            $clean[$productId] = $qty;
        }
    }
    $_SESSION['cart'] = $clean;
}

function add_to_cart(int $productId, int $qty = 1): void
{
    if ($productId <= 0) {
        return;
    }
    $cart = get_cart();
    $cart[$productId] = ($cart[$productId] ?? 0) + max(1, $qty);
    save_cart($cart);
}

function update_cart_item(int $productId, int $qty): void
{
    $cart = get_cart();
    if ($qty <= 0) {
        unset($cart[$productId]);
    } else {
        $cart[$productId] = $qty;
    }
    save_cart($cart);
}

function clear_cart(): void
{
    save_cart([]);
}

/** Total number of items in the cart (used by the navbar badge). */
function cart_count(): int
{
    return array_sum(get_cart());
}

/* ------------------------------------------------------------------ *
 * Admin authentication
 * ------------------------------------------------------------------ */

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

/** Redirect to the login page unless an admin session is active. */
function require_admin(): void
{
    if (!is_admin_logged_in()) {
        header('Location: admin_login.php');
        exit;
    }
}

/* ------------------------------------------------------------------ *
 * Uploads
 * ------------------------------------------------------------------ */

/**
 * Validate an uploaded image and move it into bookIMG/.
 * Returns the relative path on success, or null on rejection.
 */
function store_uploaded_image(array $file, ?string &$error = null): ?string
{
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    $maxBytes = 3 * 1024 * 1024; // 3 MB

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = 'Upload failed.';
        return null;
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        $error = 'Invalid upload.';
        return null;
    }
    if ($file['size'] > $maxBytes) {
        $error = 'Image must be 3 MB or smaller.';
        return null;
    }

    // Trust the file's actual contents, never the client-supplied name or type.
    $info = @getimagesize($file['tmp_name']);
    $mime = $info['mime'] ?? '';
    if (!isset($allowed[$mime])) {
        $error = 'Only JPG, PNG, WEBP or GIF images are allowed.';
        return null;
    }

    $extension = $allowed[$mime];
    $stem      = pathinfo($file['name'], PATHINFO_FILENAME);
    $stem      = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '_', $stem));
    $stem      = trim(preg_replace('/_+/', '_', $stem), '_') ?: 'book';

    $uploadDir = dirname(__DIR__) . '/bookIMG';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        $error = 'Could not create the upload directory.';
        return null;
    }

    $name = $stem . '.' . $extension;
    $n    = 2;
    while (file_exists($uploadDir . '/' . $name)) {
        $name = $stem . '_' . $n . '.' . $extension;
        $n++;
    }

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $name)) {
        $error = 'Could not save the uploaded image.';
        return null;
    }

    return 'bookIMG/' . $name;
}

ensure_guest_cookie();
