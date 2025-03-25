<?php

require 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Validate ID
    if (!filter_var($id, FILTER_VALIDATE_INT)) {
        echo "Invalid user ID!";
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found!";
        exit;
    }
} else {
    echo "No user ID provided!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    
    $email = trim($_POST['email']);
    $description = trim($_POST['description']);

    // Validate inputs
    if (empty($firstname) || empty($lastname) || empty($email) || empty($description)) {
        echo "All fields are required!";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format!";
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET firstName = :firstName, lastName = :lastName, email = :email, description = :description WHERE id = :id");
    $stmt->execute([
        'firstName' => $firstname,
        'lastName' => $lastname,
        'email' => $email,
        'description' => $description,
        'id' => $id
    ]);

    header("Location: index.php");
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
</head>
<body>
    <h1>Edit User</h1>
    <form method="POST">
        <label for="firstname">firstname:</label>
        <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstName'], ENT_QUOTES, 'UTF-8'); ?>" required>
        <br>
        <label for="lastname">lastname:</label>
        <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastName'], ENT_QUOTES, 'UTF-8'); ?>" required>
        <br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
        <br>
        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($user['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
        <br>
        <button type="submit">Update</button>
    </form>
</body>
</html>