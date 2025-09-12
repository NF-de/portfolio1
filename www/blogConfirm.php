<?php
require_once('../model/BDD.php');
require_once('../model/Classes.php');

use Model\BDD;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $imagePath = null;

    // Vérifie et déplace l'image si elle est envoyée
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = 'image/'; // Dossier image/
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = 'image/' . $filename; // Chemin relatif pour affichage
        } else {
            echo "Erreur lors de l’upload de l’image.";
            exit;
        }
    }

    if ($title && $message) {
        $blogpostData = [
            'title' => $title,
            'message' => $message,
            'image' => $imagePath
        ];

        $blogpostInserted = BDD::insertMessage($blogpostData);

        if ($blogpostInserted) {
            echo "OK";
        } else {
            echo "Erreur lors de l’enregistrement en base.";
        }
    } else {
        echo "Veuillez remplir tous les champs.";
    }
}
?>
