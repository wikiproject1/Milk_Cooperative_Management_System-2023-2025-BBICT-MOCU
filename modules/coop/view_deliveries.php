<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../auth/login.php");
    exit;
}

// Include config file
require_once "../../config/db.php";

// Fetch milk delivery data
$deliveries = [];
$sql = "SELECT md.*, f.full_name FROM milk_deliveries md JOIN farmers f ON md.farmer_id = f.farmer_id ORDER BY md.delivery_date DESC, md.delivery_time DESC";

if($result = mysqli_query($conn, $sql)){
    while($row = mysqli_fetch_assoc($result)){
        $deliveries[] = $row;
    }
    mysqli_free_result($result);
} else{
    echo "Error fetching milk deliveries: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milk Delivery History - Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6C63FF;
            --secondary-color: #4CAF50;
            --accent-color: #FF6B6B;
            --background-color: #F8F9FA;
            --card-bg: #FFFFFF;
            --text-primary: #2D3436;
            --text-secondary: #636E72;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-primary);
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #8B7FFF);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(108, 99, 255, 0.2);
        }

        .card {
            background: var(--card-bg);
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        .card-header {
            background-color: var(--card-bg);
            border-bottom: 1px solid #E9ECEF;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .table th {
            color: var(--text-secondary);
        }

        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <?php include "../../includes/header.php"; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="display-4">Milk Delivery History</h1>
            <p class="lead">View all recorded milk deliveries</p>
        </div>
    </div>

    <main class="container py-4">
        <div class="card">
            <div class="card-header">
                Milk Deliveries
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="deliveriesTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Farmer</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Shift</th>
                                <th>Quantity (Liters)</th>
                                <th>Quality Score</th>
                                <th>pH Level</th>
                                <th>Temperature (°C)</th>
                                <th>Fat Content (%)</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($deliveries)): ?>
                                <?php foreach($deliveries as $delivery): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($delivery['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($delivery['delivery_date']); ?></td>
                                        <td><?php echo htmlspecialchars(date('H:i', strtotime($delivery['delivery_time']))); ?></td>
                                        <td><?php echo htmlspecialchars($delivery['shift']); ?></td>
                                        <td><?php echo htmlspecialchars($delivery['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($delivery['quality_score'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($delivery['ph_level'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($delivery['temperature'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($delivery['fat_content'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($delivery['quality_notes'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10">No milk deliveries recorded yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php include "../../includes/footer.php"; ?>

    <!-- Bootstrap JS -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
    <!-- jQuery -->
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom JS -->
    <!-- <script src="../../assets/js/main.js"></script> -->

    <script>
        $(document).ready(function() {
            $('#deliveriesTable').DataTable();
        });
    </script>

</body>
</html> 