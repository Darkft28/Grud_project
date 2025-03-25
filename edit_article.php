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

// Fetch the article
try {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = :id");
    $stmt->execute(['id' => $articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article || $article['userId'] != $_SESSION['user_id']) {
        header("Location: index.php?error=Vous n'êtes pas autorisé à modifier cet article");
        exit;
    }
} catch (PDOException $e) {
    error_log("Erreur de récupération de l'article : " . $e->getMessage());
    header("Location: index.php?error=Erreur de récupération de l'article");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $existingImage = $article['image'] ?? null;

    if (empty($title) || empty($description)) {
        $error = "Le titre et la description sont obligatoires.";
    } else {
        try {
            // Handle image upload or deletion
            $imageUrl = $existingImage;
            if (isset($_POST['deleteImage']) && $_POST['deleteImage'] === 'on') {
                // Delete the existing image
                if ($existingImage && file_exists($existingImage)) {
                    unlink($existingImage);
                }
                $imageUrl = null;
            } elseif (!empty($_FILES['imageUpload']['name'])) {
                $uploadDir = 'uploads/';
                
                // Create uploads directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = uniqid() . '_' . basename($_FILES['imageUpload']['name']);
                $uploadPath = $uploadDir . $fileName;

                // Move uploaded file
                if (move_uploaded_file($_FILES['imageUpload']['tmp_name'], $uploadPath)) {
                    // Delete the old image if a new one is uploaded
                    if ($existingImage && file_exists($existingImage)) {
                        unlink($existingImage);
                    }
                    $imageUrl = $uploadPath;
                } else {
                    $error = "Erreur lors du téléchargement de l'image.";
                }
            }

            // Prepare update statement
            $updateStmt = $conn->prepare("
                UPDATE articles 
                SET title = :title, 
                    description = :description, 
                    image = :image, 
                    updated_at = NOW() 
                WHERE id = :id
            ");

            $updateStmt->execute([
                'title' => $title,
                'description' => $description,
                'image' => $imageUrl,
                'id' => $articleId
            ]);

            // Redirect to the article page or dashboard
            header("Location: index.php?message=Article mis à jour avec succès");
            exit;

        } catch (PDOException $e) {
            error_log("Erreur de mise à jour de l'article : " . $e->getMessage());
            $error = "Erreur lors de la mise à jour de l'article.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'Article</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Modifier l'Article</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Titre</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="title" 
                    name="title" 
                    value="<?php echo htmlspecialchars($article['title'] ?? ''); ?>" 
                    required
                >
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea 
                    class="form-control" 
                    id="description" 
                    name="description" 
                    rows="5" 
                    required
                ><?php echo htmlspecialchars($article['description'] ?? ''); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="imageUpload" class="form-label">Image (optionnel)</label>
                <input 
                    type="file" 
                    class="form-control" 
                    id="imageUpload" 
                    name="imageUpload" 
                    accept="image/*"
                >
                <?php if (!empty($article['image'])): ?>
                    <div class="mt-2">
                        <p>Image actuelle :</p>
                        <img src="<?php echo htmlspecialchars($article['image']); ?>" alt="Image actuelle" style="max-width: 200px;">
                        <div class="form-check mt-2">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="deleteImage" 
                                name="deleteImage"
                            >
                            <label class="form-check-label" for="deleteImage">Supprimer l'image actuelle</label>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="index.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</body>
</html>