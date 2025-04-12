<?php
session_start();
require __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non connecté']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

if (!$product_id) {
    echo json_encode(['success' => false, 'error' => 'ID produit invalide']);
    exit();
}

try {
    // Récupérer la quantité actuelle
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $currentQty = $stmt->fetchColumn();

    if ($currentQty > 1) {
        // Décrémenter
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $removed = false;
    } else {
        // Supprimer
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $removed = true;
    }

    // Calculer le nouveau total
    $stmt = $conn->prepare("SELECT SUM(p.price * c.quantity) FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    $newTotal = $stmt->fetchColumn() ?? 0;

    echo json_encode([
        'success' => true,
        'removed' => $removed,
        'new_total' => number_format($newTotal, 2)
    ]);

} catch (PDOException $e) {
    error_log("Erreur SQL: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erreur base de données']);
}