<?php
session_start();
require 'php/db.php'; 
$stmt = $conn->query("SELECT * FROM products");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>eCommerce - Accueil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/styleuser.css">
    <link rel="icon" type="image/png" href="images/logoo.png">

    <script src="js/script.js"></script>
</head>
<body>
    
<header>
    <h1>eCommerce Project</h1>
    <nav>
        <?php if(isset($_SESSION['user_id'])): ?> 
            <span >  hello,<?= htmlspecialchars($_SESSION['user_name']) ?>  👋</span> 
            <a href="cart.php" class="add-btn" >Panier</a>
            <a href="logout.php" class="add-btn" >Déconnexion</a>
           
        <?php else: ?>
            <a href="login.php" class="add-btn">Connexion</a>
            <a href="signup.php" class="add-btn">Inscription</a>
        <?php endif; ?>
    </nav>
</header>
<section class="banner">
    <div class="banner-content">
        <h2>Bienvenue sur TechStore</h2>
        <p class="banner-subtitle">Découvrez nos dernières arrivage</p>
        <a href="#ss" class="btn btn-primary">Explorer les articles</a>
    </div>
</section>
    <div class="search-container" >
        <input type="text" id="liveSearch" placeholder="🔍 Rechercher un produit..." class="search-input">
    </div>
    <div class="price-filters"id="ss">
        <input type="number" id="minPrice" placeholder="Prix min" class="filter-input" min="0">
        <input type="number" id="maxPrice" placeholder="Prix max" class="filter-input" min="0">
  
    </div>
<main>
<div class="products-grid" id="products">
    <?php foreach ($products as $product): ?>
        <div class="product-card" data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>"data-price="<?= floatval($product['price']) ?>">
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
</main>

</body>
</html>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const liveSearch = document.getElementById('liveSearch');
    const minPrice = document.getElementById('minPrice');
    const maxPrice = document.getElementById('maxPrice');
    const productCards = document.querySelectorAll('.product-card');

    function filterProducts() {
        const searchTerm = liveSearch.value.trim().toLowerCase();
        const min = parseFloat(minPrice.value) || 0;
        const max = parseFloat(maxPrice.value) || Infinity;

        productCards.forEach(card => {
            const productName = card.dataset.name;
            const price = parseFloat(card.dataset.price);
            
            const nameMatch = productName.includes(searchTerm);
            const priceMatch = price >= min && price <= max;
            
            const isVisible = nameMatch && priceMatch;
            
            card.style.opacity = isVisible ? '1' : '0';
            card.style.transform = isVisible ? 'scale(1)' : 'scale(0.9)';
            card.style.pointerEvents = isVisible ? 'all' : 'none';
            card.style.position = isVisible ? 'static' : 'absolute';
        });
    }

    // Écouteurs d'événements avec debounce
    [liveSearch, minPrice, maxPrice].forEach(input => {
        input.addEventListener('input', debounce(filterProducts, 300));
    });

    // Fonction debounce existante
    function debounce(func, timeout = 300){
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => { func.apply(this, args); }, timeout);
        };
    }
});
minPrice.addEventListener('change', function() {
    if(parseFloat(this.value) > parseFloat(maxPrice.value)) {
        maxPrice.value = '';
    }
});

// Reset min price si max < min
maxPrice.addEventListener('change', function() {
    if(parseFloat(this.value) < parseFloat(minPrice.value)) {
        minPrice.value = '';
    }
});
liveSearch.addEventListener('input', debounce(function(e) {
    // Le code de filtrage ici
}));
</script>
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
</script>