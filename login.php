<?php
$bdd = new PDO('mysql:host=localhost;dbname=gestion_biblio','root','');
session_start();
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = $_POST['mail'];
    $mdp = $_POST['mdp'];

    if (!empty($mail) && !empty($mdp)) {
        $req = $bdd->prepare('SELECT * FROM utilisateur WHERE mail = ? AND mdp = ?');
        $req->execute([$mail, $mdp]);

        if ($req->rowCount() < 1) {
            $error_msg = "Email ou mot de passe incorrect !";
        } else {
            $rep = $req->fetch();
            $_SESSION['id_utilisateur'] = $rep['id_utilisateur'];
            header('Location: main.php');
            exit();
        }
    } else {
        $error_msg = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Connexion</title>
</head>
<body>
<section class="page_login">
    <form action="traitement.php" method="post" class="login">
        
        <input type="text" id="mail" name="mail" placeholder="Entrez votre email..." required class="input"><br>

        <input type="password" id="mdp" name="mdp" placeholder="Entrez votre mot de passe..." required class="input"><br>

        <input type="submit" value="Valider" name="ok" class="btn">

        <button class="btn"><a href="incript.php">S'inscrire</a></button>

        <?php if (!empty($error_msg)) : ?>
            <p class="error-message"><?= $error_msg ?></p>
        <?php endif; ?>
    </form>

    <div class="icn_user">
        <img src="img/photo.png" alt="Icône utilisateur">
    </div>
</section>
</body>
</html>
