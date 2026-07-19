<?php
require_once __DIR__ . '/functions.php';

$id  = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$qty = (int)($_POST['qty'] ?? $_GET['qty'] ?? 1);

if ($id > 0) {
    update_cart_item($id, $qty);
}

header('Location: cart.php');
exit;
