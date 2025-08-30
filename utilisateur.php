<?php
include('traitement.php');
$stmt = $bdd->query("SELECT * FROM utilisateur");
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="/gestion_de_biblio/panier.css" />
    <link rel="stylesheet" href="/gestion_de_biblio/main.css" />
    <link rel="stylesheet" href="/gestion_de_biblio/table.css" />
    <title>Utilisateurs</title>
</head>
<body>

<header>
    <div class="barnav">
        <img src="img/logo.png" alt="logo" style="width:150px; height:150px; object-fit:contain; display:block;">
        <div class="nav">
            <ul>
                <li><a href="main_admin.php">Accueil</a></li>
                <div class="livre_not">
                    <li><a href="livre_ad.php" class="livre_notif">Livre</a></li>
                    <div class="notif">
                        <a href=""><?php /* echo $notf; */ ?></a>
                    </div>
                </div>
                <li><a href="emprunt_ad.php">Emprunts</a></li>
                <li><a href="utilisateur.php"><span class="selec">Utilisateur</span></a></li>
                <select id="menu" onchange="window.location.href = this.value;">
                    <option value="parametre">Paramètre</option>
                    <option value="deco.php">Déconnexion</option>
                </select>
            </ul>
        </div>
        <section class="panier"></section>
    </div>
</header>

<div class="row">
    <h1>Utilisateurs</h1>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Profil</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Supprimer</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($utilisateurs as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id_utilisateur']) ?></td>
                    <td>
                        <?php if (!empty($user['photo'])): ?>
                            <img src="img/image/<?= htmlspecialchars($user['photo']) ?>" width="50" height="50" style="border-radius:50%;" alt="Photo profil">
                        <?php else: ?>
                            <img src="img/image/utilisateur.png" width="50" height="50" style="border-radius:50%;" alt="Photo profil par défaut">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($user['nom']) ?></td>
                    <td><?= htmlspecialchars($user['prenom']) ?></td>
                    <td><?= htmlspecialchars($user['mail']) ?></td>
                    <td>
                        <form method="POST" action="sup_utili.php" onsubmit="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($user['id_utilisateur']) ?>">
                            <button type="submit" style="background-color: red; color: white; font-size: 16px; cursor: pointer;">
                                🗑 Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
