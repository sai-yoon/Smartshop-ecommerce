<?php
require 'auth.php';
require 'db.php';

if ($_SESSION['user_type'] !== 'seller') {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
}

header("Location: my_products.php");
exit;
