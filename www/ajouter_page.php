<?php
require_once '../model/BDD.php';
require_once '../model/Classes.php';

use Model\BDD;

$titre = $_POST['titre'] ?? '';
$parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;

$pageId = BDD::ajouterPage($titre, $parentId);

$contenuTitre = $_POST['contenu_titre'] ?? '';
$contenuParagraphe = $_POST['contenu_paragraphe'] ?? '';
$mapUrl = $_POST['map_url'] ?? '';
$imageFilename = null;


if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../www/image/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $tmpName = $_FILES['image']['tmp_name'];
    $originalName = basename($_FILES['image']['name']);
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);

    $imageFilename = 'page_' . $pageId . '_' . time() . '.' . $extension;

    $destination = $uploadDir . $imageFilename;

    if (!move_uploaded_file($tmpName, $destination)) {
    
        die('Erreur lors du téléchargement de l\'image.');
    }
}


if (!empty($contenuTitre) || !empty($contenuParagraphe) || !empty($mapUrl) || $imageFilename !== null) {
    BDD::ajouterContenu([
        'page_id' => $pageId,
        'titre' => $contenuTitre,
        'paragraphe' => $contenuParagraphe,
        'map_url' => $mapUrl,
        'images' => $imageFilename
    ]);
}

header('Location: backoffice.php');
exit;
