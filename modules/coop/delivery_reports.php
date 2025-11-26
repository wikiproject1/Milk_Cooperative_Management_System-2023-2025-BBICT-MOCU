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

// Fetch milk delivery data and calculate total quantity per farmer
$farmer_totals = [];
$sql = "SELECT f.full_name, SUM(md.quantity) AS total_quantity FROM milk_deliveries md JOIN farmers f ON md.farmer_id = f.farmer_id GROUP BY md.farmer_id ORDER BY f.full_name";

if($result = mysqli_query($conn, $sql)){
    while($row = mysqli_fetch_assoc($result)){
        $farmer_totals[] = $row;
    }
    mysqli_free_result($result);
} else{
    echo "Error fetching delivery totals: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milk Delivery Reports - Milk Cooperative System</title>
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
            <h1 class="display-4">Milk Delivery Reports</h1>
            <p class="lead">Summary of milk deliveries per farmer</p>
        </div>
    </div>

    <main class="container py-4">
        <div class="card">
            <div class="card-header">
                Total Deliveries per Farmer
            </div>
            <div class="card-body">
                 <div class="mb-3">
                    <button id="exportCsvBtn" class="btn btn-success"><i class="fas fa-file-csv"></i> Export to CSV</button>
                </div>
                <div class="table-responsive">
                    <table id="farmerTotalsTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Farmer Name</th>
                                <th>Total Quantity (Liters)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($farmer_totals)): ?>
                                <?php foreach($farmer_totals as $total): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($total['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars(number_format($total['total_quantity'], 2)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2">No delivery data available yet.</td>
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
            $('#farmerTotalsTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "info": true
            });

             // Export CSV functionality
            $('#exportCsvBtn').on('click', function() {
                var table = $('#farmerTotalsTable').DataTable();
                var data = table.rows().data().toArray();
                var csvContent = "Farmer Name,Total Quantity (Liters)\n";
                data.forEach(function(row) {
                    csvContent += row.join(',') + "\n";
                });

                var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                var link = document.createElement("a");
                if (link.download !== undefined) { // feature detection
                    var url = URL.createObjectURL(blob);
                    link.setAttribute("href", url);
                    link.setAttribute("download", "farmer_milk_delivery_totals.csv");
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            });
        });
    </script>

</body>
</html> 