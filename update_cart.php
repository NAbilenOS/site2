<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$data = json_decode(file_get_contents("php://input"), true);
$response = ['success' => false];

if (isset($data['product_id'])) {
    $id = (int) $data['product_id'];

    if (isset($_SESSION['cart'][$id])) {
        if ($data['action'] === 'increase') {
            $_SESSION['cart'][$id]['quantity'] += 1;
        } elseif ($data['action'] === 'decrease' && $_SESSION['cart'][$id]['quantity'] > 1) {
            $_SESSION['cart'][$id]['quantity'] -= 1;
        } elseif ($data['action'] === 'remove') {
            unset($_SESSION['cart'][$id]);
        }
        $response = ['success' => true, 'new_quantity' => $_SESSION['cart'][$id]['quantity'] ?? 0];
    }
} elseif ($data['action'] === 'clear') {
    $_SESSION['cart'] = [];
    $response = ['success' => true];
}

echo json_encode($response);
?>
