<?php
// Initialize the session
session_start();

// Check if the user is logged in and is a farmer
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "farmer"){
    header("location: ../auth/login.php");
    exit;
}

// Include config file
require_once "../../config/db.php";

// Get the farmer_id code for the logged-in user
$user_id = $_SESSION["id"];
$farmer_code = null;
$sql = "SELECT farmer_id FROM farmers WHERE user_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $farmer_code);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

// Handle date filtering
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get delivery history with filtering
$sql = "SELECT md.*, 
        CASE 
            WHEN md.status = 'pending' THEN 'warning'
            WHEN md.status = 'approved' THEN 'success'
            WHEN md.status = 'rejected' THEN 'danger'
            ELSE 'secondary'
        END as status_color
        FROM milk_deliveries md 
        WHERE md.farmer_id = ? 
        AND md.delivery_date BETWEEN ? AND ?
        ORDER BY md.delivery_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sss", $farmer_code, $start_date, $end_date);
mysqli_stmt_execute($stmt);
$deliveries = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery History - Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include "../../includes/farmer_header.php"; ?>
    <div class="main-content">
        <div class="container mt-5">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-history"></i> Delivery History</h4>
                    <a href="request_delivery.php" class="btn btn-light">
                        <i class="fas fa-plus"></i> New Delivery Request
                    </a>
                </div>
                <div class="card-body">
                    <!-- Date Filter Form -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </form>

                    <!-- Deliveries Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="deliveriesTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Shift</th>
                                    <th>Quantity (L)</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($delivery = mysqli_fetch_assoc($deliveries)): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($delivery['delivery_date'])); ?></td>
                                    <td><?php echo ucfirst($delivery['shift']); ?></td>
                                    <td><?php echo number_format($delivery['quantity'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $delivery['status_color']; ?>">
                                            <?php echo ucfirst($delivery['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($delivery['quality_notes'] ?? ''); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#deliveriesTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 10,
                language: {
                    search: "Search deliveries:"
                }
            });
        });
    </script>
</body>
</html> 