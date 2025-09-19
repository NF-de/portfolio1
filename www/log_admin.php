<?php
session_start();
require_once '../model/BDD.php';

use Model\BDD;


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if (BDD::authenticateUser($username, $password)) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;

            // Assurez-vous qu'il n'y a pas de sortie avant cette ligne
            header("Location: backoffice.php");
            exit;
        } else {
            $error = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    } else {
        $error = "Les champs username et password sont requis.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin</title>
    <link rel="stylesheet" href="css/styles_log_admin.css">
</head>

<body>
    <video id="background-video" autoplay loop muted>
        <source src="video/temple.mp4" type="video/mp4">
    </video>
    <div class="content">
        <div class="login-container">
            <h2>Connexion Admin</h2>
            <?php if (isset($error))
                echo "<p class='error'>$error</p>"; ?>
            <form method="post">
                <input type="text" name="username" placeholder="Nom d'utilisateur" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <input type="submit" value="Se connecter">
            </form>
        </div>
    </div>
</body>

</html>