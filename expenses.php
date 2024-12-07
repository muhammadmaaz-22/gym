<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Insert expense data
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $expense_date = $_POST['expense_date'];

    $sql = "INSERT INTO expenses (category, amount, expense_date) VALUES ('$category', '$amount', '$expense_date')";
    if ($conn->query($sql) === TRUE) {
        echo "Expense recorded successfully";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch expenses
$expenses = $conn->query("SELECT * FROM expenses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Monthly Expenses</h1>
    <a href="index.php">Back to Dashboard</a>

    <h3>Record Expense</h3>
    <form method="POST">
        <input type="text" name="category" placeholder="Category" required>
        <input type="number" name="amount" placeholder="Amount" required>
        <input type="date" name="expense_date" required>
        <button type="submit">Record Expense</button>
    </form>

    <h3>Expense List</h3>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $expenses->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['category']; ?></td>
                    <td><?php echo $row['amount']; ?></td>
                    <td><?php echo $row['expense_date']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>
