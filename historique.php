<?php
session_start();
include('traitement.php');

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$id_utilisateur = $_SESSION['id'];

// emprunts en cours
$stmtEnCours = $bdd->prepare("
    SELECT emprunts.*, livre.titre 
    FROM emprunts 
    JOIN livre ON emprunts.id_livre = livre.id_livre 
    WHERE emprunts.id_utilisateur = ? AND emprunts.statut = 'en cours'
");
$stmtEnCours->execute([$id_utilisateur]);
$enCours = $stmtEnCours->fetchAll();

// empruntss rendus (historique)
$stmtRendu = $bdd->prepare("
    SELECT emprunts.*, livre.titre 
    FROM emprunts 
    JOIN livre ON emprunts.id_livre = livre.id_livre 
    WHERE emprunts.id_utilisateur = ? AND emprunts.statut = 'rendu'
    ORDER BY emprunts.date_retour DESC
");
$stmtRendu->execute([$id_utilisateur]);
$rendus = $stmtRendu->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des emprunts</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        h2 { color: #4A3DA3; }
        .section { margin-bottom: 30px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 5px #ccc; }
        .ligne { padding: 10px 0; border-bottom: 1px solid #eee; }
        .ligne:last-child { border-bottom: none; }
    </style>
</head> 
<body>

<h1>📚 Mon historique d'emprunts</h1>

<div class="section">
    <h2>📘 emprunts en cours</h2>
    <?php if (count($enCours) > 0): ?>
        <?php foreach ($enCours as $emprunts): ?>
            <div class="ligne">
                <strong><?= htmlspecialchars($emprunts['titre']) ?></strong> — empruntsé le <?= $emprunts['date_emprunts'] ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun emprunts en cours.</p>
    <?php endif; ?>
</div>

<div class="section">
    <h2>📗 emprunts rendus</h2>
    <?php if (count($rendus) > 0): ?>
        <?php foreach ($rendus as $emprunts): ?>
            <div class="ligne">
                <strong><?= htmlspecialchars($emprunts['titre']) ?></strong> — empruntsé le <?= $emprunts['date_emprunts'] ?>, rendu le <?= $emprunts['date_retour'] ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun emprunts rendu pour l’instant.</p>
    <?php endif; ?>
</div>

</body>
</html>
