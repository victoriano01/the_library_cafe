<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Traitement du retour d'un livre
if (isset($_POST['rendre'])) {
    $id_emprunt = $_POST['rendre'];

    // Vérification que l'emprunt appartient à l'utilisateur connecté
    $verif = $bdd->prepare("SELECT * FROM emprunts WHERE id_emprunt = ? AND id_utilisateur = ?");
    $verif->execute([$id_emprunt, $_SESSION['id']]);
    
    if ($verif->rowCount() > 0) {
        // Mise à jour du statut de l'emprunt
        $update = $bdd->prepare("UPDATE emprunts SET statut = 'rendu' WHERE id_emprunt = ?");
        $update->execute([$id_emprunt]);

        // Message de confirmation (facultatif)
        $_SESSION['message_rendu'] = "Livre rendu avec succès.";
    } else {
        // Emprunt invalide ou ne vous appartient pas
        $_SESSION['message_rendu'] = "Erreur : Emprunt invalide.";
    }

    // Rediriger pour éviter le renvoi de formulaire
    header('Location: emprunt.php');
    exit;
}


try {
    $bdd = new PDO('mysql:host=localhost;dbname=gestion_biblio', 'root', '');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mail = $_POST['mail'] ?? '';
    $mdp = $_POST['mdp'] ?? '';

    // Vérifier si c'est un administrateur
    $stmt_admin = $bdd->prepare("SELECT * FROM admin WHERE mail = ? AND mdp = ?");
    $stmt_admin->execute([$mail, $mdp]);
    $admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $_SESSION['id'] = $admin['id_admin'];         // ID de l'admin
        $_SESSION['mail'] = $admin['mail'];
        $_SESSION['role'] = 'admin';
        header("Location: main_admin.php");
        exit();
    }

    // Vérifier si c'est un utilisateur simple
    $stmt_user = $bdd->prepare("SELECT * FROM utilisateur WHERE mail = ? AND mdp = ?");
    $stmt_user->execute([$mail, $mdp]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['id'] = $user['id_utilisateur'];    // ID utilisateur bien utilisé ici
        $_SESSION['mail'] = $user['mail'];
        $_SESSION['role'] = 'utilisateur';
        header("Location: main.php");
        exit();
    }

    // Si aucun compte trouvé
    $erreur = "Nom ou mot de passe incorrect.";
    echo "<div style='color:red; text-align:center;'>$erreur</div>";
}
?>
