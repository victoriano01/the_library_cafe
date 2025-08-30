<?php
$bdd = new PDO('mysql:host=localhost;dbname=gestion_biblio','root','');
$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    $infofichier = pathinfo($_FILES['file']['name']);
    $extension = strtolower($infofichier['extension']);
    $extensions_autorisees = ['jpg', 'jpeg', 'png'];
    $name = basename($_FILES['file']['name']);

    if (in_array($extension, $extensions_autorisees)) {
        if (!empty($_POST['titre']) && !empty($_POST['auteur']) && !empty($_POST['cats']) && !empty($_POST['date'])) {

            // Vérifier si l'auteur existe déjà
            $stmt = $bdd->prepare("SELECT id_auteur FROM auteur WHERE nom_auteur = ?");
            $stmt->execute([$_POST['auteur']]);
            $auteur = $stmt->fetch();

            if ($auteur) {
                $id_auteur = $auteur['id_auteur'];
            } else {
                // Ajouter l'auteur
                $stmt = $bdd->prepare("INSERT INTO auteur(nom_auteur) VALUES (?)");
                $stmt->execute([$_POST['auteur']]);
                $id_auteur = $bdd->lastInsertId();
            }

            // Déplacer le fichier
            move_uploaded_file($_FILES['file']['tmp_name'], 'img/couverture/' . $name);


            // Mettre tous les anciens livres comme "non nouveaux"
            $bdd->exec("UPDATE livre SET is_new = 0");

            // Ajouter le nouveau livre comme "nouveau"
            $stmt = $bdd->prepare("INSERT INTO livre(titre, id_auteur, id_categorie, date_de_sortie, photo, is_new)
                       VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([
            $_POST['titre'],
            $id_auteur,
            $_POST['cats'],
            $_POST['date'],
            $name
        ]);

            header('Location:main_admin.php?success=1');
                exit();

            
        } else {
            echo "Tous les champs doivent être remplis.";
        }
    } else {
        echo "Extension de fichier non valide.";
    }
} else {
    echo "Erreur lors de l'envoi du fichier.";
}
