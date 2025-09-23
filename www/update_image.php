<?php
ini_set('session.gc_maxlifetime', 300);
session_set_cookie_params(0);
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: log_admin.php");
    exit;
}

require_once '../model/BDD.php';
use Model\BDD;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contenu_id']) && isset($_FILES['nouvelle_image'])) {
    $contenuId = intval($_POST['contenu_id']);
    $file = $_FILES['nouvelle_image'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        // Dossier "image" (au même niveau que backoffice et model)
        $uploadDir = '../www/image/';  
        $publicDir = '';     // chemin public pour <img src>

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid('img_') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $serverPath = $uploadDir . $fileName; // chemin serveur
        $publicPath = $publicDir . $fileName; // chemin pour BDD et navigateur

        if (move_uploaded_file($file['tmp_name'], $serverPath)) {
            // Mise à jour en BDD
            BDD::updateImage($contenuId, $publicPath);

            header("Location: modifier.php?id=" . intval($_GET['id']));
            exit;
        }
    }
}
die("Erreur lors du téléchargement de l'image.");
