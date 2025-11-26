<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a farmer
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'farmer') {
    header('Location: /MCS/index.php');
    exit();
}

$user_id = $_SESSION['id'];

// Get farmer information
$sql = "SELECT * FROM farmers WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$farmer = mysqli_fetch_assoc($result);

if (!$farmer) {
    die("Farmer profile not found");
}

// Get milk delivery records
$sql = "SELECT * FROM milk_deliveries WHERE farmer_id = ? ORDER BY delivery_date DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $farmer['farmer_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$deliveries = [];
while ($row = mysqli_fetch_assoc($result)) {
    $deliveries[] = $row;
}

// Calculate total milk delivered
$total_milk = 0;
foreach ($deliveries as $delivery) {
    $total_milk += $delivery['quantity'];
}

// Get payment status
$sql = "SELECT * FROM payments WHERE farmer_id = ? ORDER BY payment_date DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $farmer['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$payments = [];
while ($row = mysqli_fetch_assoc($result)) {
    $payments[] = $row;
}

$total_paid = 0;
$total_pending = 0;
foreach ($payments as $payment) {
    if ($payment['status'] === 'completed') {
        $total_paid += $payment['amount'];
    } else {
        $total_pending += $payment['amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Profile - <?php echo htmlspecialchars($farmer['full_name']); ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../../includes/farmer_header.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <!-- Farmer Profile Information -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Profile Information</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>Farmer ID:</strong> <?php echo htmlspecialchars($farmer['farmer_id']); ?></p>
                        <p><strong>Name:</strong> <span id="profile_full_name"><?php echo htmlspecialchars($farmer['full_name']); ?></span></p>
                        <p><strong>Phone:</strong> <span id="profile_phone"><?php echo htmlspecialchars($farmer['phone']); ?></span></p>
                        <p><strong>Email:</strong> <span id="profile_email"><?php echo htmlspecialchars($farmer['email']); ?></span></p>
                        <p><strong>Address:</strong> <span id="profile_address"><?php echo htmlspecialchars($farmer['address']); ?></span></p>
                        <p><strong>Quota:</strong> <?php echo number_format($farmer['quota'], 2); ?> liters</p>
                        <button class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#editProfileModal"><i class="fas fa-edit"></i> Edit Profile</button>
                        <a href="change_password.php" class="btn btn-warning btn-sm mt-2 ms-2"><i class="fas fa-key"></i> Change Password</a>
                    </div>
                </div>
                <!-- Payment Summary -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h4>Payment Summary</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>Total Paid:</strong> TZS <?php echo number_format($total_paid, 2); ?></p>
                        <p><strong>Pending Payments:</strong> TZS <?php echo number_format($total_pending, 2); ?></p>
                    </div>
                </div>
            </div>
            <!-- Milk Delivery Records -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Milk Delivery Records</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Shift</th>
                                        <th>Quantity (L)</th>
                                        <th>Quality</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($deliveries as $delivery): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d', strtotime($delivery['delivery_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($delivery['shift']); ?></td>
                                        <td><?php echo number_format($delivery['quantity'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($delivery['quality_grade']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $delivery['status'] === 'approved' ? 'success' : ($delivery['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($delivery['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2"><strong>Total Milk Delivered:</strong></td>
                                        <td colspan="3"><strong><?php echo number_format($total_milk, 2); ?> liters</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="editProfileForm">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="full_name" id="edit_full_name" value="<?php echo htmlspecialchars($farmer['full_name']); ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" name="phone" id="edit_phone" value="<?php echo htmlspecialchars($farmer['phone']); ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="edit_email" value="<?php echo htmlspecialchars($farmer['email']); ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea class="form-control" name="address" id="edit_address" rows="2" required><?php echo htmlspecialchars($farmer['address']); ?></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(function() {
      $('#editProfileForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=update_farmer_profile&farmer_id=<?php echo $farmer['id']; ?>';
        $.ajax({
          url: '../../api/save_data.php',
          type: 'POST',
          data: formData,
          dataType: 'json',
          success: function(response) {
            if(response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.message,
                timer: 1800,
                showConfirmButton: false
              });
              // Update profile info on page
              $('#profile_full_name').text($('#edit_full_name').val());
              $('#profile_phone').text($('#edit_phone').val());
              $('#profile_email').text($('#edit_email').val());
              $('#profile_address').text($('#edit_address').val());
              var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editProfileModal'));
              modal.hide();
            } else {
              Swal.fire('Error', response.message, 'error');
            }
          },
          error: function() {
            Swal.fire('Error', 'Failed to update profile.', 'error');
          }
        });
      });
    });
    </script>
</body>
</html>
