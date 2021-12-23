<?php

use Auth\Controller\Authentication;

$auth = new Authentication();
$loggedIn = false;
if($auth->isLoggedIn()) {
    $loggedIn = true;
}
?>

<nav>
    <ul>
        <?php if(!$loggedIn): ?>
            <li>
            <a href="register.php">ENREGISTRER</a>
            </li>
            <li>
                <a href="login.php">CONNEXION</a>
            </li>
        <?php else: ?>
            <li>
            <a href="index.php">PROFIL</a>
            </li>
            <li>
                <a href="logout.php">DECONNEXION</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>