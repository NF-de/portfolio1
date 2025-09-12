<?php
ini_set('session.gc_maxlifetime', 300);
session_set_cookie_params(0);
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: log_admin.php");
    exit;
}

require_once '../model/BDD.php';

$id = (int) ($_GET['id'] ?? 0);
$db = new SQLite3('../data/db-cosmodrome.db');

$stmt = $db->prepare('SELECT * FROM blogpost WHERE id = :id');
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $message = $_POST['message'] ?? '';
    $imagePath = $result['image'];


    if (!empty($_FILES['image']['name'])) {
        $uploadDir = 'image/';
        $filename = basename($_FILES['image']['name']);
        $targetFile = $uploadDir . time() . '_' . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        } else {
            echo "Erreur lors du téléchargement de l'image.";
        }
    }

    $update = $db->prepare('UPDATE blogpost SET title = :title, message = :message, image = :image WHERE id = :id');
    $update->bindValue(':title', $title, SQLITE3_TEXT);
    $update->bindValue(':message', $message, SQLITE3_TEXT);
    $update->bindValue(':image', $imagePath, SQLITE3_TEXT);
    $update->bindValue(':id', $id, SQLITE3_INTEGER);
    $update->execute();

    header('Location: backoffice.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Modifier le message</title>
    <link rel="stylesheet" href="admin.css">
</head>

<body>

    <div class="container">
        <h2>Modifier le message</h2>
        <form method="post" enctype="multipart/form-data">
            <label for="title">Titre :</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($result['title']) ?>">

            <label for="message">Message :</label>
            <textarea id="message" name="message" rows="6"><?= htmlspecialchars($result['message']) ?></textarea>

            <?php if (!empty($result['image'])): ?>
                <p>Image actuelle :</p>
                <img src="<?= htmlspecialchars($result['image']) ?>" alt="Image du message" style="max-width: 200px;">
            <?php endif; ?>
            <br>
            <br>
            <label for="image">Changer l'image :</label>
            
            <input type="file" name="image" accept="image/*">
            <br>
            <br>
            <button type="submit">Enregistrer</button>
        </form>
    </div>

</body>

</html>
