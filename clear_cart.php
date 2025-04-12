<?php
session_start();
require __DIR__ . '/php/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['cart_message'] = "❌ Connectez-vous d'abord";
    header('Location: login.php');
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    $_SESSION['cart_message'] = "✅ Panier vidé avec succès";
} catch (PDOException $e) {
    $_SESSION['cart_message'] = "❌ Erreur lors de la suppression";
}

header('Location: cart.php');
exit();
?>
