<?php
session_start();
include('db.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect if not logged in
    exit;
}

// Get total number of customers
$customers_sql = "SELECT COUNT(*) as total_customers FROM customers";
$customers_result = $conn->query($customers_sql);
$customers_data = $customers_result->fetch_assoc();
$total_customers = $customers_data['total_customers'];

// Get total payments for the month
$payments_sql = "SELECT SUM(amount_paid) as total_payments FROM payments WHERE MONTH(payment_date) = MONTH(CURRENT_DATE())";
$payments_result = $conn->query($payments_sql);

// Check if the query was successful
if ($payments_result === false) {
    // If query fails, display the error message
    die("Error executing query: " . $conn->error);
}

// Fetch the results
$payments_data = $payments_result->fetch_assoc();

// If no data is found, set total_payments to 0
$total_payments = $payments_data ? $payments_data['total_payments'] : 0;

// Get total payments for the year (total amount received)
$yearly_payments_sql = "SELECT SUM(amount_paid) as total_yearly_payments FROM payments WHERE YEAR(payment_date) = YEAR(CURRENT_DATE())";
$yearly_payments_result = $conn->query($yearly_payments_sql);

// Check if the query was successful
if ($yearly_payments_result === false) {
    // If query fails, display the error message
    die("Error executing query: " . $conn->error);
}

// Fetch the results
$yearly_payments_data = $yearly_payments_result->fetch_assoc();

// If no data is found, set total_yearly_payments to 0
$total_yearly_payments = $yearly_payments_data ? $yearly_payments_data['total_yearly_payments'] : 0;


// Get total expenses for the month
$expenses_sql = "SELECT SUM(amount) as total_expenses FROM expenses WHERE MONTH(expense_date) = MONTH(CURRENT_DATE())";
$expenses_result = $conn->query($expenses_sql);

// Check if the query was successful
if ($expenses_result === false) {
    // If query fails, display the error message
    die("Error executing query: " . $conn->error);
}

// Fetch the results
$expenses_data = $expenses_result->fetch_assoc();

// If no data is found, set total_expenses to 0
$total_expenses = $expenses_data ? $expenses_data['total_expenses'] : 0;


// Get daily payments received
$daily_sql = "SELECT SUM(amount) as daily_payments FROM daily_transactions WHERE DATE(	transaction_datetime) = CURDATE()";
$daily_result = $conn->query($daily_sql);

// Check if the query was successful
if ($daily_result === false) {
    // If query fails, display the error message
    die("Error executing query: " . $conn->error);
}

// Fetch the results
$daily_data = $daily_result->fetch_assoc();

// If no data is found, set daily_payments to 0
$daily_payments = $daily_data ? $daily_data['daily_payments'] : 0;

// Assuming you've already fetched this data from your database
$months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
$monthly_payments = [];
$monthly_expenses = [];

// Loop through each month to get payments and expenses
for ($i = 1; $i <= 12; $i++) {
    // Query to get the total payments for the month
    $payments_sql = "SELECT SUM(amount) AS amount_paid FROM payments WHERE MONTH(payment_date) = $i AND YEAR(payment_date) = YEAR(CURRENT_DATE())";
    $payments_result = $conn->query($payments_sql);
    
    // Check if the query was successful and fetch the result
    if ($payments_result) {
        $payments_data = $payments_result->fetch_assoc();
        $monthly_payments[] = $payments_data && isset($payments_data['amount_paid']) ? (float)$payments_data['amount_paid'] : 0;
    } else {
        // Handle error if the query fails
        $monthly_payments[] = 0;
        // Optionally log the error or show a message
        // error_log("Error in payments query: " . $conn->error);
    }
    
    // Query to get the total expenses for the month
    $expenses_sql = "SELECT SUM(amount) AS total_expenses FROM expenses WHERE MONTH(expense_date) = $i AND YEAR(expense_date) = YEAR(CURRENT_DATE())";
    $expenses_result = $conn->query($expenses_sql);
    
    // Check if the query was successful and fetch the result
    if ($expenses_result) {
        $expenses_data = $expenses_result->fetch_assoc();
        $monthly_expenses[] = $expenses_data && isset($expenses_data['total_expenses']) ? (float)$expenses_data['total_expenses'] : 0;
    } else {
        // Handle error if the query fails
        $monthly_expenses[] = 0;
        // Optionally log the error or show a message
        // error_log("Error in expenses query: " . $conn->error);
    }
}

// Pass the data to JavaScript via JSON encoding
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect if not logged in
    exit;
}

// Fetch customer data
$customers_sql = "SELECT * FROM customers";
$customers_result = $conn->query($customers_sql);

// Check if the query was successful
if ($customers_result === false) {
    // If query fails, display the error message
    die("Error fetching customer data: " . $conn->error);
}

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_POST['customer_id'];
    $amount_paid = $_POST['amount_paid'];
    $payment_date = $_POST['payment_date'];

    // Insert payment data into the database
    $sql = "INSERT INTO payments (customer_id, amount_paid, payment_date) VALUES ('$customer_id', '$amount_paid', '$payment_date')";
    if ($conn->query($sql) === TRUE) {
        // Fetch customer email for sending email alerts
        $customer_query = $conn->query("SELECT email, name FROM customers WHERE id = '$customer_id'");
        $customer = $customer_query->fetch_assoc();
        $customer_email = $customer['email'];
        $customer_name = $customer['name'];

        echo "Payment recorded and notifications sent successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
}


// Fetch payments for the payments list
$payments = $conn->query("SELECT payments.*, customers.name AS customer_name, customers.email AS customer_email FROM payments JOIN customers ON payments.customer_id = customers.id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gym Management</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>/* General Styling */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f7fc;
    color: #333;
    margin: 0;
    padding: 0;
}

/* Sidebar */
.navbar {
    background-color: #2c3e50;
    color: #fff;
}

.nav-link {
    color: #fff;
    padding: 15px;
    font-size: 18px;
    transition: background-color 0.3s ease;
}

.nav-link:hover {
    background-color: #34495e;
    cursor: pointer;
}

.container-fluid {
    display: flex;
    flex-direction: row;
    height: 100vh; /* Full height to ensure the sidebar and content take full page */
}

.col-md-2 {
    background-color: #34495e;
    width: 200px; /* Fixed width for the sidebar */
    min-height: 100vh;
    padding-top: 20px;
    position: fixed; /* Make the sidebar fixed to the left */
    top: 0;
    left: 0;
}

.col-md-2 .nav-link {
    border-bottom: 1px solid #5d6d7e;
}

.col-md-2 .nav-link:last-child {
    border-bottom: none;
}

/* Main Content */
.col-md-10 {
    margin-left: 200px; /* Offset by sidebar width */
    padding: 20px;
    background-color: #fff;
    overflow-y: auto; /* Allow scrolling if content overflows */
}

/* Heading */
h1 {
    color: #2c3e50;
    font-size: 2.5rem;
    margin-bottom: 30px;
    text-transform: uppercase;
    letter-spacing: 2px;
}

/* Dashboard Cards */
.card {
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-10px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.card-header {
    font-size: 1.1rem;
    font-weight: bold;
    text-transform: uppercase;
    background-color: #1abc9c;
    border-bottom: 2px solid #16a085;
    color: #fff;
    padding: 15px;
    border-radius: 10px 10px 0 0;
}

.card-body {
    padding: 20px;
    text-align: center;
}

.card-title {
    font-size: 1.75rem;
    font-weight: bold;
    color: #fff;
}

/* Colors for different cards */
.bg-primary {
    background-color: #3498db !important;
}

.bg-success {
    background-color: #2ecc71 !important;
}

.bg-warning {
    background-color: #f39c12 !important;
}

.bg-info {
    background-color: #1abc9c !important;
}

/* Buttons */
button {
    background-color: #1abc9c;
    color: #fff;
    font-size: 16px;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #16a085;
}

/* Animations */
@keyframes fadeIn {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.container-fluid {
    animation: fadeIn 1s ease-out;
}

/* Responsive Design */
@media (max-width: 768px) {
    .col-md-2 {
        width: 100%;
        min-height: 0;
        position: static; /* Make sidebar static on small screens */
    }
    .col-md-10 {
        margin-left: 0;
    }
    .nav-link {
        font-size: 16px;
    }
    .card {
        margin-bottom: 20px;
    }
}

/* Icons for Gym-Related Elements */
.card-header i {
    font-size: 1.5rem;
    margin-right: 10px;
}

/* Example of Gym-Related Icons (Font Awesome or Material Icons can be used) */
.bg-primary .card-header i {
    content: url('https://img.icons8.com/ios/50/ffffff/weight.png'); /* Example: Gym icon */
}

.bg-success .card-header i {
    content: url('https://img.icons8.com/ios/50/ffffff/money-bag.png'); /* Payment icon */
}

.bg-warning .card-header i {
    content: url('https://img.icons8.com/ios/50/ffffff/expense.png'); /* Expense icon */
}

.bg-info .card-header i {
    content: url('https://img.icons8.com/ios/50/ffffff/dollar.png'); /* Daily transaction icon */
}
</style>
<body>

    <!-- Sidebar and Navbar -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2">
                <nav class="nav flex-column">
                    <a class="nav-link" href="index.php">Dashboard</a>
                    <a class="nav-link" href="customers.php">Customers</a>
                    <a class="nav-link" href="payments.php">Payments</a>
                    <a class="nav-link" href="expenses.php">Expenses</a>
                    <a class="nav-link" href="daily.php">Daily Transactions</a>
                    <a class="nav-link" href="logout.php">Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <h1 class="my-4">Gym Management Dashboard</h1>

                <!-- Dashboard Summary -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-header">Total Customers</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $total_customers; ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-header">Total Amount Received This Year:</div>
                            <div class="card-body">
                                <h5 class="card-title"> $<?php echo number_format($total_yearly_payments, 2); ?>
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-header">Total Payments (This Month)</div>
                            <div class="card-body">
                                <h5 class="card-title">$<?php echo number_format($total_payments, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-header">Total Expenses (This Month)</div>
                            <div class="card-body">
                                <h5 class="card-title">$<?php echo number_format($total_expenses, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-header">Daily Payments</div>
                            <div class="card-body">
                                <h5 class="card-title">$<?php echo number_format($daily_payments, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                </div>

     
 <div class="container">
        <h2>Customer Details</h2>
        <?php if ($customers_result->num_rows > 0): ?>
            <table>
                <thead>
                <tr>
                <th>Customer Name</th>
                <th>Customer Email</th>
                <th>Amount Paid</th>
                <th>Payment Date</th>
            </tr>
                </thead>
                <?php while ($row = $payments->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['customer_name']; ?></td>
                    <td><?php echo $row['customer_email']; ?></td>
                    <td><?php echo $row['amount_paid']; ?></td>
                    <td><?php echo $row['payment_date']; ?></td>
                </tr>
            <?php } ?>
            </table>
        <?php else: ?>
            <p>No customer records found.</p>
        <?php endif; ?>
    </div>
<!-- Chart Section -->
<div class="col-md-12">
    <h3>Financial Overview</h3>
    <p><i>(Monthly Payments vs Expenses)</i></p>
    <canvas id="financialChart"></canvas>
</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Sample Data - replace with dynamic PHP data or AJAX call to get actual values
    var paymentsData = <?php echo json_encode($monthly_payments); ?>;
    var expensesData = <?php echo json_encode($monthly_expenses); ?>;
    var months = <?php echo json_encode($months); ?>; // Example: ['January', 'February', ...]

    var ctx = document.getElementById('financialChart').getContext('2d');

    var financialChart = new Chart(ctx, {
        type: 'line', // Can also be 'line', 'pie', etc.
        data: {
            labels: months, // X-axis labels (months)
            datasets: [
                {
                    label: 'Payments',
                    data: paymentsData, // Y-axis data for payments
                    backgroundColor: 'rgba(52, 152, 219, 0.6)', // Blue for payments
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Expenses',
                    data: expensesData, // Y-axis data for expenses
                    backgroundColor: 'rgba(231, 76, 60, 0.6)', // Red for expenses
                    borderColor: 'rgba(231, 76, 60, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.dataset.label + ': $' + tooltipItem.raw.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2); // Format Y-axis values as currency
                        }
                    }
                }
            }
        }
    });
</script>


    </body>
</html>
