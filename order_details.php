<?php
session_start();
require 'php/db.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupération de l'ID de commande
$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$order_id) {
    $_SESSION['order_error'] = "Commande invalide";
    header("Location: order_history.php");
    exit();
}

try {
    // Récupération des détails de la commande
    $stmt = $conn->prepare("CALL DisplayOrderDetails(?)");
    $stmt->execute([$order_id]);
    $order_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérification de l'appartenance de la commande
    $stmt = $conn->prepare("SELECT user_id, total, status FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order_info || $order_info['user_id'] != $_SESSION['user_id']) {
        $_SESSION['order_error'] = "Commande introuvable ou accès refusé";
        header("Location: order_history.php");
        exit();
    }

} catch (PDOException $e) {
    $_SESSION['order_error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: order_history.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la commande | TechStore</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/stylecart.css">
    <link rel="icon" type="image/png" href="images/logoo.png">
</head>
<body>

<header>
    <h1>Détails de la commande #<?= $order_id ?></h1>
    <nav>
        <a href="order_history.php" class="btn btn-primary">← Retour à l'historique</a>
    </nav>
</header>
<div class="products-grid">
<main class="order-container">
    

    <?php if (isset($_SESSION['order_success'])): ?>
        <div class="order-status-banner terminee">
            <?= $_SESSION['order_success'] ?>
        </div>
        <?php unset($_SESSION['order_success']); ?>
    <?php endif; ?>

    <div class="order-status-banner <?= $order_info['status'] ?>">
        Statut : <?= strtoupper($order_info['status']) ?>
    </div>

    <table class="order-details">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Prix unitaire</th>
                <th>Quantité</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_details as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= number_format($item['price'], 2) ?> €</td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['price'] * $item['quantity'], 2) ?> €</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="order-summary">
        <h3>Détails</h3>
        <p>Total commande : <?= number_format($order_info['total'], 2) ?> €</p>
        <p>Date de commande : <?= date('d/m/Y H:i', strtotime($order_details[0]['order_date'])) ?></p>
        
        <?php if ($order_info['status'] == 'en-attente'): ?>
            <form action="cancel_order.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?')">
                <input type="hidden" name="order_id" value="<?= $order_id ?>">
                <button type="submit" class="btn cancel-btn">
                    🚫 Annuler la commande
                </button>
            </form>
        <?php endif; ?>
    </div>
</main>

</div>

</body>
</html>