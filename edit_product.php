<?php
require 'php/db.php'; // Connexion à la base de données

if (!isset($_GET['id'])) {
    header("Location: manage_products.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: manage_products.php");
    exit();
}

// Mettre à jour le produit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock']; // Mise à jour du stock

    // Récupérer les nouvelles caractéristiques
    $material = $_POST['material'];
    $warranty = $_POST['warranty'];
    $weight = $_POST['weight'];
    $color = $_POST['color'];

    // Mettre à jour le produit avec les nouvelles caractéristiques
    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, material = ?, warranty = ?, weight = ?, color = ? WHERE id = ?");
    $stmt->execute([$name, $description, $price, $stock, $material, $warranty, $weight, $color, $id]);

    echo "<script>alert('✅ Produit modifié avec succès !'); window.location='manage_products.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un Produit</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="images/logoo.png">
</head>
<body>
    <div class="edit-container">
        <h1 class="edit-title">Modifier le produit</h1>
        
        <div class="current-image">
            <img src="images/<?= htmlspecialchars($product['image']) ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>">
        </div>

        <form class="edit-form" action="edit_product.php?id=<?= $id ?>" method="POST">
            <div class="form-group full-width">
                <label class="form-label">Nom du produit :</label>
                <input type="text" name="name" class="form-input" 
                       value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="form-group full-width">
                <label class="form-label">Description :</label>
                <textarea name="description" class="form-textarea" required><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Prix (€) :</label>
                <input type="number" step="0.01" name="price" class="form-input" 
                       value="<?= $product['price'] ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Stock :</label>
                <input type="number" name="stock" class="form-input" 
                       value="<?= $product['stock'] ?>" min="0" required>
            </div>

            <section class="edit-features">
                <h3 class="form-label">Caractéristiques techniques</h3>
                
                <div class="form-group">
                    <label class="form-label">Matériau :</label>
                    <input type="text" name="material" class="form-input" 
                           value="<?= htmlspecialchars($product['material']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Garantie :</label>
                    <input type="text" name="warranty" class="form-input" 
                           value="<?= htmlspecialchars($product['warranty']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Poids :</label>
                    <input type="text" name="weight" class="form-input" 
                           value="<?= htmlspecialchars($product['weight']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Couleur :</label>
                    <input type="text" name="color" class="form-input" 
                           value="<?= htmlspecialchars($product['color']) ?>" required>
                </div>
            </section>

            <button type="submit" class="update-btn">💾 Enregistrer les modifications</button>
        </form>

        <a href="manage_products.php" class="back-link">← Retour à la gestion</a>
    </div>
</body>
</html>