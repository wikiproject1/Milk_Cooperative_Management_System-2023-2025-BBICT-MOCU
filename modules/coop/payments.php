<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../auth/login.php");
    exit;
}

// Include config file
require_once "../../config/db.php";

// Fetch Coop account balance
$coop_balance = 0;
$coop_account = mysqli_query($conn, "SELECT balance FROM coop_account LIMIT 1");
if ($coop_account && $row = mysqli_fetch_assoc($coop_account)) {
    $coop_balance = $row['balance'];
}
// Fetch Coop transaction history
$coop_transactions = mysqli_query($conn, "SELECT * FROM coop_transactions ORDER BY created_at DESC LIMIT 20");

// Process payment gateway simulation (demo mode)
if(isset($_POST['pay_now_id'])) {
    $payment_id = intval($_POST['pay_now_id']);
    // Only update status to paid
    $sql = "UPDATE payments SET status = 'paid' WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $payment_id);
        if(mysqli_stmt_execute($stmt)){
            $_SESSION["success"] = "Payment sent successfully (demo mode).";
        } else {
            $_SESSION["error"] = "Error updating payment status.";
        }
        mysqli_stmt_close($stmt);
    }
    header("location: payments.php");
    exit;
}

// Process form submission for new payment
if(isset($_POST["recipient_id"], $_POST["recipient_type"], $_POST["amount"], $_POST["payment_date"], $_POST["payment_method"], $_POST["notes"])){
    $recipient_id = $_POST["recipient_id"];
    $recipient_type = $_POST["recipient_type"];
    $amount = $_POST["amount"];
    $payment_date = $_POST["payment_date"];
    $payment_method = $_POST["payment_method"];
    $notes = $_POST["notes"];
    $account_name = $_POST["account_name"] ?? '';
    $account_number = $_POST["account_number"] ?? '';
    if ($recipient_type === 'farmer') {
    $sql = "INSERT INTO payments (farmer_id, amount, payment_date, payment_method, notes, account_name, account_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "sdsssss", $recipient_id, $amount, $payment_date, $payment_method, $notes, $account_name, $account_number);
            if(mysqli_stmt_execute($stmt)){
                $_SESSION["success"] = "Payment recorded successfully.";
            } else {
                $_SESSION["error"] = "Error recording payment.";
            }
            mysqli_stmt_close($stmt);
        }
    } elseif ($recipient_type === 'industry') {
        $sql = "INSERT INTO payments (industry_id, amount, payment_date, payment_method, notes, account_name, account_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "sdsssss", $recipient_id, $amount, $payment_date, $payment_method, $notes, $account_name, $account_number);
        if(mysqli_stmt_execute($stmt)){
            $_SESSION["success"] = "Payment recorded successfully.";
        } else {
            $_SESSION["error"] = "Error recording payment.";
        }
        mysqli_stmt_close($stmt);
        }
    }
    header("location: payments.php");
    exit;
}

// Get all farmers for the dropdown
$sql = "SELECT farmer_id, full_name FROM farmers ORDER BY full_name";
$farmers = mysqli_query($conn, $sql);

// Get recent payments
$sql = "SELECT p.*, f.full_name as farmer_name 
        FROM payments p 
        JOIN farmers f ON p.farmer_id = f.farmer_id 
        ORDER BY p.payment_date DESC";
$recent_payments = mysqli_query($conn, $sql);

// Get monthly payment statistics
$sql = "SELECT 
            COALESCE(SUM(amount), 0) as total_amount,
            COUNT(*) as total_payments
        FROM payments 
        WHERE MONTH(payment_date) = MONTH(CURRENT_DATE()) 
        AND YEAR(payment_date) = YEAR(CURRENT_DATE())";
$monthly_stats = mysqli_query($conn, $sql)->fetch_assoc();

// Fetch all industries and farmers for mapping
$industries_map = [];
$res = mysqli_query($conn, "SELECT id, company_name FROM industries");
while($row = mysqli_fetch_assoc($res)) {
    $industries_map[$row['id']] = $row['company_name'];
}
$farmers_map = [];
$res = mysqli_query($conn, "SELECT farmer_id, full_name FROM farmers");
while($row = mysqli_fetch_assoc($res)) {
    $farmers_map[$row['farmer_id']] = $row['full_name'];
}
// Fetch industry payments
$industry_payments = mysqli_query($conn, "SELECT * FROM payments WHERE industry_id IS NOT NULL ORDER BY payment_date DESC");
// Fetch farmer payments
$farmer_payments = mysqli_query($conn, "SELECT * FROM payments WHERE farmer_id IS NOT NULL ORDER BY payment_date DESC");

// Handle industry payment receive/reject actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['industry_payment_action'], $_POST['payment_id'])) {
    $payment_id = intval($_POST['payment_id']);
    $action = $_POST['industry_payment_action'];
    if ($action === 'receive') {
        // Mark as paid
        $sql = "UPDATE payments SET status = 'paid' WHERE id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $payment_id);
            if(mysqli_stmt_execute($stmt)){
                // Add to coop account
                $amount_res = mysqli_query($conn, "SELECT amount FROM payments WHERE id = $payment_id");
                $amount_row = mysqli_fetch_assoc($amount_res);
                $amount = $amount_row['amount'];
                mysqli_query($conn, "UPDATE coop_account SET balance = balance + $amount WHERE id = 1");
                // Log transaction
                $desc = "Industry payment received (Payment ID: $payment_id)";
                mysqli_query($conn, "INSERT INTO coop_transactions (type, amount, description, related_id) VALUES ('credit', $amount, '" . mysqli_real_escape_string($conn, $desc) . "', $payment_id)");
                $_SESSION["success"] = "Payment received and added to Coop account.";
            } else {
                $_SESSION["error"] = "Error receiving payment.";
            }
            mysqli_stmt_close($stmt);
        }
    } elseif ($action === 'reject') {
        $sql = "UPDATE payments SET status = 'rejected' WHERE id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $payment_id);
            if(mysqli_stmt_execute($stmt)){
                $_SESSION["success"] = "Payment rejected.";
            } else {
                $_SESSION["error"] = "Error rejecting payment.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    header("location: payments.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        .payment-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .payment-method {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .method-bank {
            background-color: #e3f2fd;
            color: #1976d2;
            border: 1px solid #1976d2;
        }
        
        .method-cash {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #2e7d32;
        }
        
        .method-mobile {
            background-color: #fff3e0;
            color: #f57c00;
            border: 1px solid #f57c00;
        }
    </style>
</head>
<body>
    <?php include "../../includes/header.php"; ?>
    
    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Payments</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal">
                <i class="fas fa-plus"></i> New Payment
            </button>
        </div>

        <?php if(isset($_SESSION["success"])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                    echo $_SESSION["success"];
                    unset($_SESSION["success"]);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION["error"])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                    echo $_SESSION["error"];
                    unset($_SESSION["error"]);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Monthly Statistics -->
        <div class="row">
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="stat-value">TZS <?php echo number_format($monthly_stats['total_amount'], 2); ?></div>
                    <div class="stat-label">Total Payments This Month</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($monthly_stats['total_payments']); ?></div>
                    <div class="stat-label">Number of Payments This Month</div>
                </div>
            </div>
        </div>

        <!-- Coop Account Balance -->
        <div class="stat-card mb-4">
            <div class="stat-value">Coop Account Balance: TZS <?php echo number_format($coop_balance, 2); ?></div>
            <div class="stat-label">This is your demo wallet for all payments and sales.</div>
        </div>

        <!-- Coop Transaction History -->
        <div class="payment-card mb-4">
            <h2 class="mb-4">Coop Transaction History</h2>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($coop_transactions && mysqli_num_rows($coop_transactions) > 0): ?>
                            <?php while($txn = mysqli_fetch_assoc($coop_transactions)): ?>
                                <tr>
                                    <td><?php echo $txn['created_at']; ?></td>
                                    <td><span class="badge bg-<?php echo $txn['type'] == 'credit' ? 'success' : 'danger'; ?>"><?php echo ucfirst($txn['type']); ?></span></td>
                                    <td><?php echo number_format($txn['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($txn['description']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">No transactions yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="payment-card">
            <h2 class="mb-4">Recent Payments</h2>
            <?php if(mysqli_num_rows($recent_payments) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Farmer</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Notes</th>
                                <th>Status</th>
                                <th>Account Name</th>
                                <th>Account Number</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($payment = mysqli_fetch_assoc($recent_payments)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['farmer_name']); ?></td>
                                    <td>TZS <?php echo number_format($payment['amount'], 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                    <td>
                                        <span class="payment-method method-<?php echo strtolower($payment['payment_method']); ?>">
                                            <?php echo htmlspecialchars($payment['payment_method']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($payment['notes']); ?></td>
                                    <td>
                                        <?php if(isset($payment['status']) && $payment['status'] == 'paid'): ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Unpaid</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($payment['account_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($payment['account_number'] ?? ''); ?></td>
                                    <td>
                                        <?php if(!isset($payment['status']) || $payment['status'] != 'paid'): ?>
                                            <button type="button" class="btn btn-success btn-sm pay-now-btn" 
                                                data-id="<?php echo $payment['id']; ?>"
                                                data-farmer="<?php echo htmlspecialchars($payment['farmer_name']); ?>"
                                                data-amount="<?php echo number_format($payment['amount'], 2); ?>"
                                                data-method="<?php echo htmlspecialchars($payment['payment_method']); ?>"
                                                data-notes="<?php echo htmlspecialchars($payment['notes']); ?>"
                                                data-date="<?php echo date('M d, Y', strtotime($payment['payment_date'])); ?>"
                                                data-recipient-type="<?php echo $payment['farmer_id'] ? 'farmer' : 'industry'; ?>"
                                                data-recipient-id="<?php echo $payment['farmer_id'] ? $payment['farmer_id'] : $payment['industry_id']; ?>"
                                                data-account-name="<?php echo htmlspecialchars($payment['account_name'] ?? ''); ?>"
                                                data-account-number="<?php echo htmlspecialchars($payment['account_number'] ?? ''); ?>">
                                                <i class="fas fa-bolt"></i> Pay Now
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">--</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No payments recorded yet.</div>
            <?php endif; ?>
        </div>

        <!-- New Payment Modal -->
        <div class="modal fade" id="paymentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Record New Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Recipient</label>
                                <input type="text" class="form-control" id="recipient_search" placeholder="Search Farmer or Industry by name or ID..." autocomplete="off" required>
                                <div id="recipient_results" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                                <input type="hidden" name="recipient_id" id="recipient_id" required>
                                <input type="hidden" name="recipient_type" id="recipient_type" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Amount (TZS)</label>
                                <input type="number" class="form-control" name="amount" step="0.01" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Payment Date</label>
                                <input type="date" class="form-control" name="payment_date" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select class="form-select" name="payment_method" id="payment_method" required>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="cash">Cash</option>
                                    <option value="mobile">Mobile Money</option>
                                </select>
                            </div>
                            
                            <div class="mb-3" id="mobile_provider_group" style="display:none;">
                                <label class="form-label">Mobile Provider</label>
                                <select class="form-select" name="mobile_provider" id="mobile_provider">
                                    <option value="">Select Provider</option>
                                    <option value="mpesa">M-Pesa</option>
                                    <option value="halopesa">HaloPesa</option>
                                    <option value="tigopesa">TigoPesa</option>
                                    <option value="airtelmoney">Airtel Money</option>
                                    <option value="tpesa">TPesa</option>
                                </select>
                            </div>
                            <div class="mb-3" id="bank_provider_group" style="display:none;">
                                <label class="form-label">Bank Name</label>
                                <select class="form-select" name="bank_provider" id="bank_provider">
                                    <option value="">Select Bank</option>
                                    <option value="crdb">CRDB</option>
                                    <option value="nmb">NMB</option>
                                    <option value="nbc">NBC</option>
                                    <option value="dtb">DTB</option>
                                    <option value="stanbic">Stanbic</option>
                                    <option value="exim">Exim</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Account Name</label>
                                <input type="text" class="form-control" name="account_name" placeholder="Enter account name (optional)">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Account Number</label>
                                <input type="text" class="form-control" name="account_number" placeholder="Enter account number (optional)">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Record Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Pay Now Modal -->
        <div class="modal fade" id="payNowModal" tabindex="-1" aria-labelledby="payNowModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="payNowModalLabel">Process Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <p><strong>Recipient:</strong> <span id="modalFarmer"></span></p>
                <p><strong>Amount:</strong> TZS <span id="modalAmount"></span></p>
                <p><strong>Date:</strong> <span id="modalDate"></span></p>
                <p><strong>Method:</strong> <span id="modalMethod"></span></p>
                <p><strong>Notes:</strong> <span id="modalNotes"></span></p>
                <p><strong>Account Name:</strong> <span id="modalAccountName"></span></p>
                <p><strong>Account Number:</strong> <span id="modalAccountNumber"></span></p>
                <form id="payNowForm" method="post" style="display:none;">
                  <input type="hidden" name="pay_now_id" id="modalPayId">
                  <input type="hidden" name="recipient_type" id="modalRecipientType">
                  <input type="hidden" name="recipient_id" id="modalRecipientId">
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmPayBtn"><i class="fas fa-bolt"></i> Confirm & Process Payment</button>
              </div>
            </div>
          </div>
        </div>

        <!-- In the HTML, add Bootstrap tabs for Industry and Farmer Payments -->
        <ul class="nav nav-tabs mb-4" id="paymentTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="industry-tab" data-bs-toggle="tab" data-bs-target="#industry" type="button" role="tab">Industry Payments</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="farmer-tab" data-bs-toggle="tab" data-bs-target="#farmer" type="button" role="tab">Farmer Payments</button>
          </li>
        </ul>
        <div class="tab-content" id="paymentTabsContent">
          <div class="tab-pane fade show active" id="industry" role="tabpanel">
            <div class="payment-card">
              <h2 class="mb-4">Industry Payments (Received)</h2>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Industry</th>
                      <th>Amount</th>
                      <th>Date</th>
                      <th>Method</th>
                      <th>Notes</th>
                      <th>Status</th>
                      <th>Account Name</th>
                      <th>Account Number</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while($payment = mysqli_fetch_assoc($industry_payments)): ?>
                      <tr>
                        <td><?php echo isset($industries_map[$payment['industry_id']]) ? htmlspecialchars($industries_map[$payment['industry_id']]) : 'N/A'; ?></td>
                        <td>TZS <?php echo number_format($payment['amount'], 2); ?></td>
                        <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                        <td><span class="payment-method method-<?php echo strtolower($payment['payment_method']); ?>"><?php echo htmlspecialchars($payment['payment_method']); ?></span></td>
                        <td><?php echo htmlspecialchars($payment['notes']); ?></td>
                        <td>
                          <?php if(isset($payment['status']) && $payment['status'] == 'unpaid'): ?>
                            <form method="post" style="display:inline;">
                              <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                              <input type="hidden" name="industry_payment_action" value="receive">
                              <button type="submit" class="btn btn-success btn-sm">Receive</button>
                            </form>
                            <form method="post" style="display:inline;">
                              <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                              <input type="hidden" name="industry_payment_action" value="reject">
                              <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                          <?php elseif(isset($payment['status']) && $payment['status'] == 'paid'): ?>
                            <span class="badge bg-success">Paid</span>
                          <?php elseif(isset($payment['status']) && $payment['status'] == 'rejected'): ?>
                            <span class="badge bg-danger">Rejected</span>
                          <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($payment['account_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($payment['account_number'] ?? ''); ?></td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="tab-pane fade" id="farmer" role="tabpanel">
            <div class="payment-card">
              <h2 class="mb-4">Farmer Payments (Paid Out)</h2>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Farmer</th>
                      <th>Amount</th>
                      <th>Date</th>
                      <th>Method</th>
                      <th>Notes</th>
                      <th>Status</th>
                      <th>Account Name</th>
                      <th>Account Number</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while($payment = mysqli_fetch_assoc($farmer_payments)): ?>
                      <tr>
                        <td><?php echo isset($farmers_map[$payment['farmer_id']]) ? htmlspecialchars($farmers_map[$payment['farmer_id']]) : 'N/A'; ?></td>
                        <td>TZS <?php echo number_format($payment['amount'], 2); ?></td>
                        <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                        <td><span class="payment-method method-<?php echo strtolower($payment['payment_method']); ?>"><?php echo htmlspecialchars($payment['payment_method']); ?></span></td>
                        <td><?php echo htmlspecialchars($payment['notes']); ?></td>
                        <td><?php if(isset($payment['status']) && $payment['status'] == 'paid'): ?><span class="badge bg-success">Paid</span><?php else: ?><span class="badge bg-warning text-dark">Unpaid</span><?php endif; ?></td>
                        <td><?php echo htmlspecialchars($payment['account_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($payment['account_number'] ?? ''); ?></td>
                        <td>
                          <?php if(!isset($payment['status']) || $payment['status'] != 'paid'): ?>
                            <button type="button" class="btn btn-success btn-sm pay-now-btn" 
                              data-id="<?php echo $payment['id']; ?>"
                              data-farmer="<?php echo isset($farmers_map[$payment['farmer_id']]) ? htmlspecialchars($farmers_map[$payment['farmer_id']]) : 'N/A'; ?>"
                              data-amount="<?php echo number_format($payment['amount'], 2); ?>"
                              data-method="<?php echo htmlspecialchars($payment['payment_method']); ?>"
                              data-notes="<?php echo htmlspecialchars($payment['notes']); ?>"
                              data-date="<?php echo date('M d, Y', strtotime($payment['payment_date'])); ?>"
                              data-recipient-type="farmer"
                              data-recipient-id="<?php echo $payment['farmer_id']; ?>"
                              data-account-name="<?php echo htmlspecialchars($payment['account_name'] ?? ''); ?>"
                              data-account-number="<?php echo htmlspecialchars($payment['account_number'] ?? ''); ?>">
                              <i class="fas fa-bolt"></i> Pay Now
                            </button>
                          <?php else: ?>
                            <span class="text-muted">--</span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
    $(document).ready(function() {
        <?php if(isset($_SESSION["success"])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo addslashes($_SESSION["success"]); ?>',
                confirmButtonColor: '#3085d6',
                timer: 2000
            });
            <?php unset($_SESSION["success"]); ?>
        <?php endif; ?>
        <?php if(isset($_SESSION["error"])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo addslashes($_SESSION["error"]); ?>',
                confirmButtonColor: '#d33',
            });
            <?php unset($_SESSION["error"]); ?>
        <?php endif; ?>

        // Recipient search logic
        let farmers = [];
        let industries = [];
        // Preload farmers and industries from PHP
        <?php
        $farmers_arr = [];
        $farmers_res = mysqli_query($conn, "SELECT farmer_id, full_name FROM farmers");
        while($row = mysqli_fetch_assoc($farmers_res)) {
            $farmers_arr[] = $row;
        }
        $industries_arr = [];
        $industries_res = mysqli_query($conn, "SELECT id, company_name FROM industries");
        while($row = mysqli_fetch_assoc($industries_res)) {
            $industries_arr[] = $row;
        }
        ?>
        farmers = <?php echo json_encode($farmers_arr); ?>;
        industries = <?php echo json_encode($industries_arr); ?>;

        $('#recipient_search').on('input', function() {
            const query = $(this).val().toLowerCase();
            let results = [];
            if (query.length > 0) {
                results = [
                    ...farmers.filter(f => f.full_name.toLowerCase().includes(query) || f.farmer_id.toLowerCase().includes(query)).map(f => ({
                        id: f.farmer_id,
                        name: f.full_name,
                        type: 'farmer',
                        label: f.full_name + ' (Farmer)'
                    })),
                    ...industries.filter(i => i.company_name.toLowerCase().includes(query) || i.id.toLowerCase().includes(query)).map(i => ({
                        id: i.id,
                        name: i.company_name,
                        type: 'industry',
                        label: i.company_name + ' (Industry)'
                    }))
                ];
            }
            let html = '';
            results.forEach(r => {
                html += `<button type="button" class="list-group-item list-group-item-action" data-id="${r.id}" data-type="${r.type}">${r.label}</button>`;
            });
            $('#recipient_results').html(html).toggle(results.length > 0);
        });
        $('#recipient_results').on('click', 'button', function() {
            const id = $(this).data('id');
            const type = $(this).data('type');
            const label = $(this).text();
            $('#recipient_id').val(id);
            $('#recipient_type').val(type);
            $('#recipient_search').val(label);
            $('#recipient_results').hide();
        });
        // Hide results if clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#recipient_search, #recipient_results').length) {
                $('#recipient_results').hide();
            }
        });

        // Show/hide provider dropdowns based on payment method
        $('#payment_method').on('change', function() {
            const method = $(this).val();
            if (method === 'mobile') {
                $('#mobile_provider_group').show();
                $('#bank_provider_group').hide();
                $('#bank_provider').val('');
            } else if (method === 'bank') {
                $('#bank_provider_group').show();
                $('#mobile_provider_group').hide();
                $('#mobile_provider').val('');
            } else {
                $('#mobile_provider_group').hide();
                $('#bank_provider_group').hide();
                $('#mobile_provider').val('');
                $('#bank_provider').val('');
            }
        });
        // Trigger change on page load in case of form repopulation
        $('#payment_method').trigger('change');

        // AJAX Pay Now button
        $(document).on('click', '.pay-now-btn', function() {
            var btn = $(this);
            var paymentId = btn.data('id');
            btn.prop('disabled', true);
            $.ajax({
                url: '', // same page
                type: 'POST',
                data: { pay_now_id: paymentId },
                success: function() {
                    btn.replaceWith('<span class="badge bg-success">Paid</span>');
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Sent!',
                        text: 'Payment marked as paid.',
                        timer: 1800,
                        showConfirmButton: false
                    });
                },
                error: function() {
                    btn.prop('disabled', false);
                    Swal.fire('Error', 'Failed to process payment.', 'error');
                }
            });
        });
    });
    </script>
    <?php include "../../includes/footer.php"; ?>
</body>
</html> 