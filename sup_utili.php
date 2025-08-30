<?php
session_start();
include('traitement.php');

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    // Supprime l'utilisateur de la base
    $stmt = $bdd->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$id]);
    
    // Redirige vers la page des utilisateurs
    header("Location:utilisateur.php");
    exit;
}
