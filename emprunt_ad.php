<?php


include('traitement.php');
// metre a jour le statue 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marquer_rendu'], $_POST['id_emprunt'])) {
    $id_emprunt = $_POST['id_emprunt'];
    $update = $bdd->prepare("UPDATE emprunts SET statut = 'rendu' WHERE id_emprunt = ?");
    $update->execute([$id_emprunt]);
}


// Récupérer les nouveaux livres
$livres = $bdd->query("
    SELECT livre.*, auteur.nom_auteur FROM livre 
    JOIN auteur ON livre.id_auteur = auteur.id_auteur 
    WHERE livre.is_new = 1 
    ORDER BY livre.id_livre DESC
");

// Gérer les notifications
$notifs = $bdd->query('SELECT COUNT(*) AS nombre FROM livre WHERE statut="new"');
$notif = $notifs->fetch();
$notf = ($notif['nombre'] < 1) ? '' : '<a href="nouv.php" class="notif">
    <img src="img/image/notif.png" alt="">' . $notif['nombre'] . '</a>';

// Gérer l'ajout ou la suppression dans le panier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['supprimer'])) {
        $id_suppr = $_POST['supprimer'];
        foreach ($_SESSION['panier'] as $index => $item) {
            if ($item['id_livre'] == $id_suppr) {
                unset($_SESSION['panier'][$index]);
            }
        }
        $_SESSION['panier'] = array_values($_SESSION['panier']);
    } elseif (isset($_POST['id_livre'], $_POST['titre'], $_POST['photo'], $_POST['auteur'], $_POST['date_retour'])) {
        $livre = [
            'id_livre' => $_POST['id_livre'],
            'titre' => $_POST['titre'],
            'photo' => $_POST['photo'],
            'auteur' => $_POST['auteur'],
            'date_retour' => $_POST['date_retour'],
        ];

        if (!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }

        $existe = false;
        foreach ($_SESSION['panier'] as $item) {
            if ($item['id_livre'] == $livre['id_livre']) {
                $existe = true;
                break;
            }
        }

        if (!$existe) {
            $_SESSION['panier'][] = $livre;
        }
    }
}

$panier_count = isset($_SESSION['panier']) ? count($_SESSION['panier']) : 0;

// Statistiques
$stmt = $bdd->query("
    SELECT 
        (SELECT COUNT(*) FROM livre) AS total_livres,
        (SELECT COUNT(*) FROM utilisateur) AS total_users
");
$data = $stmt->fetch();

// Récupérer le nom utilisateur en toute sécurité
$nom_utilisateur = $_POST['nom'] ?? null;
$id_utilisateur = null;

if ($nom_utilisateur) {
    $stmt = $bdd->prepare("SELECT id_utilisateur FROM utilisateur WHERE nom = ?");
    $stmt->execute([$nom_utilisateur]);
    $id_utilisateur = $stmt->fetchColumn();
}

// Emprunts récents (si utilisateur trouvé)
if ($id_utilisateur) {
    $stmt = $bdd->prepare("
    SELECT e.id_emprunt, u.nom, l.titre, e.date_emprunt, e.date_retour_prevue, e.statut
    FROM emprunts e
    JOIN livre l ON e.id_livre = l.id_livre
    JOIN utilisateur u ON e.id_utilisateur = u.id_utilisateur
    WHERE u.id_utilisateur = ?
    ORDER BY e.date_retour_prevue DESC
");


    $stmt->execute([$id_utilisateur]);
} else {
    // S’il n’y a pas d'utilisateur fourni, on récupère les 5 derniers emprunts en général
    $stmt = $bdd->query("
    SELECT e.id_emprunt, u.nom, l.titre, e.date_emprunt, e.date_retour_prevue, e.statut
    FROM emprunts e
    JOIN livre l ON e.id_livre = l.id_livre
    JOIN utilisateur u ON e.id_utilisateur = u.id_utilisateur
    ORDER BY e.date_retour_prevue DESC
    LIMIT 5
");


}

$emprunts = $stmt->fetchAll();
$reqEmprunts = $bdd->query("SELECT COUNT(*) AS total_emprunts FROM emprunts");
$nbEmprunts = $reqEmprunts->fetchColumn();



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/gestion_de_biblio/panier.css">
    <link rel="stylesheet" href="/gestion_de_biblio/main.css">
    <link rel="stylesheet" href="/gestion_de_biblio/table.css">
    <title>acceuil</title>
</head>
<body>

<header>
            <div class="barnav">
            <img src="img/logo.png" alt="logo" style="width:150px; height:150px; object-fit:contain; display:block;">
                <div class="nav">
                    <ul>
                        <li><a href="main_admin.php" >Accueil</a></li>
                        <div class="livre_not">
                            <li><a href="livre_ad.php" class="livre_notif">Livres</a></li>
                            <div class="notif">
                                <a href=""><?php #echo $notf; ?></a>
                            </div>
                        </div>
                        <li><a href="emprunt_ad.php"><span class="selec">Emprunt</span> </a></li>
                        <li><a href="utilisateur.php">Utilisateur</a></li>
                        <select id="menu" name="" onchange="window.location.href = this.value;">
                        <option value="parametre">Paramètre</option>
                            <option value="deco.php">Déconnexion</option>
                        </select>

                        
                    </ul>
                </div>
                <section class="panier">

                </section>
            </div>
        </header>
        <section class="ad_emprunt">
        <h1>SUIVI DES EMPRUNTS</h1>
        <p>Liste des emprunts :</p>
        <table class="tb_emp">
            <tr class="tr">
                <th>Utilisateur </th>
                <th>Livre</th>
                <th>Date d'emprunt</th>
                <th>Retour</th>
                <th>statut</th>
            </tr>


<?php foreach ($emprunts as $row): ?>
    <?php
        $aujourd_hui = new DateTime(); 
        $retour_prevu = new DateTime($row['date_retour_prevue']);
        $retard = '';

        if ($row['statut'] === 'rendu') {
            $retard = 'Non'; 
        } elseif ($retour_prevu < $aujourd_hui) {
            $diff = $retour_prevu->diff($aujourd_hui)->days;
            $retard = $diff . ' jour(s)';
        } else {
            $retard = 'Non';    
        }
    ?>
    <tr>
        <td><?php echo htmlspecialchars($row['nom']); ?></td>
        <td><?php echo htmlspecialchars($row['titre']); ?></td>
        <td><?php echo htmlspecialchars($row['date_emprunt']); ?></td>
        <td><?php echo htmlspecialchars($row['date_retour_prevue']); ?></td>
        <td>
<?php 
    $statut = $row['statut'];
    if ($statut === 'en cours') {
        echo '<span style="color: orange;">En cours</span>';
    } elseif ($statut === 'rendu') {
        echo '<span style="color: green;">Rendu</span>';
    } elseif ($statut === 'annulé') {
        echo '<span style="color: red;">Annulé</span>';
    } else {
        echo htmlspecialchars($statut);
    }
?>
</td>


    </tr>
<?php endforeach; ?>

        </table>
    </section>
</body>
</html>

