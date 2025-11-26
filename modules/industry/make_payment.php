<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an industry user
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "industry"){
    header("location: ../auth/login.php");
    exit;
}

require_once "../../config/db.php";

// Get the logged-in industry user's ID
$user_id = $_SESSION["id"];
$industry = null;
$sql = "SELECT * FROM industries WHERE user_id = ?";
if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if(mysqli_num_rows($result) == 1){
        $industry = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}

// Handle payment form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["amount"])) {
    $amount = $_POST["amount"];
    $payment_date = $_POST["payment_date"];
    $payment_method = $_POST["payment_method"];
    $notes = $_POST["notes"];
    $account_name = $_POST["account_name"];
    $account_number = $_POST["account_number"];
    $status = 'unpaid';
    $sql = "INSERT INTO payments (industry_id, amount, payment_date, payment_method, notes, account_name, account_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "idssssss", $industry['id'], $amount, $payment_date, $payment_method, $notes, $account_name, $account_number, $status);
        if(mysqli_stmt_execute($stmt)){
            $_SESSION["success"] = "Payment recorded successfully.";
        } else {
            $_SESSION["error"] = "Error recording payment.";
        }
        mysqli_stmt_close($stmt);
    }
    header("location: make_payment.php");
    exit;
}

// Fetch payment history for this industry
$payments = [];
$sql = "SELECT * FROM payments WHERE industry_id = ? ORDER BY payment_date DESC";
if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $industry['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $payments[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Pre-fill account name/number from industry profile
$prefill_account_name = $industry['account_name'] ?? '';
$prefill_account_number = $industry['account_number'] ?? '';

include "../../includes/industry_header.php";
?>
<div class="container mt-5">
    <div class="card shadow mb-4">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-money-bill-wave"></i> Make Payment</h4>
        </div>
        <div class="card-body">
            <?php if(isset($_SESSION["success"])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION["success"]; unset($_SESSION["success"]); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if(isset($_SESSION["error"])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION["error"]; unset($_SESSION["error"]); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <form action="" method="post">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Amount (TZS)</label>
                        <input type="number" class="form-control" name="amount" step="0.01" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" class="form-control" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" name="payment_method" required>
                            <option value="bank">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="mobile">Mobile Money</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Notes</label>
                        <input type="text" class="form-control" name="notes">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Account Name</label>
                        <input type="text" class="form-control" name="account_name" value="<?php echo htmlspecialchars($prefill_account_name); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" class="form-control" name="account_number" value="<?php echo htmlspecialchars($prefill_account_number); ?>">
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg">Submit Payment</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0"><i class="fas fa-wallet"></i> Payment History</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Notes</th>
                            <th>Account Name</th>
                            <th>Account Number</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($payments) > 0): ?>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td>TZS <?php echo number_format($p['amount'],2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($p['payment_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($p['payment_method']); ?></td>
                                    <td><?php echo htmlspecialchars($p['notes']); ?></td>
                                    <td><?php echo htmlspecialchars($p['account_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($p['account_number'] ?? ''); ?></td>
                                    <td>
                                        <?php if(isset($p['status']) && $p['status'] == 'paid'): ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Unpaid</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No payments made yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include "../../includes/footer.php"; ?> 