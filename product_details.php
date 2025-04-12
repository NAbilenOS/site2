<?php
session_start(); // Démarrer la session
require 'php/db.php'; // Connexion à la base de données

// Vérifier si l'ID du produit est présent dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Produit introuvable !");
}

$product_id = (int)$_GET['id'];

// Récupérer le produit depuis la base de données
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Produit introuvable !");
}
$stmt = $conn->query("SELECT * FROM products");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Détails</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/styleprod.css">
    <link rel="icon" type="image/png" href="images/logoo.png">
</head>
<body>

<header>
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <nav>
        <a href="index.php" class="add-btn"> Accueil</a>
    </nav>
</header>

<main class="product-hero">
    <div class="product-3d-container">
        <img src="images/<?= htmlspecialchars($product['image']) ?>" 
             alt="<?= htmlspecialchars($product['name']) ?>" 
             class="product-main-image">
    </div>
    
    <div class="product-info-section">
        <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
        <p class="product-price">$<?= number_format($product['price'], 2) ?></p>
        <div class="product-description">
            <h3>Description</h3>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        </div>
        
        <div class="product-features">
            <h3>Caractéristiques</h3>
            <ul class="features-list">
                <li>Matériau : <?= htmlspecialchars($product['material']) ?></li>
                <li>Garantie : <?= htmlspecialchars($product['warranty']) ?></li>
                <li>Poids : <?= htmlspecialchars($product['weight']) ?></li>
                <li>Couleur : <?= htmlspecialchars($product['color']) ?></li>
            </ul>
        </div>
        <h3>Stock :<?= number_format($product['stock']) ?></h3>
        <?php if(isset($_SESSION['user_id'])): ?>
            <form class="ajax-cart-form">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <button type="submit" class="btn btn-primary">Ajouter au panier</button>
            </form>
        <?php else: ?>
            <button class="btn btn-primary" onclick="window.location.href='signup.php'">Ajouter au panier</button>
        <?php endif; ?>
    </div>
</main>
<section class="related-products">
    <h2 class="related-title">Produits similaires</h2>
    <div class="products-grid" id="products">
    <?php foreach ($products as $product): ?>
    <div class="product-card">
        <img src="images/<?= $product['image'] ?>" class="product-image" alt="<?= $product['name'] ?>" onclick="window.location.href='product_details.php?id=<?= $product['id'] ?>'">
        <h3><a><?= htmlspecialchars($product['name']) ?></a></h3>
        <p class="price"><?= number_format($product['price'], 2) ?> €</p>
        <?php if(isset($_SESSION['user_id'])): ?>
            <form class="ajax-cart-form">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <button type="submit" class="btn btn-primary">Ajouter au panier</button>
            </form>
        <?php else: ?>
            <button class="btn btn-primary" onclick="window.location.href='signup.php'">Ajouter au panier</button>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>


<script>
document.querySelectorAll('.ajax-cart-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        fetch('php/add_to_cart.php', {
            method: 'POST',
            body: new URLSearchParams(new FormData(this))
        })
        .then(response => {
            if(response.redirected) {
                window.location.href = response.url;
            }
            return response.json();
        })
        .then(data => {
            if(data.success) {
                alert('✅ Produit ajouté !');
            }
        });
    });
});

function showCartNotification(message) {
    const notif = document.createElement('div');
    notif.className = 'cart-notification';
    notif.textContent = message;
    document.body.appendChild(notif);
    
    setTimeout(() => notif.remove(), 3000);
}
</script>
    </script>
</body>
</html>
