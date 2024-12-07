<?php
session_start();
include('db.php'); // Include database connection

if (isset($_GET['query'])) {
    $query = $conn->real_escape_string($_GET['query']);
    
    $sql = "SELECT * FROM customers WHERE name LIKE '%$query%' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        echo json_encode($customer);
    } else {
        echo json_encode(['error' => 'Customer not found']);
    }
}
?>
