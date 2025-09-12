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

if (!isset($_GET['id'])) {
    die('ID de la page non spécifié.');
}

$pageId = intval($_GET['id']);


$pages = BDD::getPagesHierarchy();
$hierarchy = BDD::buildHierarchy($pages);

function findPageById(array $pages, int $id)
{
    foreach ($pages as $page) {
        if ($page->getId() === $id) {
            return $page;
        }
        $children = $page->getChildren();
        if ($children) {
            $found = findPageById($children, $id);
            if ($found)
                return $found;
        }
    }
    return null;
}

$page = findPageById($hierarchy, $pageId);

if (!$page) {
    die('Page introuvable.');
}


$contenus = BDD::getContenuByPageId($pageId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveauTitre = $_POST['titre'] ?? '';
    $paragraphes = $_POST['paragraphes'] ?? [];

    if ($nouveauTitre === '') {
        $erreur = 'Le titre ne peut pas être vide.';
    } else {
        $bdd = new SQLite3('../data/db-cosmodrome.db');

        // Mise à jour du titre
        $stmt = $bdd->prepare('UPDATE pages SET titre = :titre WHERE id = :id');
        $stmt->bindValue(':titre', $nouveauTitre, SQLITE3_TEXT);
        $stmt->bindValue(':id', $pageId, SQLITE3_INTEGER);
        $stmt->execute();

        // Mise à jour des paragraphes
        foreach ($paragraphes as $contenuId => $texte) {
            $stmt2 = $bdd->prepare('UPDATE contenu SET paragraphe = :paragraphe WHERE id = :id');
            $stmt2->bindValue(':paragraphe', $texte, SQLITE3_TEXT);
            $stmt2->bindValue(':id', $contenuId, SQLITE3_INTEGER);
            $stmt2->execute();
        }

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
    <h1>Modifier la page <?= htmlspecialchars($page->getTitre()) ?></h1>

    <?php if (isset($erreur)): ?>
        <p style="color:red"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="titre">Titre :</label><br>
        <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($page->getTitre()) ?>" required><br><br>

        <?php foreach ($contenus as $index => $contenu): ?>
            <label for="paragraphe_<?= $index ?>">Contenu <?= $index + 1 ?> :</label><br>
            <textarea id="paragraphe_<?= $index ?>" name="paragraphes[<?= $contenu->getId() ?>]" rows="10"
                cols="50"><?= htmlspecialchars($contenu->getParagraphe()) ?></textarea><br><br>
        <?php endforeach; ?>

        <button type="submit">Enregistrer</button>
        <a href="backoffice.php">Annuler</a>
    </form>
</body>

</html>