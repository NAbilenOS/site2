<?php
session_start();
require 'php/db.php';

$stmt = $conn->prepare("CALL DisplayOrderHistory(?)");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
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
    <h1> Mes Commendes </h1>
    <nav>
    <a href="cart.php" class="btn btn-primary">⬅ Retour</a>
    </nav>
</header>

<main class="order-container">

    <div class="order-history">
        <?php foreach ($orders as $order): ?>
        <div class="order-card">
            <h3>Commande #<?= $order['id'] ?></h3>
            <p>Date: <?= $order['order_date'] ?></p>
            <p>Total: <?= $order['total'] ?> €</p>
            <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-secondary">Détails</a>
        </div>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>






