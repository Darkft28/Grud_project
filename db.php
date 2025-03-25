<?php
// db.php
require 'env.php';

try {
    // Connexion à la base de données
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Affiche l'erreur si la connexion échoue
    echo "Connection failed: " . $e->getMessage();
    die(); // Arrête le script si la connexion échoue
}
?>
