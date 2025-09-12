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
    <title>Cosmodrome</title>
    <link rel="stylesheet" href="acceuil.css">
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
        <?php echo BDD::displayPages($pageHierarchy); ?>
    </header>
    <div class="contenu">
        <div class="container">
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
                        echo "<img src='image/$img' alt='' style='max-width:800px;' />";
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
            <p>🚀 &copy; 2025 Cosmodrome — L’aventure spatiale commence ici</p>
            <small>Promouvoir la passion de l’astronomie et l’exploration des étoiles</small>
            <nav class="footer-nav">
                <a href="https://www.facebook.com/people/CosmoDr%C3%B4me-Observatoire-Claude-Tavenier/100039266894686/">📘Facebook</a>
                <a href="https://www.instagram.com/explore/locations/934652393232471/cosmodrome-observatoire-claude-tavenier/">📸Instagram</a>
            </nav>
        </div>
    </footer>
</body>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const pageId = this.dataset.id;

                fetch('get_contenu.php?page_id=' + pageId)
                    .then(response => response.text())
                    .then(html => {
                        const contenuDiv = document.querySelector('.contenu');
                        contenuDiv.innerHTML = html;
                        contenuDiv.scrollIntoView({ behavior: 'smooth' });
                    })
                    .catch(error => {
                        console.error('Erreur AJAX :', error);
                    });
            });
        });
    });
</script>
   <script src="script.js"></script>
</html>