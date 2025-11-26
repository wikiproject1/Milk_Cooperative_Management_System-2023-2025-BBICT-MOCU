<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../auth/login.php");
    exit;
}

include "../../includes/header.php";
require_once "../../config/db.php";

$industry_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$industry = null;
if ($industry_id > 0) {
    $sql = "SELECT * FROM industries WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $industry_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $industry = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
}
if (!$industry) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Industry not found.</div></div>';
    include "../../includes/footer.php";
    exit;
}
?>
<div class="container mt-5">
    <h2>Edit Industry</h2>
    <form id="editIndustryForm" class="mt-4" style="max-width:600px;">
        <input type="hidden" name="industry_id" id="industry_id" value="<?php echo $industry['id']; ?>">
        <div class="mb-3">
            <label class="form-label">Company Name</label>
            <input type="text" class="form-control" name="company_name" id="company_name" value="<?php echo htmlspecialchars($industry['company_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contact Person</label>
            <input type="text" class="form-control" name="contact_person" id="contact_person" value="<?php echo htmlspecialchars($industry['contact_person']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" id="phone" value="<?php echo htmlspecialchars($industry['phone']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($industry['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea class="form-control" name="address" id="address" rows="2" required><?php echo htmlspecialchars($industry['address']); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Industry Type</label>
            <select class="form-control" name="industry_type" id="industry_type">
                <option value="">Select Type</option>
                <option value="Dairy Processor" <?php if($industry['industry_type'] == 'Dairy Processor') echo 'selected'; ?>>Dairy Processor</option>
                <option value="Exporter" <?php if($industry['industry_type'] == 'Exporter') echo 'selected'; ?>>Exporter</option>
                <option value="Distributor" <?php if($industry['industry_type'] == 'Distributor') echo 'selected'; ?>>Distributor</option>
                <option value="Retailer" <?php if($industry['industry_type'] == 'Retailer') echo 'selected'; ?>>Retailer</option>
                <option value="Other" <?php if($industry['industry_type'] == 'Other') echo 'selected'; ?>>Other</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update Industry</button>
        <a href="industries.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    $('#editIndustryForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=update_industry';
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
                    }).then(() => {
                        window.location.href = 'industries.php';
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to update industry.', 'error');
            }
        });
    });
});
</script>
<?php include "../../includes/footer.php"; ?> 