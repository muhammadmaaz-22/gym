<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');

// Ensure safe database query with sanitization
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Prepare and execute query
$stmt = $conn->prepare("SELECT id, name, email, amount FROM payments WHERE name LIKE CONCAT('%', ?, '%')");
$stmt->bind_param("s", $search_query);
$stmt->execute();
$result = $stmt->get_result();

// Generate dynamic table rows
if ($result->num_rows > 0) {
    while ($customer = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$customer['name']}</td>";
        echo "<td>{$customer['email']}</td>";
        echo "<td>{$customer['amount']}</td>";

        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='2'>No customer found</td></tr>";
}
?>
