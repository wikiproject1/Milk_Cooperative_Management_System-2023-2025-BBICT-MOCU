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

// Get all farmers
$sql = "SELECT f.*, u.username, 
        (SELECT COUNT(*) FROM milk_deliveries WHERE farmer_id = f.id) as total_deliveries,
        (SELECT COALESCE(SUM(quantity), 0) FROM milk_deliveries WHERE farmer_id = f.id) as total_milk
        FROM farmers f 
        JOIN users u ON f.user_id = u.id 
        ORDER BY f.full_name";
$farmers = mysqli_query($conn, $sql);

// Include header
include "../../includes/header.php";
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Farmers List</h1>
        <a href="register_farmer.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Register New Farmer
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Quota (L)</th>
                            <th>Total Deliveries</th>
                            <th>Total Milk (L)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($farmer = mysqli_fetch_assoc($farmers)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($farmer['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($farmer['username']); ?></td>
                            <td><?php echo htmlspecialchars($farmer['phone']); ?></td>
                            <td><?php echo htmlspecialchars($farmer['address']); ?></td>
                            <td><?php echo number_format($farmer['quota'], 2); ?></td>
                            <td><?php echo $farmer['total_deliveries']; ?></td>
                            <td><?php echo number_format($farmer['total_milk'], 2); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-info btn-sm" onclick="viewFarmer(<?php echo $farmer['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="editFarmer(<?php echo $farmer['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteFarmer(<?php echo $farmer['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Farmer Modal -->
<div class="modal fade" id="viewFarmerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Farmer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="farmerDetails"></div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Farmer Modal -->
<div class="modal fade" id="editFarmerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Farmer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editFarmerForm">
                    <input type="hidden" id="edit_farmer_id" name="farmer_id">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" id="edit_phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quota (Liters)</label>
                        <input type="number" step="0.01" class="form-control" id="edit_quota" name="quota" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="updateFarmer()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
// View farmer details
function viewFarmer(id) {
    $.ajax({
        url: '../../api/fetch_data.php',
        type: 'POST',
        data: {
            action: 'get_farmer',
            farmer_id: id
        },
        success: function(response) {
            if(response.success) {
                const farmer = response.data;
                let html = `
                    <p><strong>Full Name:</strong> ${farmer.full_name}</p>
                    <p><strong>Username:</strong> ${farmer.username}</p>
                    <p><strong>Phone:</strong> ${farmer.phone}</p>
                    <p><strong>Address:</strong> ${farmer.address}</p>
                    <p><strong>Quota:</strong> ${farmer.quota} L</p>
                    <p><strong>Total Deliveries:</strong> ${farmer.total_deliveries}</p>
                    <p><strong>Total Milk:</strong> ${farmer.total_milk} L</p>
                `;
                $('#farmerDetails').html(html);
                $('#viewFarmerModal').modal('show');
            } else {
                showError(response.message);
            }
        }
    });
}

// Edit farmer
function editFarmer(id) {
    $.ajax({
        url: '../../api/fetch_data.php',
        type: 'POST',
        data: {
            action: 'get_farmer',
            farmer_id: id
        },
        success: function(response) {
            if(response.success) {
                const farmer = response.data;
                $('#edit_farmer_id').val(farmer.id);
                $('#edit_full_name').val(farmer.full_name);
                $('#edit_phone').val(farmer.phone);
                $('#edit_address').val(farmer.address);
                $('#edit_quota').val(farmer.quota);
                $('#editFarmerModal').modal('show');
            } else {
                showError(response.message);
            }
        }
    });
}

// Update farmer
function updateFarmer() {
    const formData = new FormData($('#editFarmerForm')[0]);
    formData.append('action', 'update_farmer');
    
    $.ajax({
        url: '../../api/save_data.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if(response.success) {
                showSuccess(response.message);
                $('#editFarmerModal').modal('hide');
                location.reload();
            } else {
                showError(response.message);
            }
        }
    });
}

// Delete farmer
function deleteFarmer(id) {
    showConfirm('Are you sure you want to delete this farmer?', function() {
        $.ajax({
            url: '../../api/save_data.php',
            type: 'POST',
            data: {
                action: 'delete_farmer',
                farmer_id: id
            },
            success: function(response) {
                if(response.success) {
                    showSuccess(response.message);
                    location.reload();
                } else {
                    showError(response.message);
                }
            }
        });
    });
}
</script>

<?php
// Include footer
include "../../includes/footer.php";
?> 