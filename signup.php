<?php
require 'php/db.php';

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $message = "❌ Cet email est déjà utilisé !";
    } else {
        // Insérer le nouvel utilisateur
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $password])) {
            header("Location: login.php?success=1"); 
            exit();
        } else {
            $message = "❌ Erreur lors de l'inscription.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | eCommerce</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="images/logoo.png">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <img src="images/logoo.png" class="auth-logo" alt="Logo">
            <h2 class="auth-title">Rejoignez notre communauté</h2>
            <p class="auth-subtitle">Créez votre compte </p>
        </div>

        <?php if ($message): ?>
            <div class="error-message"><?= $message ?></div>
        <?php endif; ?>

        <form class="auth-form" action="signup.php" method="POST">
            <div class="form-group">
                <label>Nom complet</label>
                <input type="text" name="username" class="form-input" required placeholder="nom">
            </div>

            <div class="form-group">
                <label>Adresse Email</label>
                <input type="email" name="email" class="form-input" required placeholder="exemple@email.com">
            </div>

            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" class="form-input" required placeholder="••••••••">
            </div>

            <button type="submit" class="auth-btn">Créer mon compte</button>
        </form>

        <div class="auth-links">
            <p>Déjà inscrit ? <a href="login.php" class="auth-link">Se connecter</a></p>
        </div>
    </div>
</body>
</html>
