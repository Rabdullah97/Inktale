<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}

$productId   = (int)($_POST['product_id'] ?? 0);
$name        = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$price       = (float)($_POST['price'] ?? 0);
$stock       = (int)($_POST['stock'] ?? 0);
$category    = trim($_POST['category'] ?? '');
$language    = trim($_POST['language'] ?? 'English');
$authorName  = trim($_POST['author_name'] ?? '');
$authorBio   = trim($_POST['author_bio'] ?? '');

/* ---------- validation ---------- */
$errors = [];
if ($name === '')                       $errors[] = 'Book name is required.';
if (mb_strlen($name) > 200)             $errors[] = 'Book name is too long.';
if ($price < 0)                         $errors[] = 'Price cannot be negative.';
if ($stock < 0)                         $errors[] = 'Stock cannot be negative.';
if (!in_array($language, ['Arabic', 'English'], true)) {
    $language = 'English';
}

if ($errors) {
    header('Location: admin.php?message=' . urlencode(implode(' ', $errors)));
    exit;
}

/* ---------- keep the existing picture unless a new one is uploaded ---------- */
$picturePath = '';
if ($productId > 0) {
    $stmt = $conn->prepare('SELECT picture FROM products WHERE product_id = ?');
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $picturePath = $stmt->get_result()->fetch_assoc()['picture'] ?? '';
    $stmt->close();
}

if (!empty($_FILES['picture']['name'])) {
    $uploadError = null;
    $stored = store_uploaded_image($_FILES['picture'], $uploadError);
    if ($stored === null) {
        header('Location: admin.php?message=' . urlencode($uploadError));
        exit;
    }
    $picturePath = $stored;
}

if ($picturePath === '') {
    $picturePath = 'images/default-book.jpg';
}

/* ---------- write ---------- */
if ($productId > 0) {
    $stmt = $conn->prepare(
        'UPDATE products
            SET name = ?, description = ?, picture = ?, price = ?, stock = ?,
                category = ?, language = ?, author_name = ?, author_bio = ?
          WHERE product_id = ?'
    );
    $stmt->bind_param(
        'sssdissssi',
        $name, $description, $picturePath, $price, $stock,
        $category, $language, $authorName, $authorBio, $productId
    );
    $stmt->execute();
    $stmt->close();

    header('Location: admin.php?message=' . urlencode('Product updated successfully'));
    exit;
}

$stmt = $conn->prepare(
    'INSERT INTO products
        (name, description, picture, price, stock, category, language, author_name, author_bio)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param(
    'sssdissss',
    $name, $description, $picturePath, $price, $stock,
    $category, $language, $authorName, $authorBio
);
$stmt->execute();
$stmt->close();

header('Location: admin.php?message=' . urlencode('Product added successfully'));
exit;
