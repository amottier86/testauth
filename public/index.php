<?php

require(__DIR__ . "/header.php"); 
$username = null;
$password = null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home page</title>
</head>
<body>
    <h1>Bienvenue dans votre espace <?= $username ?> !</h1>
    <p>Votre mot de passe crypté est : <?= $password ?></p>
    <a href="logout.php">Déconnexion</a>
</body>
</html>