<?php

require_once '../model/BDD.php';
require_once '../model/Classes.php';

$bdd = new SQLite3('../data/db-cosmodrome.db');


$username = 'admin';
$motDePasseClair = 'admin';

$hashedPassword = password_hash($motDePasseClair, PASSWORD_DEFAULT);

$requete = $bdd->prepare('UPDATE login SET password = :password WHERE username = :username');
$requete->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
$requete->bindValue(':username', $username, SQLITE3_TEXT);

$requete->execute();

echo "Mot de passe haché et mis à jour pour l'utilisateur $username.\n";
