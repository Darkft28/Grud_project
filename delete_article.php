<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if article ID is provided
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: index.php?error=Invalid article ID");
    exit;
}

$articleId = intval($_GET['id']);

try {
    // First, verify that the article belongs to the logged-in user
    $stmt = $conn->prepare("SELECT userId FROM articles WHERE id = :id");
    $stmt->execute(['id' => $articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article || $article['userId'] != $_SESSION['user_id']) {
        header("Location: index.php?error=Vous n'êtes pas autorisé à supprimer cet article");
        exit;
    }

    // Delete the article
    $deleteStmt = $conn->prepare("DELETE FROM articles WHERE id = :id");
    $deleteStmt->execute(['id' => $articleId]);

    // Redirect with success message
    header("Location: index.php?message=Article supprimé avec succès");
    exit;

} catch (PDOException $e) {
    // Log the error and redirect with an error message
    error_log("Erreur de suppression d'article : " . $e->getMessage());
    header("Location: index.php?error=Erreur lors de la suppression de l'article");
    exit;
}
?>