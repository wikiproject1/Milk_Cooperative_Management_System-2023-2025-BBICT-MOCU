<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an industry user
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "industry"){
    header("location: ../auth/login.php");
    exit;
}

// Include config file
require_once "../../config/db.php";

// Define variables and initialize with empty values
$delivery_date = $quantity = "";
$delivery_date_err = $quantity_err = "";

// Get the logged-in industry user's ID
$user_id = $_SESSION["id"];

// Fetch industry details based on the user_id
$sql = "SELECT * FROM industries WHERE user_id = ?";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        if(mysqli_num_rows($result) == 1){
            $industry = mysqli_fetch_assoc($result);
        } else{
            // Industry not found for this user
            echo "Error: Industry details not linked to this user account.";
            exit;
        }
    } else{
        echo "Oops! Something went wrong fetching industry details.";
        exit;
    }
    mysqli_stmt_close($stmt);
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate required date
    if (!isset($_POST["delivery_date"]) || empty($_POST["delivery_date"])) {
        $delivery_date_err = "Please enter the required date.";
    } else {
        $delivery_date = trim($_POST["delivery_date"]);
    }

    // Validate quantity
    if (!isset($_POST["quantity"]) || empty(trim($_POST["quantity"]))) {
        $quantity_err = "Please enter the quantity in liters.";
    } elseif(!is_numeric($_POST["quantity"]) || $_POST["quantity"] <= 0){
        $quantity_err = "Quantity must be a positive number.";
    } else{
        $quantity = trim($_POST["quantity"]);
    }

    // Check input errors before inserting in database
    if(empty($delivery_date_err) && empty($quantity_err)){

        // Prepare an insert statement
        $sql = "INSERT INTO orders (industry_id, delivery_date, quantity) VALUES (?, ?, ?)";

        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "isd", $param_industry_id, $param_delivery_date, $param_quantity);

            // Set parameters
            $param_industry_id = $industry['id'];
            $param_delivery_date = $delivery_date;
            $param_quantity = $quantity;

            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Order placed successfully
                $_SESSION["order_placement_success"] = "Order placed successfully!";
                 header("location: place_order.php"); // Redirect to clear form
                 exit();
            } else{
                $_SESSION["error"] = "Error placing order: " . mysqli_error($conn);
                 header("location: place_order.php"); // Redirect to show error
                 exit();
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}

// Fetch recent orders for this industry
$orders = [];
if ($industry) {
    $sql = "SELECT * FROM orders WHERE industry_id = ? ORDER BY order_date DESC LIMIT 5";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $industry['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<?php include "../../includes/industry_header.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order - Milk Cooperative System (Industry Portal)</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.css">
    <style>
        :root {
            --primary-color: #6C63FF; /* Blue/Purple */
            --secondary-color: #4CAF50; /* Green */
            --accent-color: #FF6B6B; /* Red */
            --background-color: #F8F9FA; /* Light grey */
            --card-bg: #FFFFFF; /* White */
            --text-primary: #2D3436; /* Dark grey */
            --text-secondary: #636E72; /* Medium grey */
        }

        body {
            background-color: var(--background-color);
            color: var(--text-primary);
            padding-top: 80px; /* Adjust if using a fixed header */
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #8B7FFF); /* Blue/Purple gradient */
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(108, 99, 255, 0.2); /* Blue/Purple shadow */
        }

        .form-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 500;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 2px solid #E9ECEF;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(108, 99, 255, 0.25); /* Blue/Purple shadow */
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #8B7FFF); /* Blue/Purple gradient */
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3); /* Blue/Purple shadow */
        }

        .invalid-feedback {
            color: var(--accent-color); /* Red */
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #E9ECEF;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-title {
            color: var(--primary-color); /* Blue/Purple */
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <h1 class="display-4">Place Milk Order</h1>
            <p class="lead">Submit a new milk order</p>
        </div>
    </div>

    <main class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-card">
                    <?php if(isset($_SESSION["order_placement_success"])): ?>
                        <script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: '<?php echo $_SESSION['order_placement_success']; ?>',
                                confirmButtonText: 'OK'
                            });
                            <?php unset($_SESSION['order_placement_success']); ?>
                        </script>
                    <?php endif; ?>

                    <?php if(isset($_SESSION["error"])): ?>
                         <script>
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: '<?php echo $_SESSION['error']; ?>',
                                confirmButtonText: 'OK'
                            });
                            <?php unset($_SESSION['error']); ?>
                        </script>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-section">
                            <h3 class="section-title">Order Details</h3>
                            <div class="mb-3">
                                <label for="delivery_date" class="form-label">Required Delivery Date</label>
                                <input type="date" name="delivery_date" id="delivery_date" class="form-control <?php echo (!empty($delivery_date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $delivery_date; ?>" required>
                                <span class="invalid-feedback"><?php echo $delivery_date_err; ?></span>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity (Liters)</label>
                                <input type="number" name="quantity" id="quantity" class="form-control <?php echo (!empty($quantity_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $quantity; ?>" min="1" step="0.01" required>
                                <span class="invalid-feedback"><?php echo $quantity_err; ?></span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Place Order</button>
                            <!-- Add a link back to industry dashboard -->
                            <!-- <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a> -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Recent Orders</h5>
            <a href="order_history.php" class="btn btn-outline-primary btn-sm">View Full Order History</a>
        </div>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order Date</th>
                                <th>Required Delivery Date</th>
                                <th>Quantity (Liters)</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($orders) > 0): ?>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($o['order_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($o['delivery_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($o['quantity']); ?></td>
                                        <td>
                                            <?php if(isset($o['status'])): ?>
                                                <span class="badge bg-<?php echo $o['status'] == 'delivered' ? 'success' : ($o['status'] == 'approved' ? 'info' : ($o['status'] == 'pending' ? 'warning text-dark' : 'secondary')); ?>">
                                                    <?php echo ucfirst($o['status']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($o['notes'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center">No recent orders found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?php include "../../includes/footer.php"; ?>
<?php if (isset($conn) && mysqli_ping($conn)) { mysqli_close($conn); } ?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.js"></script>
    <!-- Custom JS -->
    <!-- <script src="../../assets/js/main.js"></script> -->

</body>
</html> 