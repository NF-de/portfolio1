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

function afficherMessagesAvecBoutons(): string
{
    $html = '';
    $posts = BDD::getBlogPosts();

    foreach ($posts as $post) {
        $html .= '<div class="menu-item">';
        $html .= '<div class="message-content">';
        $html .= '<div class="page-title">' . htmlspecialchars($post['title']) . '</div>';
        $html .= '<p>' . nl2br(htmlspecialchars($post['message'])) . '</p>';

        if (!empty($post['image'])) {
            $html .= '<img src="' . htmlspecialchars($post['image']) . '" alt="Image du message" style="max-width:200px;">';
        }

        $html .= '</div>';
        $html .= '<div class="message-actions">';
        $html .= '<a href="modifier_message.php?id=' . $post['id'] . '" class="btn-modifier">Modifier</a>';
        $html .= '<a href="supprimer_message.php?id=' . $post['id'] . '" class="btn-supprimer" onclick="return confirm(\'Supprimer ce message ?\');">Supprimer</a>';
        $html .= '</div>';
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
        <h2>Créer un nouveau message</h2>
        <form id="albumForm" enctype="multipart/form-data">
            <input type="text" id="title" name="title" placeholder="Titre">
            <input type="text" id="message" name="message" placeholder="Message">
            <input type="file" id="image" name="image" accept="image/*">
            <input type="submit" value="Post">
        </form>

        <h2>Messages existants</h2>
        <?= afficherMessagesAvecBoutons() ?>
    </div>
    </div>
    <a href="logout.php">Se déconnecter</a>

    <script>
        document.getElementById('albumForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const title = document.getElementById('title').value.trim();
            const message = document.getElementById('message').value.trim();

            if (title === '' || message === '') {
                alert('Veuillez remplir les deux champs : Titre et Message.');
                return;
            }

            const form = e.target;
            const formData = new FormData(form);

            fetch('blogConfirm.php', {
                method: 'POST',
                body: formData
            })
                .then(result => {
                    console.log(result);
                    alert('Message posté avec succès !');
                    window.location.reload();
                })

                .catch(error => {
                    console.error('Erreur :', error);
                    alert('Une erreur est survenue lors de la publication.');
                });
        });
    </script>
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