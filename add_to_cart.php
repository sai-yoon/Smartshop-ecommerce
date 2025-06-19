<?php
require 'auth.php';
require 'db.php';

if ($_SESSION['user_type'] !== 'customer') {
    header("Location: index.php");
    exit;
}

$product_id = $_POST['product_id'] ?? null;
$quantity = $_POST['quantity'] ?? 1;

if (!$product_id || $quantity < 1) {
    die("Invalid input.");
}

// Check if product is already in the cart
$stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->execute([$_SESSION['user_id'], $product_id]);
$existing = $stmt->fetch();

if ($existing) {
    $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$quantity, $_SESSION['user_id'], $product_id]);
} else {
    $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
}

header("Location: cart.php");
exit;
