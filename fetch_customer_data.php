<?php
include('db.php');
$id = $_GET['id'] ?? '';
$result = $conn->query("SELECT id, name, email FROM payments WHERE id = '$id'");
$customer = $result->fetch_assoc();
echo json_encode($customer);
?>
