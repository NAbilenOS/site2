<?php
session_start();
require 'db.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die("Votre panier est vide.");
}

$userId = $_SESSION['user_id'];
$totalPrice = 0;

foreach ($_SESSION['cart'] as $productId => $quantity) {
    $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    $totalPrice += $product['price'] * $quantity;
}

$stmt = $conn->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, ?)");
$stmt->execute([$userId, $totalPrice]);

unset($_SESSION['cart']);
echo "Commande passée avec succès !";
?>
