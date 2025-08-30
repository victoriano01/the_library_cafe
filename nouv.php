<?php
include('traitement.php');

$id = $_SESSION['id'];

// Récupère les infos utilisateur
$stmt = $bdd->prepare("SELECT photo FROM utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// Définit la photo (par défaut si vide)
$photoPath = !empty($user['photo']) ? 'img/image/' . $user['photo'] : 'img/image/utilisateur.png';


// Gérer l'ajout d’un livre dans le panier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['supprimer'])) {
        // Supprimer un livre du panier
        $id_suppr = $_POST['supprimer'];
        foreach ($_SESSION['panier'] as $index => $item) {
            if ($item['id_livre'] == $id_suppr) {
                unset($_SESSION['panier'][$index]);
            }
        }
        // Réindexer le tableau
        $_SESSION['panier'] = array_values($_SESSION['panier']);
    } elseif (isset($_POST['id_livre'], $_POST['titre'], $_POST['photo'], $_POST['auteur'], $_POST['date_retour'])) {
        // Ajouter un livre
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

        // Éviter les doublons
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



$id_utilisateur = $_SESSION['id'];

// Gérer les notifications
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/gestion_de_biblio/panier.css">
    <link rel="stylesheet" href="/gestion_de_biblio/main.css">
    <title>Accueil</title>
</head>
<body>
<header>
    <div class="barnav">
        <img src="img/logo.png" alt="Logo" style="width:150px; height:150px; object-fit:contain; display:block;">
        <div class="nav">
            <ul>
                <li><a href="main.php">Accueil</a></li>
                <div class="livre_not">
                    <li><a href="livre.php" class="livre_notif"><span class="selec">Livres</span></a></li>
                </div>
                <li><a href="emprunt.php">Emprunts</a></li>
                <select id="menu" onchange="window.location.href = this.value;">
                    <option value="parametre">Paramètres</option>
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
                    <img src="img/image/cart.png" alt="Panier" style="width: 35px !important; height: auto;">
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
</body>
</html>

<?php
include('traitement.php');
$livres = $bdd->query('SELECT * FROM livre l 
    JOIN categorie c ON c.id_categorie = l.id_categorie 
    JOIN auteur a ON a.id_auteur = l.id_auteur 
    WHERE is_new = 1');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/gestion_de_biblio/table.css">
    <title>Nouveaux Livres</title>
</head>
<body>
<div class="row">
    <h1>Nouveaux Livres Ajoutés</h1>
    <table class="table">
        <thead>
            <tr>
                <td>Couverture</td>
                <td>Titre</td>
                <td>Auteur</td>
                <td>Catégorie</td>
                <td>Date de publication</td>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($livre = $livres->fetch()) {
                echo '
                <tr>
                    <td><img src="img/couverture/' . $livre['photo'] . '" width="100px" height="100px" style="object-fit:contain; display:block;" alt="Couverture"></td>
                    <td>' . htmlspecialchars($livre['titre']) . '</td>
                    <td>' . htmlspecialchars($livre['nom_auteur']) . '</td>
                    <td>' . htmlspecialchars($livre['titre_categorie']) . '</td>
                    <td>' . htmlspecialchars($livre['date_de_sortie']) . '</td>
                </tr>';
            }

            $bdd->query('UPDATE livre SET is_new = 0 WHERE is_new = 1');
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
