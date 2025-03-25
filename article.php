<?php
session_start();
require 'db.php';

// Vérifier si l'ID de l'article est fourni et valide
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // Récupérer l'article avec les informations de l'utilisateur
    $stmt = $conn->prepare("SELECT a.*, u.firstName, u.lastName FROM articles a LEFT JOIN users u ON a.userId = u.id WHERE a.id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($article) {
        // Récupérer les commentaires de l'article
        $commentsStmt = $conn->prepare("SELECT c.*, u.firstName, u.lastName FROM comments c JOIN users u ON c.userId = u.id WHERE c.articleId = :articleId ORDER BY c.created_at DESC");
        $commentsStmt->execute(['articleId' => $id]);
        $comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Gestion de l'ajout de commentaire
        $commentError = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id'])) {
                $commentError = "Vous devez être connecté pour commenter.";
            } else {
                $commentText = trim($_POST['comment']);
                if (empty($commentText)) {
                    $commentError = "Le commentaire ne peut pas être vide.";
                } else {
                    try {
                        $insertCommentStmt = $conn->prepare("INSERT INTO comments (articleId, userId, comment, created_at) VALUES (:articleId, :userId, :comment, NOW())");
                        $insertCommentStmt->execute([
                            'articleId' => $id,
                            'userId' => $_SESSION['user_id'],
                            'comment' => $commentText
                        ]);
                        header("Location: article.php?id=$id#comments");
                        exit;
                    } catch (PDOException $e) {
                        $commentError = "Erreur lors de l'envoi du commentaire.";
                        error_log("Erreur de commentaire : " . $e->getMessage());
                    }
                }
            }
        }
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($article['title']); ?></title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
        </head>
        <body>
            <div class="container mt-5">
                <h1 class="mb-4"><?php echo htmlspecialchars($article['title']); ?></h1>

                <?php if (!empty($article['image'])): ?>
                    <img src="<?php echo htmlspecialchars($article['image']); ?>" alt="Article Image" class="img-fluid mb-4">
                <?php endif; ?>

                <?php if (!empty($article['description'])): ?>
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($article['description'])); ?></p>
                <?php endif; ?>

                <p><small class="text-muted">Créé par <?php echo htmlspecialchars($article['firstName'] . ' ' . $article['lastName'] ?: 'Auteur inconnu'); ?> le <?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?></small></p>
                <a href="index.php" class="btn btn-secondary mt-3 mb-4">Retour à la liste des articles</a>

                <div id="comments" class="mt-4">
                    <h3>Commentaires (<?php echo count($comments); ?>)</h3>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="POST" class="mb-4">
                            <?php if (!empty($commentError)): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($commentError); ?></div>
                            <?php endif; ?>
                            <textarea class="form-control" name="comment" rows="3" placeholder="Votre commentaire..." required></textarea>
                            <button type="submit" class="btn btn-primary mt-2">Envoyer</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">Veuillez vous <a href="login.php">connecter</a> pour commenter.</div>
                    <?php endif; ?>
                    
                    <?php if (count($comments) > 0): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                    <footer class="blockquote-footer">
                                        <?php echo htmlspecialchars($comment['firstName'] . ' ' . $comment['lastName']); ?> le <?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?>
                                    </footer>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun commentaire pour le moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "Article introuvable.";
    }
} else {
    echo "ID d'article invalide.";
}
?>