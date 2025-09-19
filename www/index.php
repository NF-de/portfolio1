<?php
require('../model/Classes.php');
require('../model/BDD.php');

use Model\BDD;

$pages = BDD::getPagesHierarchy();

$pageHierarchy = BDD::buildHierarchy($pages);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio</title>
    <link rel="stylesheet" href="css/acceuil.css">
    <link rel="shortcut icon" href="image/icon.png">
</head>

<body>
    <video id="background-video" autoplay loop muted>
        <source src="video/coree.mp4" type="video/mp4">
    </video>
    <div class="overlay"></div>

    <div class="content">
        <h1 class="title">BIENVENUE SUR MON PORTFOLIO</h1>
    </div>


<div class="menu-header">
            <?php echo BDD::displayPages($pageHierarchy); ?>
        </div>

    <div class="contenu">
        

        <div class="container">
            <div class="presentation">
                <div class="presentation-container">
                    <h2>À propos de moi</h2>
                    <p>
                        Bonjour ! Je m'appelle Fialoux Mateo et je suis passionné par le développement web et de
                        développement de jeux vidéo.
                        Je crée des projets, des sites internets et des applications interactives pour partager ma passion et
                        mon savoir-faire.
                    </p>
                    <p>
                        Mon paracours :
                        <br>
                        Bac Pro Systèmes Numériques → formation technique solide en réseau.
                        <br>
                        BTS SIO (Services Informatiques aux Organisations) → acquisition des compétences en développement.
                    </p>
                    <p>
                        Sur ce site, vous découvrirez mes travaux, mes expériences et mes projets liés au développement web ainsi que le développement de jeux vidéo.
                    </p>
                    <img src="image/photo.png" alt="Portrait de Fialoux Mateo" class="portrait">
                </div>
            </div>

            <?php
            if (isset($_GET['page_id'])) {
                $pageId = (int) $_GET['page_id'];
            } else {
                $pageId = 6;
            }
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
            ?>

        </div>
    </div>



    <footer class="footer">
        <div class="footer-content">
            <p>⛩️ &copy; 2025 Portfolio — Mateo Fialoux</p>
            <small>Promouvoir la passion du développement</small>
            <nav class="footer-nav">
                <a href="">📘Facebook</a>
                <a href="">📸Instagram</a>
            </nav>
            <br>
            <a href="log_admin.php"><button class="neon-btn">Connexion</button></a>
       </div>
    </footer>
</body>
<script src="javascript/script.js"></script>

</html>