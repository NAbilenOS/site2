<?php
require 'php/db.php';
session_start();

$user_id = $_SESSION['user_id'];
$total_price = 0; // Initialisation correcte
$product_details = [];

// Récupération des produits AVEC calcul du total
$sql = "SELECT p.id, p.name, p.price, p.image, c.quantity 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($cart_items as $item) {
    $product_details[$item['id']] = [
        'name' => $item['name'],
        'price' => (float)$item['price'],
        'image' => $item['image'],
        'quantity' => (int)$item['quantity'] 
    ];
    $total_price += $item['price'] * $item['quantity']; 
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier | eCommerce</title>
    <link rel="icon" type="image/png" href="images/logoo.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/styleuser.css">
    <link rel="stylesheet" href="css/stylecart.css">
</head>
<body>

<header>
    <h1>Mon Panier 🛒</h1>
    <nav>
        <a href="index.php" class="add-btn">⬅ Retour</a>
        <a href="order_history.php" class="add-btn">📦 Voir mes commandes</a>
    </nav>
</header>
<?php if (isset($_SESSION['order_error'])): ?>
        <div class="cart-message error">
            <?= $_SESSION['order_error'] ?>
        </div>
        <?php unset($_SESSION['order_error']); ?>
    <?php endif; ?>

<main class="cart-container">

    <?php if (!empty($product_details)): ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Produit</th>
                    <th>Prix Unitaire</th>
                    <th>Quantité</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($product_details as $id => $item): ?>
                <tr>
                    <td data-label="Image">
                        <img src="images/<?= $item['image'] ?>" 
                             class="cart-item-image" 
                             alt="<?= $item['name'] ?>">
                    </td>
                    <td data-label="Produit"><?= $item['name'] ?></td>
                    <td data-label="Prix">$<?= number_format($item['price'], 2) ?></td>
                    <td data-label="Quantité">
    <div class="quantity-control">
        <button class="quantity-btn minus-btn" 
                data-product-id="<?= $id ?>">−</button>
            <input type="text" class="quantity-input" 
               value="<?= $item['quantity'] ?>" 
               data-price="<?= $item['price'] ?>" 
               readonly>
                <button class="quantity-btn plus-btn" 
                data-product-id="<?= $id ?>">+</button>
                </div>
            </td>
                    <td data-label="Total">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-total">
            <h3>Total : $<?= number_format($total_price, 2) ?></h3>
        </div>

        <div class="cart-actions">
            <a href="clear_cart.php" class="btn btn-primary">🗑 Vider le panier</a>
            <a href="checkout.php" class="btn btn-primary">🛍 Commander maintenant</a>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <h2>Votre panier est vide 😢</h2>
            <p>Commencez vos achats dès maintenant !</p>
            <a href="index.php" class="btn btn-primary">Découvrir les produits</a>
        </div>
    <?php endif; ?>
</main>
<script>
document.querySelectorAll('.plus-btn').forEach(button => {
    button.addEventListener('click', async (e) => {
        e.preventDefault();
        const productId = button.dataset.productId;
        
        try {
            const response = await fetch('php/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            });
            const data = await response.json();
            if(data.success) {
                const input = button.parentNode.querySelector('.quantity-input');
                input.value = parseInt(input.value) + 1;
                updateTotal(data.new_total);
            } else {
                alert(data.error);
            }
        } catch (error) {
            console.error('Erreur:', error);
        }
    });
});

// Gestion du bouton -
document.querySelectorAll('.minus-btn').forEach(button => {
    button.addEventListener('click', async (e) => {
        e.preventDefault();
        const productId = button.dataset.productId;
        const input = button.parentNode.querySelector('.quantity-input');
        const currentQty = parseInt(input.value);

        try {
            const response = await fetch('php/remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            });

            const data = await response.json();
            
            if(data.success) {
                if(data.removed) {
                    // Supprimer la ligne du tableau
                    button.closest('tr').remove();
                } else {
                    // Mettre à jour la quantité
                    input.value = currentQty - 1;
                }
                // Mettre à jour le total global
                document.querySelector('.cart-total h3').textContent = 
                    `Total : $${data.new_total}`;
            } else {
                alert('Erreur: ' + (data.error || 'Action impossible'));
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Une erreur réseau est survenue');
        }
    });
});

// Fonction pour mettre à jour le total global
function updateTotal(newTotal) {
    document.querySelectorAll('.cart-total h3').forEach(totalElement => {
        totalElement.textContent = `Total : $${newTotal}`;
    });
}
</script>
<script src="js/script.js"></script>

</body>
</html>
