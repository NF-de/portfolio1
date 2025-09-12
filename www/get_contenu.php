<?php
require_once '../model/BDD.php';
require_once '../model/Classes.php';

use Model\BDD;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contenu de la Page</title>
    <link rel="stylesheet" href="acceuil.css">
</head>
<body>
    <div class="container">
        <?php
        if (isset($_GET['page_id'])) {
            $pageId = (int) $_GET['page_id'];

            $db = new SQLite3('../data/db-cosmodrome.db');
            $stmt = $db->prepare("SELECT titre FROM pages WHERE id = :id");
            $stmt->bindValue(':id', $pageId, SQLITE3_INTEGER);
            $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

            $titrePage = $result['titre'] ?? '';

            if (strtolower($titrePage) === 'blog') {
                $posts = BDD::getBlogPosts();
            
                if (!empty($posts)) {
                    foreach ($posts as $post) {
                        echo "<div class='blog-post'>";
                        echo "<h3>" . htmlspecialchars($post['title']) . "</h3>";
                
                        if (!empty($post['image'])) {
                            echo "<img src='" . htmlspecialchars($post['image']) . "' alt='Image du blog'>";
                        }
                
                        echo "<p>" . nl2br(htmlspecialchars($post['message'])) . "</p>";
                        echo "</div><br><br>";
                    }
                } else {
                    echo "<p>Aucun article pour le moment.</p>";
                }                
            
            } else {
            
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
            }
        }
        ?>
    </div>
</body>
</html>
