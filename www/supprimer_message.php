<?php
require_once '../model/BDD.php';

$id = (int) ($_GET['id'] ?? 0);
$db = new SQLite3('../data/db-cosmodrome.db');

$stmt = $db->prepare('DELETE FROM blogpost WHERE id = :id');
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$stmt->execute();

header('Location: backoffice.php');
exit;
