<?php
session_start();
include('db.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error_message = ''; // Initialize an empty error message

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prevent SQL Injection by using prepared statements
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password using password_verify() (hashed password)
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id']; // Store user ID in session
            $_SESSION['username'] = $user['username']; // Store username in session
            header("Location: index.php"); // Redirect to dashboard
            exit;
        } else {
            $error_message = "Invalid credentials."; // Incorrect password
        }
    } else {
        $error_message = "User not found."; // User does not exist
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="container">
    <h1>Login</h1>
    <?php 
    if (!empty($error_message)) { 
        echo "<p>$error_message</p>"; 
    }
    ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>
