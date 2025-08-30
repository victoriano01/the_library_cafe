<?php
include('traitement.php');

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

// Recherche
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $q = "%" . trim($_GET['q']) . "%";
    $stmt = $bdd->prepare("
        SELECT livre.*, auteur.nom_auteur FROM livre 
        JOIN auteur ON livre.id_auteur = auteur.id_auteur 
        WHERE livre.titre LIKE ? OR auteur.nom_auteur LIKE ?
        ORDER BY livre.id_livre DESC
    ");
    $stmt->execute([$q, $q]);
    $livres = $stmt;
} else {
    $livres = $bdd->query("
        SELECT livre.*, auteur.nom_auteur FROM livre 
        JOIN auteur ON livre.id_auteur = auteur.id_auteur 
        ORDER BY livre.id_livre DESC
    ");
}

// Gérer l'ajout d’un livre dans le panier
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

$id_utilisateur = $_SESSION['id'];

$notifs = $bdd->query('SELECT COUNT(*) AS nombre FROM livre WHERE is_new = 1');
$notif = $notifs->fetch();
$notf = ($notif['nombre'] < 1) ? '' : '<a href="nouv.php" class="notif">
    <img src="img/image/notif.png" alt="">' . $notif['nombre'] . '</a>';

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
                    <li><a href="livre.php" class="livre_notif"><span class="selec">Livre</span></a></li>
                </div>
                <li><a href="emprunt.php">Emprunts</a></li>
                <select id="menu" name="" onchange="window.location.href = this.value;">
                    <option value="parametre">Paramètres</option>
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

<section id="livre">
    <div class="sec_nouv">
        <h1>Les Livres</h1>

        <form method="GET" action="" class="recherche-form">
            <div class="search">
                <input type="text" name="q" id="searchInput" placeholder="Rechercher un livre ou un auteur" class="recherche-input" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                <button type="submit" class="recherche-btn">
                    <img src="img/image/rech.png" alt="" style="width: 23px;">
                </button>
                <?php if (!empty($_GET['q'])): ?>
                    <button type="button" id="resetBtn" class="recherche-btn" style="margin-left: 10px; background-color: #ccc; padding: 5px 10px; border: none; cursor: pointer;">
                        Tout afficher
                    </button>
                <?php endif; ?>
            </div>
        </form>

        <div class="box">
            <?php while ($livre = $livres->fetch()): ?>
                <div class="box_livre">
                    <div class="couv">
                        <img src="img/couverture/<?php echo htmlspecialchars($livre['photo']); ?>" alt="" width="100">
                    </div>
                    <h1><?php echo htmlspecialchars($livre['titre']); ?></h1>
                    <p><?php echo htmlspecialchars($livre['nom_auteur']); ?></p>
                    <a href="emprunt.php?id_livre=<?php echo $livre['id_livre']; ?>">
                        <button class="btn">Emprunter</button>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<script>
    const resetBtn = document.getElementById("resetBtn");
    if (resetBtn) {
        resetBtn.addEventListener("click", () => {
            window.location.href = window.location.pathname;
        });
    }
</script>

</body>
</html>
