<?php
require_once 'includes/db_connect.php';
require_once 'includes/MilkDelivery.php';

// Initialize MilkDelivery class
$milkDelivery = new MilkDelivery($conn);

// Handle AJAX search request
if (isset($_GET['action']) && $_GET['action'] === 'search') {
    header('Content-Type: application/json');
    $searchTerm = $_GET['term'] ?? '';
    $results = $milkDelivery->searchFarmers($searchTerm);
    $farmers = [];
    while ($row = $results->fetch_assoc()) {
        $farmers[] = [
            'id' => $row['id'],
            'farmer_id' => $row['farmer_id'],
            'name' => $row['name'],
            'location' => $row['location'],
            'last_delivery' => $row['last_delivery_date']
        ];
    }
    echo json_encode($farmers);
    exit;
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['farmer_id'], $_POST['quantity'])) {
        $success = $milkDelivery->recordDelivery(
            $_POST['farmer_id'],
            $_POST['quantity'],
            $_POST['quality_grade'] ?? null,
            $_POST['remarks'] ?? null,
            $_SESSION['user_id'] ?? 1 // Replace with actual user ID from session
        );
        $message = $success ? 'Milk delivery recorded successfully!' : 'Error recording milk delivery.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milk Delivery Entry - Cooperative System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .select2-container {
            width: 100% !important;
        }
        .farmer-info {
            display: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Milk Delivery Entry</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Farmer Search -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Search Farmer</h5>
                <select id="farmerSearch" class="form-select">
                    <option value="">Search by Farmer ID or Name...</option>
                </select>
            </div>
        </div>
        
        <!-- Farmer Info -->
        <div id="farmerInfo" class="card mb-4 farmer-info">
            <div class="card-body">
                <h5 class="card-title">Farmer Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Farmer ID:</strong> <span id="farmerId"></span></p>
                        <p><strong>Name:</strong> <span id="farmerName"></span></p>
                        <p><strong>Location:</strong> <span id="farmerLocation"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Last Delivery:</strong> <span id="lastDelivery"></span></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Milk Delivery Form -->
        <div id="deliveryForm" class="card mb-4 farmer-info">
            <div class="card-body">
                <h5 class="card-title">Record Milk Delivery</h5>
                <form method="POST" id="milkDeliveryForm">
                    <input type="hidden" name="farmer_id" id="selectedFarmerId">
                    
                    <div class="mb-3">
                        <label class="form-label">Delivery Date</label>
                        <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantity (liters)</label>
                        <input type="number" name="quantity" class="form-control" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quality Grade</label>
                        <select name="quality_grade" class="form-select">
                            <option value="">Select Grade</option>
                            <option value="A">Grade A</option>
                            <option value="B">Grade B</option>
                            <option value="C">Grade C</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Record Delivery</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#farmerSearch').select2({
                placeholder: 'Search by Farmer ID or Name...',
                allowClear: true,
                ajax: {
                    url: 'milk_delivery_entry.php',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            action: 'search',
                            term: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(item) {
                                return {
                                    id: item.id,
                                    text: item.farmer_id + ' - ' + item.name,
                                    data: item
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
            
            // Handle farmer selection
            $('#farmerSearch').on('select2:select', function(e) {
                const data = e.params.data.data;
                $('#selectedFarmerId').val(data.id);
                $('#farmerId').text(data.farmer_id);
                $('#farmerName').text(data.name);
                $('#farmerLocation').text(data.location);
                $('#lastDelivery').text(data.last_delivery || 'No previous deliveries');
                
                $('.farmer-info').show();
            });
            
            // Handle farmer clear
            $('#farmerSearch').on('select2:clear', function() {
                $('.farmer-info').hide();
                $('#milkDeliveryForm')[0].reset();
            });
        });
    </script>
</body>
</html> 