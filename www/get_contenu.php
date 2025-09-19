<?php
require_once '../model/BDD.php';
require_once '../model/Classes.php';

use Model\BDD;

if (isset($_GET['page_id'])) {
    $pageId = (int) $_GET['page_id'];

    // Récupérer directement les contenus
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
