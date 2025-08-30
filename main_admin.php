<?php


include('traitement.php');
// Compter les emprunts en cours
$id_utilisateur = $_SESSION['id'];
$stmt = $bdd->prepare("SELECT COUNT(*) FROM emprunts WHERE id_utilisateur = ? AND statut = 'en cours'");
$stmt->execute([$id_utilisateur]);
$nb_emprunts_en_cours = $stmt->fetchColumn();
// Requête pour récupérer les emprunts récents (par ex. 5 derniers)
$stmt = $bdd->query("
    SELECT u.nom, l.titre, e.date_retour_prevue, e.statut
    FROM emprunts e
    JOIN livre l ON e.id_livre = l.id_livre
    JOIN utilisateur u ON e.id_utilisateur = u.id_utilisateur
    WHERE e.statut = 'en cours' OR e.statut = 'retardé'
    ORDER BY e.date_retour_prevue DESC
    LIMIT 5
");
$emprunts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer le nombre total d'emprunts en retard (hors boucle)
$stmt_retards = $bdd->query("
    SELECT COUNT(*) 
    FROM emprunts 
    WHERE statut = 'en cours' 
    AND date_retour_prevue < CURDATE()
");
$nb_retards = $stmt_retards->fetchColumn();

// Exemple d'utilisation dans l'affichage (boucle sur les emprunts)
$aujourd_hui = new DateTime();

foreach ($emprunts as $row) {
    $date_retour = new DateTime($row['date_retour_prevue']);
    $retard = 0;

    if ($row['statut'] === 'en cours' && $date_retour < $aujourd_hui) {
        $interval = $date_retour->diff($aujourd_hui);
        $retard = $interval->days; // jours de retard
    }

    // Ici tu peux afficher $row et $retard si besoin
}



$emprunts = $stmt->fetchAll();

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

// Derniers emprunts
$stmt = $bdd->query("
    SELECT u.nom, l.titre, e.date_retour_prevue
    FROM emprunts e
    JOIN livre l ON e.id_livre = l.id_livre
    JOIN utilisateur u ON e.id_utilisateur = u.id_utilisateur
    ORDER BY e.date_retour_prevue DESC
    LIMIT 5
");
$emprunts = $stmt->fetchAll();
if (empty($emprunts)) {

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/gestion_de_biblio/panier.css">
    <link rel="stylesheet" href="/gestion_de_biblio/main.css">
    <link rel="stylesheet" href="/gestion_de_biblio/table.css">
    <title>acceuil_admin</title>
</head>
<body>
    <header>
        <div class="barnav">
            <img src="img/logo.png" alt="logo" style="width:150px; height:150px; object-fit:contain; display:block;">
            <div class="nav">
                <ul>
                    <li><a href="main_admin.php"><span class="selec">Accueil</span></a></li>
                    <div class="livre_not">
                        <li><a href="livre_ad.php" class="livre_notif">Livre</a></li>
                        <div class="notif">
                            <a href=""><?php /* echo $notf; */ ?></a>
                        </div>
                    </div>
                    <li><a href="emprunt_ad.php">Emprunts</a></li>
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

    <section class="ad_main">
        <h1>Tableau de bord</h1>
        <div class="group-box">
            <a href="livre_ad.php" class="BOX">
            <div class="box a">
                    <h2>Total de livres</h2>
                    <p><?php echo $data['total_livres']; ?></p>  
            </div>
            </a>

            <a href="utilisateur.php" class="BOX">
            <div class="box b">
                <h2>Total d’utilisateurs</h2>
                <p><?php echo $data['total_users']; ?></p>
            </div>
            </a>

            <a href="emprunt_ad.php" class="BOX">
                 <div class="box a">
                <h2>Emprunts en cours</h2>
                <p><?php echo $nb_emprunts_en_cours; ?></p>
            </div>
            </a>
            <div class="box b">
                <h2>Retards</h2>
                <p><?php echo $nb_retards?></p>
            </div>
        </div>

        <div class="dernier_empr">
            <h1>Derniers Emprunts</h1>
            <table class="der_empru">
                <tr class="list titre">
                    <td>NOM</td>
                    <td>LIVRE</td>
                    <td>DATE RETOUR</td>
                </tr>
                <?php foreach ($emprunts as $row): ?>
                    <tr class="list sec">
                        <td><?php echo htmlspecialchars($row['nom']); ?></td>
                        <td><?php echo htmlspecialchars($row['titre']); ?></td>
                        <td><?php echo htmlspecialchars($row['date_retour_prevue']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </section>
</body>
</html>
