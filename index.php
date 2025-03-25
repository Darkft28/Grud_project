<?php
session_start();
require 'db.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

try {
    // Fetch all users
    $usersStmt = $conn->query("SELECT * FROM users");
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all articles with user information
    $articlesStmt = $conn->query("
        SELECT a.*, u.firstName, u.lastName 
        FROM articles a 
        LEFT JOIN users u ON a.userId = u.id 
        ORDER BY a.created_at DESC
    ");
    $articles = $articlesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur de récupération des données : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Tableau de Bord</h1>
            <?php if (!$isLoggedIn): ?>
                <div>
                    <a href="login.php" class="btn btn-primary">Connexion</a>
                    <a href="register.php" class="btn btn-success">Inscription</a>
                </div>
            <?php else: ?>
                <div>
                    <span>Bonjour, <?php echo htmlspecialchars($_SESSION['firstName'] . ' ' . $_SESSION['lastName']); ?></span>
                    <a href="logout.php" class="btn btn-danger">Déconnexion</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if ($isLoggedIn): ?>
            <div class="mb-4">
                <a href="articleCreate.php" class="btn btn-primary">Créer un Nouvel Article</a>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <h2>Articles Récents</h2>
                <?php if (count($articles) > 0): ?>
                    <?php foreach ($articles as $article): ?>
                        <div class="card mb-3">
                            <?php if (!empty($article['image'])): ?>
                                <img src="<?php echo htmlspecialchars($article['image']); ?>" class="card-img-top" alt="Article Image">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($article['description']); ?></p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        Créé par <?php echo htmlspecialchars($article['firstName'] . ' ' . $article['lastName']); ?> 
                                        le <?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?>
                                    </small>
                                </p>

                                <?php if ($isLoggedIn && $_SESSION['user_id'] == $article['userId']): ?>
                                    <a href="edit_article.php?id=<?php echo $article['id']; ?>" class="btn btn-light">
                                        <img src="assets/img/edit.png" alt="Edit Icon" style="width: 20px; height: 20px;">
                                    </a>
                                    <a href="delete_article.php?id=<?php echo $article['id']; ?>" class="btn btn-light">
                                        <img src="assets/img/close.png" alt="Delete Icon" style="width: 20px; height: 20px;">
                                    </a>
                                <?php endif; ?>
                                
                                <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-primary">Voir plus</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun article trouvé.</p>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <h2>Utilisateurs</h2>
                <?php if (count($users) > 0): ?>
                    <ul class="list-group">
                        <?php foreach ($users as $user): ?>
                            <li class="list-group-item">
                                <?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?>
                                <small class="text-muted d-block"><?php echo htmlspecialchars($user['email']); ?></small>
                                <?php
                                if ($isLoggedIn){
                                    if ($_SESSION['user_id'] == $user['id']){ ?>
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-light">
                                            <img src="assets\img\edit.png" alt="Icon" style="width: 20px; height: 20px;">
                                        </a>
                                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-light">
                                            <img src="assets\img\close.png" alt="Icon" style="width: 20px; height: 20px;">
                                        </a>

                                    <?php }
                                } ?>

                                <!-- <button class="btn btn-light">
                                    <img src="assets\img\close.png" alt="Icon" style="width: 20px; height: 20px;">
                                </button>
                                <button class="btn btn-light">
                                    <img src="assets\img\edit.png" alt="Icon" style="width: 20px; height: 20px;">
                                </button> -->
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Aucun utilisateur trouvé.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
