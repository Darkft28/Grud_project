<?php
// Configuration de la base de données
require 'db.php';

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header("Location: login.php");
    exit();
}

// Vérifier si l'ID de l'utilisateur est passé en paramètre
if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    // Préparer et exécuter la requête de suppression
    $sql = "DELETE FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Détruire la session après suppression
        session_destroy();

        // Redirection vers la page de connexion après déconnexion
        header("Location: login.php");
        exit();
    } else {
        echo "Une erreur s'est produite lors de la suppression de l'utilisateur.";
    }
} else {
    echo "Aucun ID d'utilisateur spécifié.";
}
?>