<?php
session_start();
require 'php/db.php'; // Connexion à la base de données

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Vérifier si l'utilisateur existe
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($user && password_verify($password, $user['password'])) {
            // Démarrer la session
            session_start();
        
            // Stocker les infos utilisateur
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['cart'] = [];
        
            // Vérification des rôles
            $_SESSION['is_admin'] = ($user['admin'] == '1') ? true : false;
            $_SESSION['is_user'] = ($user['admin'] == '0') ? true : false;
        
            // Redirection selon le rôle
            if ($_SESSION['is_admin']) {
                header("Location: manage_products.php");
            } else {
                header("Location: index.php");
            } 
            exit();
        
        
        } else {
            $error = "❌ Email ou mot de passe incorrect.";
        }
    } else {
        $error = "❌ Aucun compte trouvé avec cet email.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter | eCommerce</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="images/logoo.png">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <img src="images/logoo.png" class="auth-logo" alt="Logo">
            <h2 class="auth-title">Content de vous revoir !</h2>
            <p class="auth-subtitle">Connectez-vous à votre compte</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <form class="auth-form" method="POST">
            <div class="form-group">
                <label>Adresse Email</label>
                <input type="email" name="email" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" class="form-input" required>
            </div>

            <button type="submit" class="auth-btn">Connexion</button>
        </form>

        <div class="auth-links">
            <p>Pas de compte ? <a href="signup.php" class="auth-link">S'inscrire</a></p>
            <p><a href="#" class="auth-link">Mot de passe oublié ?</a></p>
        </div>
    </div>
</body>
</html>
