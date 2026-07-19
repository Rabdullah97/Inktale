<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare('DELETE FROM products WHERE product_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}

header('Location: admin.php?message=' . urlencode('Product deleted successfully'));
exit;
