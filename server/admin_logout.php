<?php
require_once __DIR__ . '/functions.php';

// Clear only the admin keys — the visitor's cart survives the logout.
unset($_SESSION['admin_logged_in'], $_SESSION['admin_id'], $_SESSION['admin_name']);
session_regenerate_id(true);

header('Location: admin_login.php');
exit;
