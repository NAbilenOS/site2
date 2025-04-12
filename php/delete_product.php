<?php
require 'db.php'; // Connexion à la base de données

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST["product_id"];

    // Récupérer l'image du produit avant suppression
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if ($product) {
        // Supprimer l'image du dossier images/
        $image_path = "../images/" . $product['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        // Supprimer le produit de la base de données
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);

        echo "✅ Produit supprimé avec succès !";
    } else {
        echo "❌ Produit introuvable.";
    }
}

// Rediriger vers la page de gestion des produits
header("Location: ../manage_products.php");
exit;
?>
