<?php

include('traitement.php');

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
    } if (isset($_POST['id_livre'], $_POST['titre'], $_POST['photo'], $_POST['auteur'], $_POST['date_retour'])) {
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


$livres = $bdd->query("SELECT titre, statut FROM livre");



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
                        <li><a href="main_admin.php" >Accueil </a></li>
                        <div class="livre_not">
                            <li><a href="livre_ad.php" class="livre_notif"><span class="selec">Livres</span></a></li>
                            <div class="notif">
                                <a href=""><?php #echo $notf; ?></a>
                            </div>
                        </div>
                        <li><a href="emprunt_ad.php">Emprunts </a></li>
                        <li><a href="utilisateur.php">Utilisateur</a></li>
                        <select id="menu" name="" onchange="window.location.href = this.value;">
                        <option value="parametre">Paramètre</option>
                        <option value="deco.php">Déconnexion</option>
                        </select>

                        
                    </ul>
                </div>
                <section class="panier">

            </div>
        </header>



<?php
include('traitement.php');

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $q = '%' . $_GET['q'] . '%';
    $stmt = $bdd->prepare("
        SELECT l.*, a.nom_auteur, c.titre_categorie 
        FROM livre l
        JOIN auteur a ON l.id_auteur = a.id_auteur
        JOIN categorie c ON l.id_categorie = c.id_categorie
        WHERE l.titre LIKE ? OR a.nom_auteur LIKE ?
        ORDER BY l.id_livre DESC
    ");
    $stmt->execute([$q, $q]);
    $livres = $stmt;
} else {
    $livres = $bdd->query("
        SELECT l.*, a.nom_auteur, c.titre_categorie 
        FROM livre l
        JOIN auteur a ON l.id_auteur = a.id_auteur
        JOIN categorie c ON l.id_categorie = c.id_categorie
        ORDER BY l.id_livre DESC
    ");
}
?>



    <h1>livre</h1>
    <p>
    <a class="btn btn-success" id="btn" href="ajout.php?page=ajout_livre">Ajouter un nouveau livre</a>
</p>
<form method="GET" action="" class="recherche-form">
    <div class="q">
        <input type="text" name="q" id="qInput" placeholder="Rechercher un livre ou un auteur" class="recherche-input" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
        <button type="submit" class="recherche-btn">
            <img src="img/image/rech.png" alt="" style="width: 23px;">
        </button>

        <!-- Bouton "Tout afficher" visible seulement quand une recherche est faite -->
        <?php if (!empty($_GET['q'])): ?>
            <button type="button" id="resetBtn" class="recherche-btn" style="margin-left: 10px; background-color: #ccc; padding: 5px 10px; border: none; cursor: pointer;">
                Tout afficher
            </button>
        <?php endif; ?>
    </div>
</form>

    <div class="row">
    <table class="table">
        <thead>
            <tr>
                <td>Couverture</td>
                <td>Ref</td>
                <td>Titre</td>
                <td>Auteur</td>
                <td>Catégorie</td>
                <td>Date de publication</td>
                <td>Statut</td>
            </tr>
        </thead>
        <tbody>
            <?php
                while($livre =$livres->fetch()){
                            $statut = $livre['statut'];
        $couleur = match($statut) {
            'disponible' => 'green',
            'emprunté' => 'orange',
            'réservé' => 'blue',
            'retardé' => 'red',
            'supprimé' => 'black',
            'nouveau' => 'purple',
            default => 'black',
        };
                    echo'
                     <tr>
                        <td><img src="img/couverture/' . $livre['photo'] . '" width="100px" height="100px" style="object-fit:contain; display:block;" alt="couverture"></td>
                        <td>'.$livre['id_livre'].'</td>
                        <td>'.$livre['titre'].'</td>
                        <td>'.$livre['nom_auteur'].'</td>
                        <td>'.$livre['titre_categorie'].'</td>
                        <td>'.$livre['date_de_sortie'].'</td>
                        <td><span style="color: '.$couleur.'; font-weight: bold;">'.ucfirst($statut).'</span></td>                        
                    </tr>
                    ';
                }
            ?>
           
        </tbody>
    </table>
</div>



<script>
    const resetBtn = document.getElementById("resetBtn");
    if (resetBtn) {
        resetBtn.addEventListener("click", () => {
            // Rediriger vers la même page sans le paramètre de recherche
            window.location.href = window.location.pathname;
        });
    }
</script>

</body>
</html>


