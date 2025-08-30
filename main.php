


<?php
include('traitement.php');
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    echo "Utilisateur non connecté.";
    echo '<a href="login.php">Connecter ici</a>';
    exit;
}

$id = $_SESSION['id'];

// Récupère les infos utilisateur
$stmt = $bdd->prepare("SELECT photo FROM utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// Définit la photo (par défaut si vide)
$photoPath = !empty($user['photo']) ? 'img/image/' . $user['photo'] : 'img/image/utilisateur.png';


$livres = $bdd->query("
    SELECT livre.*, auteur.nom_auteur 
    FROM livre 
    JOIN auteur ON livre.id_auteur = auteur.id_auteur 
    ORDER BY livre.id_livre DESC 
    LIMIT 4
");





// Gérer les notifications
$notifs = $bdd->query('SELECT COUNT(*) AS nombre FROM livre WHERE statut="en cours"');
$notif = $notifs->fetch();
$notf = ($notif['nombre'] < 1) ? '' : '<a href="nouv.php" class="notif">
    <img src="img/image/notif.png" alt="">' . $notif['nombre'] . '</a>';



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
        <img src="img/logo.png" alt="logo" style="width:150px; height:150px; object-fit:contain; display:block;">
            <div class="nav">
                <ul>
                    <li><a href="main.php" ><span class="selec">Acceuil</span> </a></li>
                    <div class="livre_not">
                        <li><a href="livre.php" class="livre_notif">Livre </a></li>
                       
                    </div>
                    <li><a href="emprunt.php">Emprunts </a></li>
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

    <section class="sec_main">
        <div class="main">
            <div class="text">
            <h1>Bienvenue dans votre bibliothèque<br>en ligne !</h1>
<p>Une bibliothèque à portée de main : explorez les ouvrages, empruntez ce qui vous inspire,
et laissez-vous porter par une lecture simple, rapide et accessible.</p>

                <div class="info resx">
                    <img src="img/image/twitter.png" alt="">
                    <img src="img/image/facebook.png" alt="">
                    <img src="img/image/instagram.png" alt="">
                </div>
                <button><a href="livre.php">Voir les livres</a></button>

                
            </div>
            <div class="pht">
                <img src="img/photo_fond.png" alt="">
            </div>
        </div>

        <div class="more">
            <div class="box_info">
                <div class="info a">
                    <p>Explorez notre bibliothèque numérique et accédez à une large sélection de livres adaptés à tous les goûts et à tous les niveaux.</p>
                    <img src="img/image/pile-de-livres.png" alt="" class="imgs">
                </div>
                <div class="info b">
                    <p>Grâce à notre interface intuitive, ajoutez vos livres préférés à votre panier et commencez à les emprunter en quelques clics.</p>
                    <img src="img/image/panier.png" alt="" class="imgs">
                </div>
                <div class="info c">
                    <p>Gérez facilement vos emprunts, retrouvez l’historique de vos lectures et découvrez des suggestions personnalisées.</p>
                    <img src="img/image/programme.png" alt="" class="imgs">
                </div>
                
            </div>
        </div>

    </section>
    <section id="livre ">
        <div class="sec_nouv " id="bas">
        <h1>Nouveautés</h1> 
            <div class="box">
                <?php while ($livre = $livres->fetch()): ?>
                    <div class="box_livre">
                        <div class="couv">
                            <img src="img/couverture/<?php echo htmlspecialchars($livre['photo']); ?>" alt="" width="100">
                        </div>
                        <h1><?php echo htmlspecialchars($livre['titre']); ?></h1>
                        <p><?php echo htmlspecialchars($livre['nom_auteur']); ?></p>
                        <a href="emprunt.php?id_livre=<?php echo $livre['id_livre']; ?>">
                            <button class="btn">Emprunte</button>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>    
    </section>
    <footer>
        <img src="img/logo.png" alt="" width="30px">
        <div class="texte_footer">
            <p>© 2025 Bibliothèque numérique. Tous droits réservés.</p>
            <p>Développé par Steeve – Projet universitaire</p>
            <p>Contact : biblio@example.com</p>
            <p>Tél. : 032 79 547 60</p> 
        </div>
        <div class="info">
            <img src="img/image/twitter.png" alt="">
            <img src="img/image/facebook.png" alt="">
            <img src="img/image/instagram.png" alt="">
        </div>
    </footer>
        
</body>
</html>