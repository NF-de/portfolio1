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

// Récupération des pages
$parentPages = BDD::getParentPages();
$pages = BDD::getPagesHierarchy();
$hierarchy = BDD::buildHierarchy($pages);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gestion de l'image uploadée
    $imageName = '';
    if (!empty($_FILES['image']['name'])) {
        $targetDir = '../uploads/';
        $imageName = basename($_FILES['image']['name']);
        $targetFile = $targetDir . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
    }

    // Création de la page
    $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $pageId = BDD::ajouterPage($_POST['titre'], $parentId);

    // Ajout du contenu si présent
    if (!empty($_POST['contenu_titre']) || !empty($_POST['contenu_paragraphe']) || !empty($_POST['map_url']) || $imageName !== '') {
        BDD::ajouterContenu([
            'page_id' => $pageId,
            'titre' => $_POST['contenu_titre'] ?? '',
            'paragraphe' => $_POST['contenu_paragraphe'] ?? '',
            'map_url' => $_POST['map_url'] ?? '',
            'images' => $imageName
        ]);
    }

    // Rafraîchissement de la page après ajout
    header("Location: backoffice.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Backoffice Pages</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <video id="background-video" autoplay loop muted>
        <source src="video/arbre.mp4" type="video/mp4">
    </video>

    <h2>Ajout des pages</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="text" name="titre" placeholder="Titre de la page" required>

        <select name="parent_id">
            <option value="">Aucune page parente</option>
            <?php foreach ($parentPages as $page): ?>
                <option value="<?= $page->getId() ?>"><?= htmlspecialchars($page->getTitre()) ?></option>
            <?php endforeach; ?>
        </select>

        <hr>

        <h3>Contenu associé (facultatif)</h3>
        <input type="text" name="contenu_titre" placeholder="Titre du contenu">
        <textarea name="contenu_paragraphe" placeholder="Paragraphe"></textarea>
        <input type="text" name="map_url" placeholder="Lien Google Maps (facultatif)">
        <div class="form-buttons">
            <input type="file" name="image" accept="image/*">
            <input type="submit" value="Ajouter la page et son contenu">
        </div>
    </form>

    <div class="left-panel">
        <h2>Gestion des pages</h2>
        <?= BDD::displayPages($hierarchy, true) ?>
    </div>

    <a href="logout.php">Se déconnecter</a>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.toggle-button').forEach(button => {
                button.addEventListener('click', () => {
                    const parent = button.parentElement;
                    parent.classList.toggle('expanded');
                    button.textContent = parent.classList.contains('expanded') ? '▼' : '▶';
                });
            });
        });
    </script>
</body>
</html>
