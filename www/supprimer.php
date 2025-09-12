<?php
require_once '../model/BDD.php';

use Model\BDD;

if (!isset($_GET['id'])) {
    die('ID de la page non spécifié.');
}

$pageId = intval($_GET['id']);

// Suppression dans la base SQLite
$bdd = new SQLite3('../data/db-cosmodrome.db');

// Supprimer le contenu lié
$stmt1 = $bdd->prepare('DELETE FROM contenu WHERE page_id = :pageId');
$stmt1->bindValue(':pageId', $pageId, SQLITE3_INTEGER);
$stmt1->execute();

// Supprimer la page elle-même
$stmt2 = $bdd->prepare('DELETE FROM pages WHERE id = :pageId');
$stmt2->bindValue(':pageId', $pageId, SQLITE3_INTEGER);
$stmt2->execute();

header('Location: backoffice.php');
exit;
