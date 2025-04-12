<?php
session_start();
require __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Non connecté"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];
$action = $_POST['action'];

try {
    if($action === 'increase') {
        $stmt = $conn->prepare("
            UPDATE cart 
            SET quantity = quantity + 1 
            WHERE user_id = ? AND product_id = ?
        ");
    } else {
        $stmt = $conn->prepare("
            UPDATE cart 
            SET quantity = GREATEST(quantity - 1, 1) 
            WHERE user_id = ? AND product_id = ?
        ");
    }

    $stmt->execute([$user_id, $product_id]);
    
    // Récupérer la nouvelle quantité
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $result = $stmt->fetch();

    echo json_encode([
        "success" => true,
        "new_quantity" => $result['quantity']
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}