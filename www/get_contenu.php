<?php
require_once '../model/BDD.php';
require_once '../model/Classes.php';

use Model\BDD;

if (isset($_GET['page_id'])) {
    $pageId = (int) $_GET['page_id'];

    $db = new SQLite3('../data/db-cosmodrome.db');
    $stmt = $db->prepare("SELECT titre FROM pages WHERE id = :id");
    $stmt->bindValue(':id', $pageId, SQLITE3_INTEGER);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    $titrePage = $result['titre'] ?? '';

           $contenus = BDD::getContenuByPageId($pageId);

        foreach ($contenus as $contenu) {
            echo "<h2>" . htmlspecialchars($contenu->getTitre()) . "</h2>";
            echo "<p>" . nl2br(htmlspecialchars($contenu->getParagraphe())) . "</p>";

            $images = $contenu->getImages() ? explode(',', $contenu->getImages()) : [];
            foreach ($images as $img) {
                $img = trim($img);
                if ($img) {
                    echo "<img src='image/$img' alt='' />";
                }
            }

            $mapUrl = $contenu->getMapUrl();
            if ($mapUrl) {
                echo "<iframe src='$mapUrl' width='600' height='450' style='border:0;' allowfullscreen='' loading='lazy' referrerpolicy='no-referrer-when-downgrade'></iframe>";
            }
        }
    }

