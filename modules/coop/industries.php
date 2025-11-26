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

// Fetch all industries from the database, including the creator's username
$sql = "SELECT i.*, u.username as created_by_username 
        FROM industries i 
        LEFT JOIN users u ON i.created_by = u.id";
$result = mysqli_query($conn, $sql);

// Check if there are any industries
$industries = [];
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $industries[] = $row;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Industries List - Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        /* Custom styles for the industries table */
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
            background-color: rgba(108, 99, 255, 0.1);
            color: var(--primary-color);
            font-weight: 700;
            border-bottom: none !important;
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(108, 99, 255, 0.05);
        }

        .table tbody td {
            vertical-align: middle;
            border-bottom: 1px solid #E9ECEF;
            padding: 1rem;
        }

        .btn-sm {
            padding: 0.35rem 0.7rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
        }

        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

         .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #8B7FFF);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
        }
    </style>
</head>
<body>
    <?php include "../../includes/header.php"; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="display-4">Registered Industries</h1>
            <p class="lead">View and manage industry partners</p>
        </div>
    </div>

    <main class="container py-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Industries Data</h6>
                <a href="register_industry.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-industry"></i> Register New Industry
                </a>
            </div>
            <div class="card-body">
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

                <div class="table-responsive">
                    <table class="table table-bordered" id="industriesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Company Name</th>
                                <th>Contact Person</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Industry Type</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($industries)): ?>
                                <?php foreach ($industries as $industry): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($industry['company_name']); ?></td>
                                    <td><?php echo htmlspecialchars($industry['contact_person']); ?></td>
                                    <td><?php echo htmlspecialchars($industry['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($industry['email']); ?></td>
                                    <td><?php echo htmlspecialchars($industry['address']); ?></td>
                                     <td><?php echo htmlspecialchars($industry['industry_type'] ?? 'N/A'); ?></td>
                                     <td><?php echo htmlspecialchars($industry['created_by_username'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="view_industry.php?id=<?php echo $industry['id']; ?>" class="btn btn-info btn-sm" title="View"><i class="fas fa-eye"></i></a>
                                        <button class="btn btn-warning btn-sm edit-industry-btn" data-id="<?php echo $industry['id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-danger btn-sm delete-industry-btn" data-id="<?php echo $industry['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No industries registered yet.</td>
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
            $('#industriesTable').DataTable();

            // Edit industry confirmation
            $(document).on('click', '.edit-industry-btn', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Edit Industry',
                    text: 'Do you want to edit this industry?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Edit',
                    cancelButtonText: 'No',
                    confirmButtonColor: '#ffc107',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'edit_industry.php?id=' + id;
                    }
                });
            });

            // Delete industry confirmation
            $(document).on('click', '.delete-industry-btn', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Industry',
                    text: 'Are you sure you want to delete this industry? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'No',
                    confirmButtonColor: '#dc3545',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../../api/save_data.php',
                            type: 'POST',
                            data: { action: 'delete_industry', industry_id: id },
                            dataType: 'json',
                            success: function(response) {
                                if(response.success) {
                                    Swal.fire('Deleted!', response.message, 'success').then(() => { location.reload(); });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Failed to delete industry.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html> 