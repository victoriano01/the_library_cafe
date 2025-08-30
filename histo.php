<?php
$bdd = new PDO('mysql:host=localhost;dbname=gestion_biblio', 'root', '');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$id = $_SESSION['id'];

// Récupère les infos utilisateur
$stmt = $bdd->prepare("SELECT photo FROM utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// Définit la photo (par défaut si vide)
$photoPath = !empty($user['photo']) ? 'img/image/' . $user['photo'] : 'img/image/utilisateur.png';

if (!isset($_SESSION['id'])) {
    echo "Utilisateur non connecté.";
    exit;
}

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
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="/gestion_de_biblio/panier.css" />
    <link rel="stylesheet" href="/gestion_de_biblio/main.css" />
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
                    <li><a href="livre.php" class="livre_notif">Livre</a></li>
                </div>
                <li><a href="emprunt.php">Emprunts</a></li>
                <select id="menu" name="" onchange="window.location.href = this.value;">
                    <option value="parametre">Paramètres</option>
                    <option value="modifier_profil.php">Modifier le profil</option>
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

<?php
if (!isset($_GET['id_livre'])) {

    // Connexion à la base et récupération des emprunts
    $id_utilisateur = $_SESSION['id'];
    $stmt = $bdd->prepare("
        SELECT e.id_emprunt, l.titre, l.photo, a.nom_auteur, e.date_emprunt, e.statut
        FROM emprunts e
        JOIN livre l ON e.id_livre = l.id_livre
        JOIN auteur a ON l.id_auteur = a.id_auteur
        WHERE e.id_utilisateur = ?
        ORDER BY e.date_emprunt DESC
    ");
    $stmt->execute([$id_utilisateur]);
    $liste_emprunts = $stmt->fetchAll();
?>

<main class="main_panir">
    <h1>Mes emprunts</h1>

    <?php if (count($liste_emprunts) > 0): ?>
        <table cellpadding="10" class="tab">
            <thead>
                <tr>
                    <th>Couverture</th>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Date d'emprunt</th>
                    <th>Statut</th>
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
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align:center;"> Aucun emprunt effectué pour le moment.</p>
    <?php endif; ?>
</main>

<?php
exit;
}
?>

</body>
</html>
