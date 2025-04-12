<?php
session_start();
require 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Supprimer beginTransaction() ici
    
    $stmt = $conn->prepare("CALL FinalizeOrder(?)");
    $stmt->execute([$_SESSION['user_id']]);

    $_SESSION['order_success'] = "Commande validée avec succès !";
    header("Location: order_history.php");
    exit();

} catch (PDOException $e) {
    $errorMessage = $e->getMessage();
    
    // Gestion spécifique des erreurs de stock
    if (strpos($errorMessage, 'pas assez de stock') !== false) {
        $_SESSION['order_error'] = "Certains produits ne sont plus disponibles en quantité suffisante. Veuillez vérifier votre panier.";
    } else {
        $_SESSION['order_error'] = "Une erreur inattendue est survenue. Veuillez réessayer.";
    }
    
    header("Location: cart.php");
    exit();
}
?>