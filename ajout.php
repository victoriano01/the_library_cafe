
<?php
include('traitement.php');


$panier_count = 0;
if (isset($_SESSION['panier']) && is_array($_SESSION['panier'])) {
    $panier_count = count($_SESSION['panier']);
}

$count = null;
$notifs = $bdd->query('SELECT COUNT(*) AS nombre FROM livre WHERE is_new = 1');
    $notif = $notifs->fetch();
    if ($notif['nombre'] < 1) {
        $notf = '';
    } else {
        $nombre = isset($notif['nombre']) ? $notif['nombre'] : '';
        $notf = '<a href="nouv.php" class="notif">
                    <img src="img/image/notif.png" alt="">' . $nombre . '</a>';
    }
?>
<!DOCTYPE html>
<html lang="fr"> <!-- Langue corrigée -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/gestion_de_biblio/panier.css">
    <link rel="stylesheet" href="/gestion_de_biblio/main.css">
    <title>Ajout</title> <!-- Majuscule au titre -->
</head>
<body>
<header>
    <div class="barnav">
        <img src="img/logo.png" alt="Logo" style="width:150px; height:150px; object-fit:contain; display:block;">
        <div class="nav">
            <ul>
                <li><a href="main_admin.php">Accueil</a></li> <!-- Correction : Acceuil → Accueil -->
                <div class="livre_not">
                    <li><a href="livre_ad.php" class="livre_notif"> <span class="selec">Livres</span> </a></li>
                </div>
                <li><a href="emprunt_ad.php">Emprunts</a></li>
                <select id="menu" name="" onchange="window.location.href = this.value;">
                    <option value="modifier_profil.php">Modifier le profil</option>
                    <option value="historique.php">Historique</option>
                    <option value="parametres.php">Préférences</option>
                    <option value="deco.php">Déconnexion</option>
                </select>
            </ul>
        </div>
        <div class="notif">
            <a href=""><?php echo $notf; ?></a>
        </div>
    </div>
</header>

<?php
$bdd = new PDO('mysql:host=localhost;dbname=gestion_biblio','root','');
$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$cats = $bdd->query('SELECT * FROM categorie');
?>

<div class="ajout">
    <h2>Ajouter un livre</h2>
    <div class="box_aj">
        <form action="aj.php" method="post" enctype="multipart/form-data" class="from_ajout">
            <label>Titre</label><br>
            <input type="text" name="titre"><br> 
        
            <label>Auteur</label><br>
            <input type="text" name="auteur"><br>
        
            <label>Catégorie</label><br>
            <select name="cats" class="select">
                <option value="">Choisir une catégorie</option>
                <?php while ($cat = $cats->fetch()) { ?>
                    <option value="<?= $cat['id_categorie'] ?>">
                        <?= htmlspecialchars($cat['titre_categorie']) ?>
                    </option>
                <?php } ?>
            </select><br>
        
            <label>Date de publication</label><br>
            <input type="date" name="date"><br><br>

            <label for="file" id="file-label" class="custom-file-label">
                <img src="img/image/dow.png" alt="" width="25px" height="25px"> Couverture
            </label>
            <input type="file" id="file" name="file" class="file-input"><br>

            <button type="submit">Enregistrer</button>
        </form>
    </div>
</div>

<script>
    const input = document.getElementById('file');
    const label = document.getElementById('file-label');

    input.addEventListener('change', function () {
        if (this.files[0]) {
            label.textContent = this.files[0].name;
        } else {
            label.textContent = "📁 Couverture";
        }
    });
</script>
</body>
</html>
