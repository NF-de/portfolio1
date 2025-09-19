<?php
ini_set('session.gc_maxlifetime', 300);
session_set_cookie_params(0);
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: log_admin.php");
    exit;
}

require_once '../model/BDD.php';
require_once '../model/Classes.php';

use Model\BDD;

// Vérification de l'ID
if (!isset($_GET['id'])) {
    die('ID de la page non spécifié.');
}

$pageId = (int) $_GET['id'];

// Récupération de la page et de son contenu
$page = BDD::getPageById($pageId);
if (!$page) {
    die('Page introuvable.');
}

$contenus = BDD::getContenuByPageId($pageId);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveauTitre = $_POST['titre'] ?? '';
    $paragraphes = $_POST['paragraphes'] ?? [];

    if ($nouveauTitre === '') {
        $erreur = 'Le titre ne peut pas être vide.';
    } else {
        // Mise à jour via la classe BDD
        BDD::modifierPage($pageId, $nouveauTitre);
        BDD::modifierContenu($paragraphes);

        header('Location: backoffice.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier la page</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <video id="background-video" autoplay loop muted>
        <source src="video/page_modifier.mp4" type="video/mp4">
    </video>

    <h1>Modifier la page <?= htmlspecialchars($page->getTitre()) ?></h1>

    <?php if (isset($erreur)): ?>
        <p style="color:red"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="titre">Titre :</label><br>
        <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($page->getTitre()) ?>" required><br><br>

        <?php foreach ($contenus as $index => $contenu): ?>
            <label for="paragraphe_<?= $index ?>">Contenu <?= $index + 1 ?> :</label><br>
            <textarea id="paragraphe_<?= $index ?>" name="paragraphes[<?= $contenu->getId() ?>]" rows="10" cols="50"><?= htmlspecialchars($contenu->getParagraphe()) ?></textarea><br><br>
        <?php endforeach; ?>

        <button type="submit">Enregistrer</button>
        <a href="backoffice.php">Annuler</a>
    </form>
</body>
</html>
