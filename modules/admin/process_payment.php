<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../index.php');
    exit();
}

$success_message = $error_message = '';

// Process payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $farmer_id = $_POST['farmer_id'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $notes = $_POST['notes'];
    
    // Validate input
    if (empty($farmer_id) || empty($amount) || empty($payment_method)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Insert payment record
            $sql = "INSERT INTO payments (farmer_id, amount, payment_date, payment_method, status, notes) 
                    VALUES (?, ?, NOW(), ?, 'completed', ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "idss", $farmer_id, $amount, $payment_method, $notes);
            mysqli_stmt_execute($stmt);
            
            // Update farmer's last payment date
            $sql = "UPDATE farmers SET last_payment_date = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $farmer_id);
            mysqli_stmt_execute($stmt);
            
            // Commit transaction
            mysqli_commit($conn);
            $success_message = "Payment processed successfully!";
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_message = "Error processing payment: " . $e->getMessage();
        }
    }
}

// Get all farmers with pending payments
$sql = "SELECT f.*, 
        COALESCE(SUM(CASE WHEN p.status = 'pending' THEN p.amount ELSE 0 END), 0) as pending_amount
        FROM farmers f
        LEFT JOIN payments p ON f.id = p.farmer_id
        GROUP BY f.id
        HAVING pending_amount > 0
        ORDER BY f.full_name";
$result = mysqli_query($conn, $sql);
$farmers = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Payments - Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container mt-4">
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Payment Form -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Process Payment</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="farmer_id" class="form-label">Select Farmer</label>
                                <select class="form-select" id="farmer_id" name="farmer_id" required>
                                    <option value="">Choose a farmer...</option>
                                    <?php foreach ($farmers as $farmer): ?>
                                        <option value="<?php echo $farmer['id']; ?>">
                                            <?php echo htmlspecialchars($farmer['full_name']); ?> 
                                            (Pending: TZS <?php echo number_format($farmer['pending_amount'], 2); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (TZS)</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                            </div>

                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="mobile_money">Mobile Money</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>

                            <button type="submit" name="process_payment" class="btn btn-primary">
                                Process Payment
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Recent Payments</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Farmer</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT p.*, f.full_name 
                                           FROM payments p 
                                           JOIN farmers f ON p.farmer_id = f.id 
                                           ORDER BY p.payment_date DESC 
                                           LIMIT 10";
                                    $result = mysqli_query($conn, $sql);
                                    while ($payment = mysqli_fetch_assoc($result)):
                                    ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d', strtotime($payment['payment_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($payment['full_name']); ?></td>
                                        <td>TZS <?php echo number_format($payment['amount'], 2); ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $payment['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-fill amount based on selected farmer's pending amount
        document.getElementById('farmer_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const pendingAmount = selectedOption.text.match(/Pending: TZS ([\d,]+\.\d{2})/);
            if (pendingAmount) {
                document.getElementById('amount').value = pendingAmount[1].replace(/,/g, '');
            }
        });
    </script>
</body>
</html> 