<?php
$bdd = new PDO('mysql:host=localhost;dbname=gestion_biblio', 'root', '');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
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

// Annuler emprunt (changer le statut au lieu de supprimer)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer'])) {
    $id_emprunt = intval($_POST['supprimer']);
    $stmt_annule = $bdd->prepare("UPDATE emprunts SET statut = 'annulé' WHERE id_emprunt = ? AND id_utilisateur = ?");
    $stmt_annule->execute([$id_emprunt, $id_utilisateur]);
    header("Location:panier.php");
    exit;
}

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

<main class="main_panir">
    <h1>Panier</h1>

    <?php if ($panier_count > 0): ?>
        <table class="tab">
            <thead>
                <tr>
                    <th>Couverture</th>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Date d'emprunt</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($emprunts as $item): ?>
                    <tr>
                        <td><img src="img/couverture/<?php echo htmlspecialchars($item['photo']); ?>" width="90" style="margin: 1rem; border-radius: 5px;"></td>
                        <td><?php echo htmlspecialchars($item['titre']); ?></td>
                        <td><?php echo htmlspecialchars($item['nom_auteur']); ?></td>
                        <td><?php echo htmlspecialchars($item['date_emprunt']); ?></td>
                        <td>
                            <form method="post" action="panier.php" class="sup">
                                <input type="hidden" name="supprimer" value="<?php echo $item['id_emprunt']; ?>">
                                <button type="submit" onclick="return confirm('Annuler ce livre ?')" class="btn_sup">
                                    Annuler
                                    <img src="img/image/pob.png" alt="">
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align:center;">Aucun livre dans le panier.</p>
    <?php endif; ?>
</main>
</body>
</html>
