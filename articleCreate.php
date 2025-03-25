<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Validate title length (should be <= 100 characters)
    if (strlen($title) > 100) {
        $error = "Le titre ne doit pas dépasser 100 caractères.";
    } 
    // Validate description length (should be <= 1000 characters)
    elseif (strlen($description) > 1000) {
        $error = "La description ne doit pas dépasser 1000 caractères.";
    } else {
        // Limiter le titre à 100 caractères (en cas de manipulation côté client)
        $title = substr($title, 0, 100);
        // Limiter la description à 1000 caractères (en cas de manipulation côté client)
        $description = substr($description, 0, 1000);

        $image = ''; // Implement image upload logic if needed

        // Handle image upload (optional)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
            $uploadPath = $uploadDir . $imageName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $image = $uploadPath;
            }
        }

        // If no error, proceed to insert the article
        if (!isset($error)) {
            try {
                $stmt = $conn->prepare("INSERT INTO articles (userId, title, image, description, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?)");
                $fullName = $_SESSION['firstName'] . ' ' . $_SESSION['lastName'];
                $stmt->execute([$userId, $title, $image, $description, $fullName, $fullName]);

                header("Location: index.php?message=" . urlencode("Article créé avec succès"));
                exit();
            } catch (PDOException $e) {
                $error = "Création d'article échouée : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un Article</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Créer un Nouvel Article</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Titre (max 100 caractères)</label>
                <input type="text" class="form-control" id="title" name="title" maxlength="100" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description (max 1000 caractères)</label>
                <textarea class="form-control" id="description" name="description" rows="5" maxlength="1000" required></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image (optionnel)</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Créer l'Article</button>
        </form>
    </div>
</body>
</html>
