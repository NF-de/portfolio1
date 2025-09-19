<?php
namespace Model;

use SQLite3;

class BDD
{
    private static string $cheminDeLaBDD = '../data/db-cosmodrome.db';

    static public function getPagesHierarchy(): array
    {
        $bdd = new SQLite3(BDD::$cheminDeLaBDD);
        $pages = array();

        $requete = "SELECT * FROM pages ORDER BY order_page_parent ASC, order_page_enfant ASC";
        $resultats = $bdd->query($requete);

        if ($resultats) {
            while ($res = $resultats->fetchArray(SQLITE3_ASSOC)) {
                $page = new \Page();
                $page->setId($res['id']);
                $page->setTitre($res['titre']);
                $page->setIdParent($res['id_parent']);
                $page->setOrderPageParent($res['order_page_parent'] ?? 0);
                $page->setOrderPageEnfant($res['order_page_enfant'] ?? 0);
                $pages[] = $page;
            }
        }

        return $pages;
    }

    public static function ajouterPage(string $titre, ?int $parentId = null): int
    {
        $db = new SQLite3(self::$cheminDeLaBDD);

        $requete = "INSERT INTO pages (titre, id_parent) VALUES (:titre, :parent_id)";
        $stmt = $db->prepare($requete);

        $stmt->bindValue(':titre', $titre, SQLITE3_TEXT);
        if ($parentId !== null) {
            $stmt->bindValue(':parent_id', $parentId, SQLITE3_INTEGER);
        } else {
            $stmt->bindValue(':parent_id', null, SQLITE3_NULL);
        }

        $stmt->execute();
        return $db->lastInsertRowID();
    }



    public static function ajouterContenu(array $data): void
    {
        $db = new SQLite3(self::$cheminDeLaBDD);

        $requete = "INSERT INTO contenu (page_id, titre, paragraphe, map_url, images)
                    VALUES (:page_id, :titre, :paragraphe, :map_url, :images)";
        $stmt = $db->prepare($requete);

        $stmt->bindValue(':page_id', $data['page_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':titre', $data['titre'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':paragraphe', $data['paragraphe'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':map_url', $data['map_url'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':images', $data['images'] ?? '', SQLITE3_TEXT);

        $stmt->execute();
    }

    static public function authenticateUser(string $username, string $password): bool
    {
        $bdd = new SQLite3(BDD::$cheminDeLaBDD);
    
        $requete = $bdd->prepare('SELECT * FROM login WHERE username = :username');
        $requete->bindValue(':username', $username, SQLITE3_TEXT);
        $resultats = $requete->execute();
    
        if ($resultats && $user = $resultats->fetchArray(SQLITE3_ASSOC)) {
          
            return password_verify($password, $user['password']);
        }
    
        return false;
    }
    



    static public function getContenuByPageId($pageId): array
    {
        $bdd = new SQLite3(BDD::$cheminDeLaBDD);
        $contenus = array();

        $requete = $bdd->prepare('SELECT * FROM contenu WHERE page_id = :pageId');
        $requete->bindValue(':pageId', $pageId, SQLITE3_INTEGER);
        $resultats = $requete->execute();

        if ($resultats) {
            while ($res = $resultats->fetchArray(SQLITE3_ASSOC)) {
                $contenu = new \Contenu();
                $contenu->setId($res['id']);
                $contenu->setTitre($res['titre']);
                $contenu->setParagraphe($res['paragraphe']);
                $contenu->setImages($res['images']);
                $contenu->setPageId($res['page_id']);

                if (isset($res['map_url']) && !empty($res['map_url'])) {
                    $contenu->setMapUrl($res['map_url']);
                }

                $contenus[] = $contenu;
            }
        }

        return $contenus;
    }

    // Fonction pour organiser les pages par parent
    static public function buildHierarchy(array $pages, $parentId = null): array
    {
        $hierarchy = array();

        foreach ($pages as $page) {
            if ($page->getIdParent() == $parentId) {
                $children = self::buildHierarchy($pages, $page->getId());

                usort($children, function ($a, $b) {
                    return $a->getOrderPageEnfant() <=> $b->getOrderPageEnfant();
                });

                if (!empty($children)) {
                    $page->setChildren($children);
                }

                $hierarchy[] = $page;
            }
        }

        return $hierarchy;
    }


    static public function displayPages(array $pages): string
    {
        $html = '';
        foreach ($pages as $page) {
            $html .= '<div class="menu-item">';
            $html .= '<a href="#" class="page-link" data-id="' . $page->getId() . '">' . htmlspecialchars($page->getTitre()) . '</a>';

            $children = $page->getChildren();
            if (!empty($children)) {
                $html .= '<div class="dropdown">';
                $html .= self::displayPages($children);
                $html .= '</div>';
            }

            $html .= '</div>';
        }
        return $html;
    }
}
?>