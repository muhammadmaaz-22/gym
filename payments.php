<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');
include('mail_config.php'); // Include your email configuration file

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['amount_paid'])) {
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

        // Admin email
        $admin_email = "admin@example.com"; // Replace with your admin email

        // Prepare email content
        $subject = "Payment Received: $amount_paid";
        $message = "Dear $customer_name,\n\nWe have successfully received your payment of $amount_paid on $payment_date.\n\nThank you for your payment.\n\nBest regards,\nYour Company";

        // Send email to customer
        mail($customer_email, $subject, $message, "From: no-reply@example.com");

        // Send email to admin
        $admin_subject = "New Payment Received from $customer_name";
        $admin_message = "A payment of $amount_paid has been received from $customer_name on $payment_date.\nCustomer Email: $customer_email";

        mail($admin_email, $admin_subject, $admin_message, "From: no-reply@example.com");

        echo "Payment recorded and notifications sent successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Handle customer data retrieval for the search functionality
if (isset($_GET['customer_name'])) {
    $search = $_GET['customer_name'];
    $customer_data = $conn->query("SELECT * FROM customers WHERE name LIKE '%$search%' LIMIT 10");
    $customers = $customer_data->fetch_all(MYSQLI_ASSOC);
    echo json_encode($customers);
    exit;
}

// Fetch all customers
$customers = $conn->query("SELECT id, name, email FROM customers");

// Fetch payments for the payments list
$payments = $conn->query("SELECT payments.*, customers.name AS customer_name, customers.email AS customer_email FROM payments JOIN customers ON payments.customer_id = customers.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments</title>
    <link rel="stylesheet" href="style.css">
    <style>
/* Fancy Search Container */
.fancy-search-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 30px;
    background: linear-gradient(135deg, #1e293b, #3b82f6);
    border-radius: 15px;
    color: #fff;
    box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
    max-width: 600px;
    margin: auto;
    font-family: 'Poppins', sans-serif;
}

/* Heading */
.fancy-heading {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #f0f8ff;
    text-shadow: 0px 4px 10px rgba(255, 255, 255, 0.2);
}

/* Search Box */
.fancy-search-box {
    position: relative;
    width: 100%;
}

/* Input Field */
.fancy-input {
    width: 100%;
    padding: 15px;
    font-size: 18px;
    border: none;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    transition: all 0.3s ease-in-out;
    box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
}

.fancy-input:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.2);
    box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3), 0px 0px 10px rgba(255, 255, 255, 0.5);
}

/* Search Results Dropdown */
.fancy-search-results {
    position: absolute;
    top: 110%;
    left: 0;
    width: 100%;
    background: rgba(0, 0, 0, 0.8);
    border-radius: 8px;
    box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.3);
    max-height: 300px;
    overflow-y: auto;
    z-index: 10;
    display: none;
}

.fancy-search-results div {
    padding: 12px 15px;
    font-size: 16px;
    color: #cbd5e1;
    cursor: pointer;
    transition: background 0.3s ease, color 0.3s ease;
}

.fancy-search-results div:hover {
    background: #1d4ed8;
    color: #f0f8ff;
}

/* Customer Details */
.fancy-customer-details {
    display: none;
    margin-top: 20px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
    color: #fff;
    max-width: 500px;
    width: 100%;
}

.fancy-customer-details h4 {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 15px;
    color: #e2e8f0;
}

.fancy-customer-details p {
    font-size: 16px;
    margin: 5px 0;
    color: #cbd5e1;
}

.fancy-customer-details button {
    margin-top: 15px;
    padding: 12px 20px;
    background: #3b82f6;
    color: #fff;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.fancy-customer-details button:hover {
    background: #2563eb;
background-color: #0056b3;}

        #customerSearchResults {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ccc;
            display: none;
            position: absolute;
            background: #fff;
            width: 100%;
        }
        #customerSearchResults div {
            padding: 8px;
            cursor: pointer;
        }
        #customerSearchResults div:hover {
            background: #f0f0f0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Payments Management</h1>
    <a href="index.php">Back to Dashboard</a>
    <div class="fancy-search-container">
    <h3 class="fancy-heading">🔍 Search for Customer</h3>
    <div class="fancy-search-box">
        <input type="text" id="customerSearch" class="fancy-input" placeholder="Enter customer name..." autocomplete="off">
        <div id="customerSearchResults" class="fancy-search-results"></div>
    </div>
    <div id="customerDetails" class="fancy-customer-details"></div>
</div>

    <h3>Record Payment</h3>
    <form method="POST">
        <select name="customer_id" required>
            <option value="">Select Customer</option>
            <?php while ($customer = $customers->fetch_assoc()) { ?>
                <option value="<?php echo $customer['id']; ?>">
                    <?php echo $customer['name']; ?> (<?php echo $customer['email']; ?>)
                </option>
            <?php } ?>
        </select>
        <input type="number" name="amount_paid" placeholder="Amount Paid" required>
        <input type="date" name="payment_date" required>
        <button type="submit">Record Payment</button>
    </form>

    <h3>Payments List</h3>
    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Customer Email</th>
                <th>Amount Paid</th>
                <th>Payment Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $payments->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['customer_name']; ?></td>
                    <td><?php echo $row['customer_email']; ?></td>
                    <td><?php echo $row['amount_paid']; ?></td>
                    <td><?php echo $row['payment_date']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
    document.getElementById('customerSearch').addEventListener('input', function () {
        const query = this.value;
        if (query.length < 1) {
            document.getElementById('customerSearchResults').style.display = 'none';
            return;
        }

        fetch(`payments.php?customer_name=${query}`)
            .then(response => response.json())
            .then(data => {
                const resultsDiv = document.getElementById('customerSearchResults');
                resultsDiv.innerHTML = '';
                resultsDiv.style.display = 'block';
                data.forEach(customer => {
                    const div = document.createElement('div');
                    div.textContent = `${customer.name} (${customer.email})`;
                    div.dataset.customerId = customer.id;
                    div.addEventListener('click', () => {
                        document.getElementById('customerSearch').value = customer.name;
                        resultsDiv.style.display = 'none';
                        showCustomerDetails(customer);
                    });
                    resultsDiv.appendChild(div);
                });
            });
    });
    document.getElementById('customerSearch').addEventListener('input', function () {
    const query = this.value;
    const resultsDiv = document.getElementById('customerSearchResults');

    if (query.length < 1) {
        resultsDiv.style.display = 'none';
        return;
    }

    fetch(`payments.php?customer_name=${query}`)
        .then(response => response.json())
        .then(data => {
            resultsDiv.innerHTML = '';
            resultsDiv.style.display = 'block';

            if (data.length === 0) {
                resultsDiv.innerHTML = `<div>No results found.</div>`;
            } else {
                data.forEach(customer => {
                    const div = document.createElement('div');
                    div.textContent = `${customer.name} (${customer.email})`;
                    div.dataset.customerId = customer.id;
                    div.addEventListener('click', () => {
                        document.getElementById('customerSearch').value = customer.name;
                        resultsDiv.style.display = 'none';
                        showCustomerDetails(customer);
                    });
                    resultsDiv.appendChild(div);
                });
            }
        })
        .catch(error => {
            console.error("Error fetching customer data:", error);
            resultsDiv.innerHTML = `<div>Error loading results.</div>`;
        });
});

function showCustomerDetails(customer) {
    const detailsDiv = document.getElementById('customerDetails');
    detailsDiv.innerHTML = `
        <h4>Customer Details</h4>
        <p><strong>Name:</strong> ${customer.name}</p>
        <p><strong>Email:</strong> ${customer.email}</p>
        <p><strong>Phone:</strong> ${customer.phone || 'N/A'}</p>
        <p><strong>amount:</strong> ${customer.amount || 'N/A'}</p>

        <button onclick="editCustomer(${customer.id})">Edit Customer</button>
    `;
    detailsDiv.style.display = 'block';
}

function editCustomer(customerId) {
    alert(`Edit functionality for customer ID ${customerId} is not yet implemented.`);
}


    function editCustomer(customerId) {
        // Implement the edit customer functionality
        alert('Edit functionality for customer ' + customerId);
    }
</script>

</body>
</html>
