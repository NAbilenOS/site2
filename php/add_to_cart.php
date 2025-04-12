<?php
session_start();
require __DIR__ . '/db.php';


if (!isset($_SESSION['user_id'])) {
    // Rediriger vers signup.php si non connecté
    header('Location: signup.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];

try {
    // Vérifier l'existence du produit
    $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    
    if (!$stmt->fetch()) {
        throw new Exception("Produit introuvable");
    }

    // Insertion/Mise à jour du panier
    $stmt = $conn->prepare("
        INSERT INTO cart (user_id, product_id, quantity)
        VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE quantity = quantity + 1
    ");
    
    if ($stmt->execute([$user_id, $product_id])) {
        $_SESSION['cart_message'] = '✅ Produit ajouté au panier !';
    } else {
        $_SESSION['cart_message'] = '❌ Erreur lors de l\'ajout';
    }

} catch (PDOException $e) {
    error_log("Erreur Panier: " . $e->getMessage());
    $_SESSION['cart_message'] = '❌ Erreur système - Réessayez plus tard';
} catch (Exception $e) {
    $_SESSION['cart_message'] = '❌ ' . $e->getMessage();
}
$stmt = $conn->prepare("SELECT SUM(p.price * c.quantity) FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$newTotal = $stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'new_total' => number_format($newTotal, 2)
]);
exit();
// Redirection vers la page précédente
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();