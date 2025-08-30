<?php
$erreur = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bdd = new PDO('mysql:host=localhost;dbname=gestion_biblio', 'root', '');

    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $mail = $_POST['mail'];
    $mdp = $_POST['mdp'];


    $check = $bdd->prepare("SELECT * FROM utilisateur WHERE mail = ?");
    $check->execute([$mail]);
    if ($check->rowCount() > 0) {
        $erreur = "⚠️ Ce mail est déjà utilisé.";
    } else {
   
        $stmt = $bdd->prepare("INSERT INTO utilisateur(nom, prenom, mail, mdp) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nom, $prenom, $mail, $mdp])) {
            $success = true;
        } else {
            $erreur = "❌ Une erreur s'est produite lors de l'inscription.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Inscription</title> 
</head>
<body>
    <div class="page_login">

        <form action="incript.php" method="post" class="incript">
        <?php if (!empty($erreur)): ?>
            <div class="erreur"><?= $erreur ?></div>
        <?php endif; ?>

            <label for="nom"></label>
            <input type="text" id="nom" name="nom" placeholder="Entrez votre nom..." required class="input"><br>

            <label for="prenom"></label>
            <input type="text" id="prenom" name="prenom" placeholder="Entrez votre prénom..." required class="input"><br>

            <label for="mail"></label>
            <input type="email" id="mail" name="mail" placeholder="Entrez votre mail..." required class="input"><br>

            <label for="mdp"></label>
            <input type="password" id="mdp" name="mdp" placeholder="Entrez votre mot de passe..." required class="input"><br>

            <input type="submit" value="Valider" name="ok" class="btn">
            <button class="btn"><a href="login.php">Se connecter</a></button>
        </form>
    </div>

<?php if (!empty($success)): ?>
    <div id="popup" class="popup">
        <div class="popup-box">
            <img src="img/image/verifier.png" alt="">
            <p>Inscription réussie.</p><br><br>
            <a href='login.php'>Connectez-vous ici</a>
            <button id="closePopup">Fermer</button>
        </div>
    </div>

    <style>
        .popup {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .popup-box {
            background: white;
            padding: 2rem 3rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 0 10px #00000088;
            position: relative;
            max-width: 300px;
        }
        .popup-box img {
            width: 80px;
            margin-bottom: 1rem;
        }
        .popup-box p {
            font-size: 20px;
            font-weight: 600;
        }
        .popup-box a {
            display: inline-block;
            margin-top: 1rem;
            background-color: #CF2160;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
        }
        #closePopup {
            margin-top: 1rem;
            padding: 5px 10px;
            border: none;
            background: #999;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>

    <script>
        document.getElementById('closePopup').addEventListener('click', function() {
            document.getElementById('popup').style.display = 'none';
        });
    </script>
<?php endif; ?>

</body>
</html>
