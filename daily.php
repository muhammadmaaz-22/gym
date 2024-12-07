<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');

// Handle form submission to record a daily transaction
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    $amount = $_POST['amount'];
    $person_name = $_POST['person_name'];
    $transaction_datetime = date('Y-m-d H:i:s'); // Get current date and time

    // Insert daily transaction data into the database
    $sql = "INSERT INTO daily_transactions (description, amount, person_name, transaction_datetime) 
            VALUES ('$description', '$amount', '$person_name', '$transaction_datetime')";
    if ($conn->query($sql) === TRUE) {
        echo "Transaction recorded successfully";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch daily transactions
$daily_transactions = $conn->query("SELECT * FROM daily_transactions");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Transactions</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<style>
    /* General Page Styles */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f4f4;
    color: #333;
    margin: 0;
    padding: 0;
}

/* Container for all content */
.container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding-bottom: 40px;
}

/* Header */
h1 {
    font-size: 2.5em;
    color: #333;
    text-align: center;
    margin-bottom: 20px;
    font-weight: bold;
    letter-spacing: 1px;
}

h3 {
    font-size: 1.8em;
    color: #555;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 300;
}

/* Form and Inputs */
form {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    margin-bottom: 40px;
}

form input, form button {
    padding: 12px 20px;
    font-size: 1em;
    border: 1px solid #ddd;
    border-radius: 5px;
    width: 100%;
    max-width: 350px;
    margin: 5px 0;
}

form input[type="text"], form input[type="number"], form input[type="datetime-local"] {
    background-color: #fafafa;
    color: #333;
}

form input[type="text"]:focus, form input[type="number"]:focus, form input[type="datetime-local"]:focus {
    outline: none;
    border-color: #4CAF50;
    background-color: #fff;
}

form button {
    background-color: #4CAF50;
    color: white;
    font-weight: bold;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form button:hover {
    background-color: #45a049;
}

/* Transaction Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 40px;
}

table th, table td {
    padding: 12px 15px;
    text-align: left;
    font-size: 1em;
    color: #333;
}

table th {
    background-color: #4CAF50;
    color: white;
    text-transform: uppercase;
    letter-spacing: 1px;
}

table tr:nth-child(even) {
    background-color: #f2f2f2;
}

table tr:hover {
    background-color: #f1f1f1;
    cursor: pointer;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 15px;
        margin: 20px;
    }

    form input, form button {
        width: 100%;
    }

    h1 {
        font-size: 2em;
    }

    h3 {
        font-size: 1.5em;
    }
}

/* Footer Links */
a {
    text-decoration: none;
    color: #4CAF50;
    font-weight: bold;
    transition: color 0.3s ease;
}

a:hover {
    color: #45a049;
}

</style>
<body>

<div class="container">
    <h1>Daily Transactions</h1>
    <a href="index.php">Back to Dashboard</a>

    <h3>Record Transaction</h3>
    <form method="POST">
    <input type="text" name="person_name" placeholder="Person's Name" required>

        <input type="number" name="amount" placeholder="Amount" required>
        <input type="datetime-local" name="transaction_datetime" required>
        <button type="submit">Record Transaction</button>
    </form>

    <h3>Transaction List</h3>
    <table>
        <thead>
            <tr>
            <th>Person Name</th>

                <th>Amount</th>
                <th>Transaction Date and Time</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $daily_transactions->fetch_assoc()) { ?>
                <tr>
                <td><?php echo $row['person_name']; ?></td>
                    <td><?php echo $row['amount']; ?></td>

                    <td><?php echo $row['transaction_datetime']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>
