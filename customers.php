<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');

// Handle form submission to add a new customer
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $membership_start = $_POST['membership_start'];
    $membership_end = $_POST['membership_end'];
    $amount_paid = $_POST['amount_paid'];
    $payment_status = $_POST['payment_status'];

    // Insert customer data into the database
    $sql = "INSERT INTO customers (name, email, phone, membership_start, membership_end, amount_paid, payment_status) 
            VALUES ('$name', '$email', '$phone', '$membership_start', '$membership_end', '$amount_paid', '$payment_status')";
    if ($conn->query($sql) === TRUE) {
        echo "New customer added successfully";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch customers from the database
$customers = $conn->query("SELECT * FROM customers");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Manage Customers</h1>
    <a href="index.php">Back to Dashboard</a>

    <h3>Add Customer</h3>
    <form method="POST">
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone" required>
        <input type="date" name="membership_start" placeholder="Membership Start Date" required>
        <input type="date" name="membership_end" placeholder="Membership End Date" required>
        <input type="text" name="amount_paid" placeholder="Amount Paid" required>
        <select name="payment_status" required>
            <option value="paid">Paid</option>
            <option value="unpaid">Unpaid</option>
        </select>
        <button type="submit">Add Customer</button>
    </form>

    <h3>Customer List</h3>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Membership Start</th>
                <th>Membership End</th>
                <th>Amount Paid</th>
                <th>Payment Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $customers->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['phone']; ?></td>
                    <td><?php echo $row['membership_start']; ?></td>
                    <td><?php echo $row['membership_end']; ?></td>
                    <td><?php echo $row['amount_paid']; ?></td>
                    <td><?php echo $row['payment_status']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>
