<?php
require 'php/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock']; // 🔹 Quantité en stock

    // Gérer l'upload de l'image
    $target_dir = "images/";
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;

    // Vérification des caractéristiques
    $material = $_POST['material'];
    $warranty = $_POST['warranty'];
    $weight = $_POST['weight'];
    $color = $_POST['color'];

    // Création de la colonne `features`
    $features = "Matériau: $material, Garantie: $warranty, Poids: $weight, Couleur: $color";

    // Vérifier et enregistrer l'image
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, material, warranty, weight, color, features, stock) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $image_name, $material, $warranty, $weight, $color, $features, $stock]);

        echo "<script>alert('✅ Produit ajouté avec succès !'); window.location='manage_products.php';</script>";
    } else {
        echo "<script>alert('❌ Erreur lors du téléchargement de l\'image.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Produit</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="images/logoo.png">
</head>
<body>
    <div class="form-container">
        <h1 class="form-title">Ajouter un nouveau produit</h1>
        
        <form class="product-form" action="add_product.php" method="POST" enctype="multipart/form-data">
            <div class="form-group full-width">
                <label class="form-label">Nom du produit :</label>
                <input type="text" name="name" class="form-input" required>
            </div>

            <div class="form-group full-width">
                <label class="form-label">Description :</label>
                <textarea name="description" class="form-textarea" required></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Prix (€) :</label>
                <input type="number" step="0.01" name="price" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">Stock :</label>
                <input type="number" name="stock" class="form-input" value="10" min="0" required>
            </div>

            <div class="form-group full-width">
                <label class="form-label">Image :</label>
                <input type="file" name="image" class="form-file" accept="image/*" required>
            </div>

            <section class="features-section">
                <h3 class="form-label">Caractéristiques techniques</h3>
                
                <div class="form-group">
                    <label class="form-label">Matériau :</label>
                    <input type="text" name="material" class="form-input" value="Acier inoxydable" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Garantie :</label>
                    <input type="text" name="warranty" class="form-input" value="2 ans" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Poids :</label>
                    <input type="text" name="weight" class="form-input" value="1.2 kg" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Couleur :</label>
                    <input type="text" name="color" class="form-input" value="Noir / Blanc / Gris" required>
                </div>
            </section>

            <button type="submit" class="form-submit">➕ Publier le produit</button>
        </form>

        <a href="manage_products.php" class="back-link">← Retour à la gestion</a>
    </div>
</body>
</html>
