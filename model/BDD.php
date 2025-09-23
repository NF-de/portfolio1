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
    public static function ajouterPage(string $titre, ?int $parentId = null): int
    {
        $db = self::getConnection();
        $sql = "INSERT INTO pages (titre, id_parent) VALUES (:titre, :parent_id)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':titre', $titre, PDO::PARAM_STR);
        $stmt->bindValue(':parent_id', $parentId, $parentId !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->execute();

        return (int) $db->lastInsertId();
    }
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
    public static function displayPages(array $pages): string
    {
        $html = '';
        foreach ($pages as $page) {
            $html .= '<div class="menu-item">';
            $html .= '<a href="#" class="page-link" data-id="' . (int) $page->getId() . '">' . htmlspecialchars($page->getTitre()) . '</a>';
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
    public static function modifierPage(int $id, string $nouveauTitre): void
    {
        $db = self::getConnection();
        $stmt = $db->prepare("UPDATE pages SET titre = :titre WHERE id = :id");
        $stmt->bindValue(":titre", $nouveauTitre, PDO::PARAM_STR);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public static function modifierContenu(array $paragraphes): void
    {
        $db = self::getConnection();
        foreach ($paragraphes as $contenuId => $texte) {
            $stmt = $db->prepare("UPDATE contenu SET paragraphe = :paragraphe WHERE id = :id");
            $stmt->bindValue(":paragraphe", $texte, PDO::PARAM_STR);
            $stmt->bindValue(":id", $contenuId, PDO::PARAM_INT);
            $stmt->execute();
        }

    }
    public static function getPageTitreById(int $id): ?string
    {
        $db = self::getConnection();
        $sql = "SELECT titre FROM pages WHERE id = :id LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row ? $row['titre'] : null;
    }
    public static function supprimerPage(int $pageId): void
    {
        $db = self::getConnection();

        // Supprimer le contenu lié
        $stmt1 = $db->prepare("DELETE FROM contenu WHERE page_id = :pageId");
        $stmt1->bindValue(":pageId", $pageId, PDO::PARAM_INT);
        $stmt1->execute();

        // Supprimer la page elle-même
        $stmt2 = $db->prepare("DELETE FROM pages WHERE id = :pageId");
        $stmt2->bindValue(":pageId", $pageId, PDO::PARAM_INT);
        $stmt2->execute();
    }
    public static function getImagesByPageId(int $pageId): array
    {
        $db = self::getConnection();
        $sql = "SELECT id, images FROM contenu WHERE page_id = :page_id AND images IS NOT NULL AND images != ''";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        $stmt->execute();

        $images = [];
        while ($res = $stmt->fetch()) {
            $images[] = [
                'id' => $res['id'],
                'path' => $res['images']
            ];
        }
        return $images;
    }
    public static function updateImage(int $contenuId, string $imagePath): void
    {
        $db = self::getConnection();
        $stmt = $db->prepare("UPDATE contenu SET images = :image WHERE id = :id");
        $stmt->bindValue(':image', $imagePath, PDO::PARAM_STR);
        $stmt->bindValue(':id', $contenuId, PDO::PARAM_INT);
        $stmt->execute();
    }



} ?>