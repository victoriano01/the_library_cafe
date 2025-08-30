<?php
include('traitement.php');


header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$id_utilisateur = $_SESSION['id'];

// Récupérer les infos utilisateur
$stmt_user = $bdd->prepare("SELECT nom, prenom, mail, mdp FROM utilisateur WHERE id_utilisateur = ?");
$stmt_user->execute([$id_utilisateur]);
$utilisateur = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Récupérer les nouveaux livres
$livres = $bdd->query("
    SELECT livre.*, auteur.nom_auteur FROM livre 
    JOIN auteur ON livre.id_auteur = auteur.id_auteur 
    WHERE livre.is_new = 1 
    ORDER BY livre.id_livre DESC
");

// Gérer les notifications
$notifs = $bdd->query('SELECT COUNT(*) AS nombre FROM livre WHERE is_new = 1');
$notif = $notifs->fetch(PDO::FETCH_ASSOC);
$notf = ($notif['nombre'] < 1) ? '' : '<a href="nouv.php" class="notif">
    <img src="img/image/notif.png" alt="">' . $notif['nombre'] . '</a>';

// Récupérer les emprunts utilisateur
$stmt = $bdd->prepare("
SELECT e.id_emprunt, l.titre, l.photo, a.nom_auteur, e.date_emprunt, e.statut
FROM emprunts e
JOIN livre l ON e.id_livre = l.id_livre
JOIN auteur a ON l.id_auteur = a.id_auteur
WHERE e.id_utilisateur = ? AND e.statut != 'annule'
ORDER BY e.date_emprunt DESC
");
$stmt->execute([$id_utilisateur]);
$emprunts = $stmt->fetchAll();
$panier_count = count($emprunts);

// Gérer le formulaire profil
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $mail = $_POST['mail'] ?? '';
    $mdp = $_POST['mdp'] ?? '';

    // Optionnel : $mdp = password_hash($mdp, PASSWORD_DEFAULT);

    $stmt = $bdd->prepare("UPDATE utilisateur SET nom = ?, prenom = ?, mail = ?, mdp = ? WHERE id_utilisateur = ?");
    $stmt->execute([$nom, $prenom, $mail, $mdp, $id_utilisateur]);

    $success = "Mise à jour réussie";
    // Mise à jour des infos locales
    $utilisateur = ['nom' => $nom, 'prenom' => $prenom, 'mail' => $mail, 'mdp' => $mdp];
}




// Récupère les infos utilisateur y compris la photo
$stmt = $bdd->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$_SESSION['id']]);
$utilisateur = $stmt->fetch();

$defaultPhoto = 'img/image/utilisateur.png';
$photoPath = !empty($utilisateur['photo']) ? 'img/image/' . $utilisateur['photo'] : $defaultPhoto;

?>





<?php if (!empty($success)): ?>
    <div class="success-message">
        <div class="box">
            <img src="img/image/verifier.png" alt="">
            <p><?= htmlspecialchars($success) ?></p><br><br>
            <a href='profil.php'>Retour au profil</a>
        </div>
    </div>
<?php endif; ?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/gestion_de_biblio/panier.css">
    <link rel="stylesheet" href="/gestion_de_biblio/main.css">
    <link rel="stylesheet" href="/gestion_de_biblio/profil.css">
    <title>Profil</title>
    <style>
    .profil_page_alt {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 999;
    background-color: rgba(0, 0, 0, 0.4); 
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}
    .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    z-index: 1000;
    display: flex;
    justify-content: center;
    align-items: center;
}


.modal-content {
    background-color: white;
    border-radius: 10px;
    padding: 30px;
    width: 400px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    animation: fadeIn 0.3s ease-in-out;
    position: relative;
}

@keyframes fadeIn {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.close-btn {
    background-color: crimson;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
}




.success-message {
    position: fixed;
    top: 30%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #d4edda;
    padding: 20px 30px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.2);
    z-index: 1000;
}

.success-message .box {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}


    </style>
</head>
<body>
<header>
    <div class="barnav">
        <img src="img/logo.png" alt="logo" style="width:150px; height:150px; object-fit:contain; display:block;">
        <div class="nav">
            <ul>
                <li><a href="main.php">Accueil</a></li>
                <div class="livre_not">
                    <li><a href="livre.php" class="livre_notif">Livre </a></li>
                </div>
                <li><a href="emprunt.php">Emprunts</a></li>
                <select id="menu" onchange="window.location.href = this.value;">
                    <option value="parametre">Paramètre</option>
                    <option value="histo.php">Historique</option>
                    <option value="deco.php">Déconnexion</option>
                </select>
            </ul>
        </div>

        <div class="notif">
            <?= $notf ?>
        </div>

        <section class="left">
            <div class="panier">
                <a href="panier.php">
                    <img src="img/image/cart.png" alt="" style="width: 35px !important; height: auto;">
                    <div class="notification"><?= $panier_count ?></div>
                </a>
            </div>
            <div class="profil">
                    <a href="profil.php">
                        <img src="<?= htmlspecialchars($photoPath) ?>" alt="Photo de profil" style="width:35px; height:35px; border-radius:50%;">
                    </a>
                </div>
        </section>
    </div>
</header>

<section class="profil_page">
<?php
$success = '';
if (isset($_SESSION['profil_update_success']) && $_SESSION['profil_update_success']) {
    $success = "Mise à jour du profil réussie !";
    unset($_SESSION['profil_update_success']);
}
?> 

<?php if (!empty($success)): ?>
<div class="success-message">
    <div class="box">
        <img src="img/image/verifier.png" alt="" width="30px">
        <p style="font-size: 20px; margin: 0 10px;"><?= htmlspecialchars($success) ?></p>
        <a href='profil.php' style="margin-left: 10px; font-weight: bold; text-decoration: none;">X</a>
    </div>
</div>
<?php endif; ?>

    <h1>Votre profil</h1>   
    <div class="profils">


        <div class="altern">
        <form action="modifier_profil.php" method="POST">
            <label for=""> Nom</label><br>
            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($utilisateur['nom']) ?>" readonly class="no-focus"><br>

            <label for=""> Prénom</label><br>
            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($utilisateur['prenom']) ?>" readonly class="no-focus"><br>

            <label for=""> Email</label><br>
            <input type="text" id="mail" name="mail" value="<?= htmlspecialchars($utilisateur['mail']) ?>" readonly class="no-focus"><br>

            <label for=""> Mot de passe</label><br>
            <input type="password" id="mdp" name="mdp" value=" <?= htmlspecialchars($utilisateur['mdp']) ?> " readonly class="no-focus">

            <div class="btn_alt">
                <button type="button" onclick="openModal()" class="alte">Modifier   </button>
            </div>
        </form>
        </div>

        <div class="prof_pht">
        <form action="modifier_profil.php" method="POST" enctype="multipart/form-data" id="photoForm">
             <label for="photoInput">
            <img src="img/image/<?= htmlspecialchars($utilisateur['photo'] ?? 'utilisateur.png') ?>" 
             alt="Photo de profil" id="profilePic" 
             style="width:320px; height:320px; border-radius:50%; cursor:pointer; margin:2rem;">
            </label>
             <input type="file" id="photoInput" name="photo" style="display:none;" onchange="document.getElementById('photoForm').submit();">

        </form>


            <div class="icn">
                <img src="img/image/facebook.png" alt="">
                <img src="img/image/instagram.png" alt="">
                <img src="img/image/twitter.png" alt="">
            </div>
        </div>
    </div>

    <img src="img/blom.png" alt="" class="blom">
    <img src="img/blom.png" alt="" class="bloma">
</section>



<!-- ✅ POPUP MODALE -->
<div class="modal-overlay" id="modalOverlay" style="display:none;">
    <div class="modal-content">
        <section class="profil_page_alt">
            <div class="profils_alt">
                <div class="alterns">
                    <form action="modifier_profil.php" method="POST">
                        <label for="modal_nom">Nom</label><br>
                        <input type="text" id="modal_nom" name="nom"><br>

                        <label for="modal_prenom">Prénom</label><br>
                        <input type="text" id="modal_prenom" name="prenom"><br>

                        <label for="modal_mail">Email</label><br>
                        <input type="text" id="modal_mail" name="mail"><br>

                        <label for="modal_mdp">Mot de passe</label><br>
                        <input type="password" id="modal_mdp" name="mdp" placeholder="8 caractères minimum">

                        <div class="btn_alts">
                            <button type="submit" name="alt" class="alte">Sauvegarder</button>
                        </div>
                    </form>
                    <button onclick="closeModal()" class="close-btn" style="margin-top:10px;">Fermer</button>
                </div>
            </div>       
        </section>
    </div>
</div>
<script>
function openModal() {
    document.getElementById("modalOverlay").style.display = "block";

    // Préremplir les champs du popup avec les valeurs actuelles du profil
    document.getElementById("modal_nom").value = document.getElementById("nom").value;
    document.getElementById("modal_prenom").value = document.getElementById("prenom").value;
    document.getElementById("modal_mail").value = document.getElementById("mail").value;
    document.getElementById("modal_mdp").value = document.getElementById("mdp").value;
}

function closeModal() {
    document.getElementById("modalOverlay").style.display = "none";
}

window.onclick = function(event) {
    const modal = document.getElementById("modalOverlay");
    if (event.target === modal) {
        modal.style.display = "none";
    }
}
</script>

</body>
</html>
