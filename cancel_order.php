<?php
session_start();
require 'php/db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['order_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $stmt = $conn->prepare("CALL CancelOrder(?, ?)");
    $stmt->execute([$_POST['order_id'], $_SESSION['user_id']]);
    
    $_SESSION['order_message'] = "Commande annulée avec succès";
} catch (PDOException $e) {
    $_SESSION['order_error'] = "Erreur d'annulation : " . $e->getMessage();
}

header("Location: order_history.php");
exit();
?>