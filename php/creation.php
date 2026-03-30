<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../html/connexion.html?status=error&message=' . urlencode('Veuillez vous connecter.'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creation</title>
    <link rel="stylesheet" href="../css/styles.css">

</head>
<header>
     <div class="logo"><strong>MonSite</strong></div><br>
    <ul class="Barre">
        <li><a href="#"><input type="text" placeholder="Rechercher..." class="search-input"><button type="submit" class="Brecherche">Rechercher</button></a></li> 
        <li class="push-right">
            <div class="dropdown">
                <button class="dropbtn">Mon Compte ▼</button>
                <div class="dropdown-content">
                    <a href="#">Mes créations</a>
                    <a href="#">Favoris</a>
                    <a href="menu.php">Menu</a>
                    <hr>
                    <a href="../html/page_accueil.html" class="deco">Déconnexion</a>
                </div>
            </div>
        </li>
    </ul>
</header>
<body><div class="zone">

    <div id="depot-img" class="depot">
        <span id="texte-img">Glissez et déposez une image ici</span>
        <input type="file" id="input-img" accept="image/*" hidden>
    </div>

    <div id="depot-text" class="depot">
        <form action="" method="post">
            <label for="description">Votre Description :</label><br>
            <textarea id="description" name="description"></textarea>
        </form>
    </div>
   
        </div>
    </div>
    <script src="../js/creation.js"></script>
</body>
</html>