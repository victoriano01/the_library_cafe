<?php
$bdd = new PDO('mysql:host=localhost;dbname=gestion_biblio', 'root', '');
include('traitement.php');

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$utilisateur_connecte = isset($_SESSION['id']);
$id = $_SESSION['id'];

// Récupère les infos utilisateur
$stmt = $bdd->prepare("SELECT photo FROM utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// Définit la photo (par défaut si vide)
$photoPath = !empty($user['photo']) ? 'img/image/' . $user['photo'] : 'img/image/utilisateur.png';


// Gérer les notifications
$id_utilisateur = $_SESSION['id'];
$notifs = $bdd->query('SELECT COUNT(*) AS nombre FROM livre WHERE is_new = 1');
$notif = $notifs->fetch();
$notf = ($notif['nombre'] < 1) ? '' : '<a href="nouv.php" class="notif">
    <img src="img/image/notif.png" alt="">' . $notif['nombre'] . '</a>';

// Récupérer les emprunts de l’utilisateur
$stmt = $bdd->prepare("
    SELECT e.*, l.titre, l.photo, a.nom_auteur
    FROM emprunts e
    JOIN livre l ON e.id_livre = l.id_livre
    JOIN auteur a ON l.id_auteur = a.id_auteur
    WHERE e.id_utilisateur = ? AND e.statut = 'en cours'
    ORDER BY e.date_emprunt DESC
");
$stmt->execute([$id_utilisateur]);
$emprunts = $stmt->fetchAll();
$panier_count = count($emprunts);



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_livre'], $_POST['date_retour'])) {
        $id_livre = intval($_POST['id_livre']);
        $date_emprunt = date('Y-m-d');
        $date_retour_prevue = $_POST['date_retour'];
        $id_utilisateur = $_SESSION['id'];


        $insert = $bdd->prepare("INSERT INTO emprunts (id_livre, id_utilisateur, date_emprunt, date_retour_prevue, date_retour_effective) VALUES (?, ?, ?, ?, NULL)");
        $insert->execute([$id_livre, $id_utilisateur, $date_emprunt, $date_retour_prevue]);

        header("Location: panier.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/gestion_de_biblio/panier.css">
    <link rel="stylesheet" href="/gestion_de_biblio/main.css">
    <title>Emprunt</title>
</head>
<body>
<header>
    <div class="barnav">
        <img src="img/logo.png" alt="logo" style="width:150px; height:150px; object-fit:contain; display:block;">
        <div class="nav">
            <ul>
                <li><a href="main.php" >Acceuil</a></li>
                <div class="livre_not">
                    <li><a href="livre.php" class="livre_notif">Livre </a></li>
                </div>
                <li><a href="emprunt.php"><span class="selec">Emprunt</span></a></li>
                <select id="menu" name="" onchange="window.location.href = this.value;">
                <option value="parametre">Paramètre</option>
                <option value="histo.php">Historique</option>
                <option value="deco.php">Déconnexion</option>
                </select>
            </ul>
        </div>
        <div class="notif">
            <a href=""><?php echo $notf; ?></a>
        </div>
        <section class="left">
                <div class="panier">
                <a href="panier.php">
                     <img src="img/image/cart.png" alt="" style="width: 35px !important; height: auto;">
                <div class="notification"><?php echo $panier_count; ?></div>
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


<?php
if (!isset($_GET['id_livre'])) {

    // Connexion à la base et récupération des emprunts
    $id_utilisateur = $_SESSION['id'];
    $stmt = $bdd->prepare("
        SELECT e.id_emprunt, l.titre, l.photo, a.nom_auteur, e.date_emprunt, e.statut
        FROM emprunts e
        JOIN livre l ON e.id_livre = l.id_livre
        JOIN auteur a ON l.id_auteur = a.id_auteur
        WHERE e.id_utilisateur = ? AND e.statut != 'annule'
        ORDER BY e.date_emprunt DESC
    ");
    $stmt->execute([$id_utilisateur]);
    $liste_emprunts = $stmt->fetchAll();
  
    
?>
    
        
    
    <section id="historique" class="main_panir emprunt" style="background-color: #A691F2; color=:white;">
    <h1>Mes emprunts – Rendre un Livre</h1>
        <div class="btn_Emprunte">  
<button class="btn_empru"><a href="livre.php#livre">Emprunter des Livres</a></button>
        <style>
            .btn_Emprunte{
                text-align: center;
            }
            .btn_empru{
                border-radius: 1px;
                margin: 1rem;
                margin-bottom: 4rem;
            font-size: 16px;
            height: 3.5rem;
            width: 15rem;
            box-shadow: 4px 4px 10px 2px rgba(124, 99, 128, 0.585);
            background-color: #cf2160;
        }
        .btn_empru a{
                color: rgb(255, 255, 255);
                text-decoration:none;
                padding: 10px;
            }
        </style>
    </div>
        <h1 style="text-align: center; color: #fff; font-size: 40px;">Rendre un livre</h1>
        <?php if (count($liste_emprunts) > 0): ?>
            <table cellpadding="10" class="tab">
                <thead>
                    <tr>
                        <th>Couverture</th>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Date d'emprunt</th>
                        <th>Statut</th>
                        <th>Rendre</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($liste_emprunts as $item): ?>
                        <tr>
                            <td><img src="img/couverture/<?php echo htmlspecialchars($item['photo']); ?>" width="90" style="margin: 1rem; border-radius: 5px;"></td>
                            <td><?php echo htmlspecialchars($item['titre']); ?></td>
                            <td><?php echo htmlspecialchars($item['nom_auteur']); ?></td>
                            <td><?php echo htmlspecialchars($item['date_emprunt']); ?></td>
                            <td><?php echo htmlspecialchars($item['statut']); ?></td>
                            <td>
                                <?php if ($item['statut'] !== 'rendu'): ?>
                                    <form method="post" action="emprunt.php" class="sup">
                                        <input type="hidden" name="rendre" value="<?php echo $item['id_emprunt']; ?>">
                                        <button type="submit" class="btn_sup" onclick="return confirm('Confirmer le retour de ce livre ?')" style="background-color:  #cf2160; color=: #ffffff;">
                                            Rendre 
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: green; font-weight: bold;">Déjà Rendu</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center;">Aucun emprunt effectué pour le moment.</p>
        <?php endif; ?>
    </section>
    <?php

    exit;
}


$id_livre = intval($_GET['id_livre']);

$stmt = $bdd->prepare("SELECT l.*,  a.nom_auteur FROM livre l JOIN auteur a ON l.id_auteur = a.id_auteur WHERE l.id_livre = ?");
$stmt->execute([$id_livre]);
$livre = $stmt->fetch();

if (!$livre) {
    echo "<p>Livre introuvable.</p>";
    exit;
}

$today = date('Y-m-d');

?>

<section class="sect_emprunt">
    <div class="box_emprunt">
        <div class="img_emprunt">
            <img src="img/couverture/<?php echo htmlspecialchars($livre['photo']); ?>"><br><br>
        </div>       

        <div class="form_emprunt">
            <p><strong>Titre :</strong> <span><?php echo htmlspecialchars($livre['titre']); ?></span></p>    
            <p><strong>Auteur :</strong><span><?php echo htmlspecialchars($livre['nom_auteur']); ?></span></p>

            <form action="" method="post">
                <input type="hidden" name="id_livre" value="<?php echo $livre['id_livre']; ?>">

                <label>Date de retour :</label>
                <input type="date" name="date_retour" min="<?php echo $today; ?>" required><br>

                <button type="submit" class="valide">Valider</button>
            </form>
        </div>
    </div>
</section>

</body>
</html>
