<?php
include('db.php');
$search = $_GET['search'] ?? '';
$result = $conn->query("SELECT id, name, email FROM customers WHERE name LIKE '%$search%'");
$customers = [];
while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}
echo json_encode($customers);
?>
