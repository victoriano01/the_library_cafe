<?php
session_start();
include('traitement.php');

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$id_utilisateur = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mise à jour des autres infos (nom, prenom, etc.) si envoyées
    if (isset($_POST['nom'], $_POST['prenom'], $_POST['mail'], $_POST['mdp'])) {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $mail = $_POST['mail'];
        $mdp = $_POST['mdp'];

        $stmt = $bdd->prepare("UPDATE utilisateur SET nom = ?, prenom = ?, mail = ?, mdp = ? WHERE id_utilisateur = ?");
        $stmt->execute([$nom, $prenom, $mail, $mdp, $id_utilisateur]);

        $_SESSION['profil_update_success'] = true;
        header('Location: profil.php');
        exit;
    }

    // Mise à jour de la photo si uploadée
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed)) {
            $newName = uniqid('profil_', true) . '.' . $ext;
            $dest = 'img/image/' . $newName;

            if (move_uploaded_file($tmpName, $dest)) {
                $stmt = $bdd->prepare("UPDATE utilisateur SET photo = ? WHERE id_utilisateur = ?");
                $stmt->execute([$newName, $id_utilisateur]);
            }
        }
        header('Location: profil.php');
        exit;
    }
}
?>


