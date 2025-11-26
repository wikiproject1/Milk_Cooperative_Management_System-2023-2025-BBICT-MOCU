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

// Fetch all farmers from the database
$sql = "SELECT f.*, u.username 
        FROM farmers f 
        JOIN users u ON f.user_id = u.id";

$result = mysqli_query($conn, $sql);

// Check if there are any farmers
$farmers = [];
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $farmers[] = $row;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmers List - Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        /* Custom styles for the farmers table */
        .card.shadow.mb-4 {
            border: none;
            border-radius: 15px;
            overflow: hidden; /* Ensures border-radius clips table corners */
        }

        .card-header.py-3 {
            background: linear-gradient(135deg, var(--primary-color), #8B7FFF);
            color: white;
            border-bottom: none;
            padding: 1.5rem;
            align-items: center;
        }

        .card-header h6 {
            color: white !important;
            font-size: 1.2rem;
        }

        .dataTables_wrapper .row:first-child {
            padding-top: 1rem;
        }

        .dataTables_filter label {
            font-weight: 500;
            color: var(--text-primary);
        }

        .dataTables_filter input {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 0.4rem 0.75rem;
            margin-left: 0.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .dataTables_filter input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(108, 99, 255, 0.25);
            outline: none;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1rem;
        }

        .table thead th {
    </style>
</head>
<body>
    <?php include "../../includes/header.php"; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="display-4">Registered Farmers</h1>
            <p class="lead">View and manage farmer information</p>
        </div>
    </div>

    <div class="container">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Farmers Data</h6>
                <a href="register_farmer.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-user-plus"></i> Register New Farmer
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="farmersTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Quota (L)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($farmers)): ?>
                                <?php foreach ($farmers as $farmer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($farmer['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($farmer['username']); ?></td>
                                    <td><?php echo htmlspecialchars($farmer['email']); ?></td>
                                    <td><?php echo htmlspecialchars($farmer['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($farmer['address']); ?></td>
                                    <td><?php echo htmlspecialchars($farmer['quota']); ?></td>
                                    <td>
                                        <a href="view_farmer.php?id=<?php echo $farmer['id']; ?>" class="btn btn-info btn-sm" title="View"><i class="fas fa-eye"></i></a>
                                        <button class="btn btn-warning btn-sm edit-farmer-btn" data-id="<?php echo $farmer['id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-danger btn-sm delete-farmer-btn" data-id="<?php echo $farmer['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No farmers registered yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include "../../includes/footer.php"; ?>

    <!-- Bootstrap JS -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
    <!-- jQuery -->
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <!-- DataTables JS -->
    <!-- <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script> -->
    <!-- <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script> -->
    <!-- Custom JS -->
    <!-- <script src="../../assets/js/main.js"></script> -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#farmersTable').DataTable();

            // Edit farmer confirmation
            $('.edit-farmer-btn').on('click', function() {
                const farmerId = $(this).data('id');
                Swal.fire({
                    title: 'Edit Farmer',
                    text: 'Do you want to edit this farmer? You will be redirected to the edit page.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Edit',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'edit_farmer.php?id=' + farmerId;
                    }
                });
            });

            // Delete farmer confirmation
            $('.delete-farmer-btn').on('click', function() {
                const farmerId = $(this).data('id');
                Swal.fire({
                    title: 'Delete Farmer',
                    text: 'Are you sure you want to delete this farmer? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // AJAX call to delete
                        $.ajax({
                            url: '../../api/save_data.php',
                            type: 'POST',
                            data: { action: 'delete_farmer', farmer_id: farmerId },
                            success: function(response) {
                                let res = response;
                                if (typeof response === 'string') {
                                    try { res = JSON.parse(response); } catch (e) { res = { success: false, message: 'Server error' }; }
                                }
                                if (res.success) {
                                    Swal.fire('Deleted!', res.message, 'success').then(() => { location.reload(); });
                                } else {
                                    Swal.fire('Error', res.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Failed to delete farmer.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html> 