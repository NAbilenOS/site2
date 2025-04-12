<?php
session_start(); // Démarrer la session
require 'php/db.php'; // Connexion à la base de données

// Supprimer un produit
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Vérifier si le produit existe
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Supprimer l'image associée
        $image_path = "images/" . $product['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        // Supprimer le produit de la base
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        echo "<script>alert('Produit supprimé avec succès !'); window.location='manage_products.php';</script>";
    }
    
}

// Récupérer tous les produits
$stmt = $conn->query("SELECT * FROM products");
$products = $stmt->fetchAll();
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les Produits</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="images/logoo.png">
</head>
<body>
    <header>
        <h1 style="color: var(--primary-blue);">Page Admin</h1>
        <nav>
            <a href="logout.php" class="btn btn-secondary"> Déconnexion</a>
            <span class="btn btn-primary">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span> 
        </nav>
    </header>

    <div class="manage-container">
        <div class="manage-header">
            <h1 class="manage-title">Gestion des produits</h1>
            <a href="add_product.php" class="add-product-btn">
                ➕ Ajouter un produit
            </a>
        </div>

        <table class="products-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Stock</th>
                    <th>Prix</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td data-label="Image">
                        <img src="images/<?= htmlspecialchars($product['image']) ?>" 
                             class="product-thumb" 
                             alt="<?= htmlspecialchars($product['name']) ?>">
                    </td>
                    <td data-label="Nom"><?= htmlspecialchars($product['name']) ?></td>
                    <td data-label="Stock"><?= number_format($product['stock']) ?></td>
                    <td data-label="Prix">$<?= number_format($product['price'], 2) ?></td>
                    <td data-label="Actions" class="actions-cell">
                        <a href="edit_product.php?id=<?= $product['id'] ?>" 
                           class="manage-btn edit-btn">
                           ✏ Éditer
                        </a>
                        <a href="manage_products.php?delete=<?= $product['id'] ?>" 
                           class="manage-btn delete-btn"
                           onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?')">
                           🗑 Supprimer
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
