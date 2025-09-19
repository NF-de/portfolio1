<?php
require_once '../model/BDD.php';

use Model\BDD;

if (!isset($_GET['id'])) {
    die('ID de la page non spécifié.');
}

$pageId = intval($_GET['id']);

// Utilisation de la méthode BDD
BDD::supprimerPage($pageId);

header('Location: backoffice.php');
exit;
