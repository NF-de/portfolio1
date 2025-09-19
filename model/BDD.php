<?php
namespace Model;

use PDO;
use PDOException;
use Exception;

class BDD
{
    private static ?PDO $instance = null;
    private static string $cheminDeLaBDD = '../data/db-cosmodrome.db';

    // Connexion singleton
    private static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO('sqlite:' . self::$cheminDeLaBDD);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new Exception("Erreur de connexion à la BDD : " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    // Récupérer toutes les pages
    public static function getPagesHierarchy(): array
    {
        $db = self::getConnection();
        $pages = [];

        $sql = "SELECT * FROM pages ORDER BY order_page_parent ASC, order_page_enfant ASC";
        foreach ($db->query($sql) as $res) {
            $page = new \Page();
            $page->setId($res['id']);
            $page->setTitre($res['titre']);
            $page->setIdParent($res['id_parent']);
            $page->setOrderPageParent($res['order_page_parent'] ?? 0);
            $page->setOrderPageEnfant($res['order_page_enfant'] ?? 0);
            $pages[] = $page;
        }

        return $pages;
    }

    // Ajouter une page
    public static function ajouterPage(string $titre, ?int $parentId = null): int
    {
        $db = self::getConnection();
        $sql = "INSERT INTO pages (titre, id_parent) VALUES (:titre, :parent_id)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':titre', $titre, PDO::PARAM_STR);
        $stmt->bindValue(':parent_id', $parentId, $parentId !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->execute();

        return (int)$db->lastInsertId();
    }

    // Ajouter du contenu
    public static function ajouterContenu(array $data): void
    {
        $db = self::getConnection();
        $sql = "INSERT INTO contenu (page_id, titre, paragraphe, map_url, images)
                VALUES (:page_id, :titre, :paragraphe, :map_url, :images)";
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':page_id', $data['page_id'], PDO::PARAM_INT);
        $stmt->bindValue(':titre', $data['titre'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':paragraphe', $data['paragraphe'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':map_url', $data['map_url'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':images', $data['images'] ?? '', PDO::PARAM_STR);

        $stmt->execute();
    }

    // Authentification
    public static function authenticateUser(string $username, string $password): bool
    {
        $db = self::getConnection();
        $sql = "SELECT password FROM login WHERE username = :username LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch();
        return $user ? password_verify($password, $user['password']) : false;
    }

    // Contenu par page
    public static function getContenuByPageId(int $pageId): array
    {
        $db = self::getConnection();
        $sql = "SELECT * FROM contenu WHERE page_id = :page_id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        $stmt->execute();

        $contenus = [];
        while ($res = $stmt->fetch()) {
            $contenu = new \Contenu();
            $contenu->setId($res['id']);
            $contenu->setTitre($res['titre']);
            $contenu->setParagraphe($res['paragraphe']);
            $contenu->setImages($res['images']);
            $contenu->setPageId($res['page_id']);
            if (!empty($res['map_url'])) {
                $contenu->setMapUrl($res['map_url']);
            }
            $contenus[] = $contenu;
        }

        return $contenus;
    }

    // Construire hiérarchie des pages
    public static function buildHierarchy(array $pages, $parentId = null): array
    {
        $hierarchy = [];
        foreach ($pages as $page) {
            if ($page->getIdParent() == $parentId) {
                $children = self::buildHierarchy($pages, $page->getId());
                usort($children, fn($a, $b) => $a->getOrderPageEnfant() <=> $b->getOrderPageEnfant());
                if (!empty($children)) {
                    $page->setChildren($children);
                }
                $hierarchy[] = $page;
            }
        }
        return $hierarchy;
    }

    // Affichage HTML des pages
    public static function displayPages(array $pages, bool $withButtons = false): string
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

        if ($withButtons) {
            $html .= ' <a href="modifier.php?id=' . $page->getId() . '" class="btn-modifier">Modifier</a>';
            $html .= ' <a href="supprimer.php?id=' . $page->getId() . '" class="btn-supprimer" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cette page ?\');">Supprimer</a>';
        }

        if ($hasChildren) {
            $html .= '<div class="children">';
            $html .= self::displayPages($page->getChildren(), $withButtons);
            $html .= '</div>';
        }

        $html .= '</div>';
    }

    return $html;
}


    // Récupérer les pages parents
    public static function getParentPages(): array
    {
        $db = self::getConnection();
        $sql = "SELECT * FROM pages WHERE id_parent IS NULL OR id_parent = 0 ORDER BY order_page_parent ASC";
        $stmt = $db->query($sql);

        $parents = [];
        foreach ($stmt as $res) {
            $page = new \Page();
            $page->setId($res['id']);
            $page->setTitre($res['titre']);
            $page->setIdParent($res['id_parent']);
            $page->setOrderPageParent($res['order_page_parent'] ?? 0);
            $page->setOrderPageEnfant($res['order_page_enfant'] ?? 0);
            $parents[] = $page;
        }
        return $parents;
    }
    public static function modifierPage(int $id, string $titre): void
{
    $db = self::getConnection();
    $stmt = $db->prepare('UPDATE pages SET titre = :titre WHERE id = :id');
    $stmt->bindValue(':titre', $titre, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}

public static function modifierContenu(array $paragraphes): void
{
    $db = self::getConnection();
    $stmt = $db->prepare('UPDATE contenu SET paragraphe = :paragraphe WHERE id = :id');

    foreach ($paragraphes as $id => $texte) {
        $stmt->bindValue(':paragraphe', $texte, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}

public static function getPageById(int $id)
{
    $pages = self::getPagesHierarchy();
    $hierarchy = self::buildHierarchy($pages);

    $finder = function($pages, $id) use (&$finder) {
        foreach ($pages as $page) {
            if ($page->getId() === $id) return $page;
            $children = $page->getChildren();
            if ($children) {
                $found = $finder($children, $id);
                if ($found) return $found;
            }
        }
        return null;
    };

    return $finder($hierarchy, $id);
}

}
?>
