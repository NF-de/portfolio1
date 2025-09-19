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

$pages = BDD::getPagesHierarchy();
$hierarchy = BDD::buildHierarchy($pages);

function afficherPagesAvecBoutons(array $pages): string
{
    $html = '';

    foreach ($pages as $page) {
        $hasChildren = !empty($page->getChildren());
        $html .= '<div class="menu-item">';
        if ($hasChildren) {
            $html .= '<span class="toggle-button">▶</span>';
        } else {
            $html .= '<span style="display:inline-block; width: 15px;"></span>';
        }

        $html .= '<span class="page-title">' . htmlspecialchars($page->getTitre()) . '</span>';
        $html .= ' <a href="modifier.php?id=' . $page->getId() . '" class="btn-modifier">Modifier</a>';
        $html .= ' <a href="supprimer.php?id=' . $page->getId() . '" class="btn-supprimer" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cette page ?\');">Supprimer</a>';

        if ($hasChildren) {
            $html .= '<div class="children">';
            $html .= afficherPagesAvecBoutons($page->getChildren());
            $html .= '</div>';
        }

        $html .= '</div>';
    }

    return $html;
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
    <h2>Ajout des pages</h2>

    <form action="ajouter_page.php" method="post" enctype="multipart/form-data">
        <input type="text" name="titre" placeholder="Titre de la page" required>

        <select name="parent_id">
            <option value="">Aucune page parente</option>
            <?php foreach ($pages as $page): ?>
                <option value="<?= $page->getId() ?>"><?= htmlspecialchars($page->getTitre()) ?></option>
            <?php endforeach; ?>
        </select>

        <hr>

        <h3>Contenu associé (facultatif)</h3>
        <input type="text" name="contenu_titre" placeholder="Titre du contenu">
        <textarea name="contenu_paragraphe" placeholder="Paragraphe"></textarea>
        <input type="text" name="map_url" placeholder="Lien Google Maps (facultatif)">

        <input type="file" name="image" accept="image/*">

        <input type="submit" value="Ajouter la page et son contenu">
    </form>

    <div class="left-panel">
        <h2>Gestion des pages</h2>
        <?= afficherPagesAvecBoutons($hierarchy) ?>
    </div>

    <div class="right-panel">
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
    <script>
        document.getElementById('contentForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(e.target);

            fetch('ajouter_contenu.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Erreur lors de l'ajout du contenu.");
                    }
                    return response.text();
                })
                .then(data => {
                    alert("Contenu ajouté !");
                    window.location.reload();
                })
                .catch(error => {
                    console.error("Erreur :", error);
                    alert("Échec de l'ajout du contenu.");
                });
        });
    </script>

</body>

</html>