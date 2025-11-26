<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../auth/login.php");
    exit;
}

include "../../includes/header.php";

$farmer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>

<div class="container mt-5">
    <h2>Edit Farmer</h2>
    <form id="editFarmerForm" class="mt-4" style="max-width:600px;">
        <input type="hidden" name="farmer_id" id="farmer_id" value="<?php echo $farmer_id; ?>">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="full_name" id="full_name" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" id="phone" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea class="form-control" name="address" id="address" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Quota (Liters)</label>
            <input type="number" step="0.01" class="form-control" name="quota" id="quota" required>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="farmers.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include "../../includes/footer.php"; ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Fetch farmer data
    var farmerId = <?php echo $farmer_id; ?>;
    if (farmerId) {
        $.ajax({
            url: '../../api/fetch_data.php',
            type: 'POST',
            data: { action: 'get_farmer', farmer_id: farmerId },
            success: function(response) {
                let res = response;
                if (typeof response === 'string') {
                    try { res = JSON.parse(response); } catch (e) { res = { success: false, message: 'Server error' }; }
                }
                if (res.success) {
                    const farmer = res.data;
                    $('#full_name').val(farmer.full_name);
                    $('#phone').val(farmer.phone);
                    $('#address').val(farmer.address);
                    $('#quota').val(farmer.quota);
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to fetch farmer data.', 'error');
            }
        });
    }

    // Handle form submit
    $('#editFarmerForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        formData.push({ name: 'action', value: 'update_farmer' });
        $.ajax({
            url: '../../api/save_data.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                let res = response;
                if (typeof response === 'string') {
                    try { res = JSON.parse(response); } catch (e) { res = { success: false, message: 'Server error' }; }
                }
                if (res.success) {
                    Swal.fire('Success', res.message, 'success').then(() => {
                        window.location.href = 'farmers.php';
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to update farmer.', 'error');
            }
        });
    });
});
</script> 