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

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $delivery_id = $_POST["delivery_id"];
    $quality_score = $_POST["quality_score"];
    $ph_level = $_POST["ph_level"];
    $temperature = $_POST["temperature"];
    $fat_content = $_POST["fat_content"];
    $notes = $_POST["notes"];
    
    // Update milk delivery with quality metrics
    $sql = "UPDATE milk_deliveries SET 
            quality_score = ?, 
            ph_level = ?, 
            temperature = ?, 
            fat_content = ?, 
            quality_notes = ? 
            WHERE id = ?";
            
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "iddddsi", $quality_score, $ph_level, $temperature, $fat_content, $notes, $delivery_id);
        
        if(mysqli_stmt_execute($stmt)){
            $_SESSION["success"] = "Quality assessment recorded successfully.";
        } else {
            $_SESSION["error"] = "Error recording quality assessment.";
        }
        
        mysqli_stmt_close($stmt);
    }
    
    header("location: quality_control.php");
    exit;
}

// Get pending quality assessments
$sql = "SELECT md.*, f.full_name as farmer_name 
        FROM milk_deliveries md 
        JOIN farmers f ON md.farmer_id = f.id 
        WHERE md.quality_score IS NULL 
        ORDER BY md.delivery_date DESC";
$pending_assessments = mysqli_query($conn, $sql);

// Get recent quality assessments
$sql = "SELECT md.*, f.full_name as farmer_name 
        FROM milk_deliveries md 
        JOIN farmers f ON md.farmer_id = f.id 
        WHERE md.quality_score IS NOT NULL 
        ORDER BY md.delivery_date DESC 
        LIMIT 10";
$recent_assessments = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality Control - Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        .quality-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .quality-score {
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .score-good {
            background-color: #d4edda;
            color: #155724;
        }
        
        .score-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .score-poor {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .metric-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .metric-label {
            font-weight: 500;
            color: #6c757d;
        }
        
        .metric-value {
            font-size: 1.25rem;
            font-weight: bold;
            color: #212529;
        }
    </style>
</head>
<body>
    <?php include "../../includes/header.php"; ?>
    
    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Quality Control</h1>
            <a href="dashboard.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

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

        <!-- Pending Assessments -->
        <div class="quality-card">
            <h2 class="mb-4">Pending Quality Assessments</h2>
            <?php if(mysqli_num_rows($pending_assessments) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Farmer</th>
                                <th>Quantity</th>
                                <th>Delivery Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($delivery = mysqli_fetch_assoc($pending_assessments)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($delivery['farmer_name']); ?></td>
                                    <td><?php echo number_format($delivery['quantity'], 2); ?> L</td>
                                    <td><?php echo date('M d, Y H:i', strtotime($delivery['delivery_date'])); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#qualityModal<?php echo $delivery['id']; ?>">
                                            Assess Quality
                                        </button>
                                    </td>
                                </tr>

                                <!-- Quality Assessment Modal -->
                                <div class="modal fade" id="qualityModal<?php echo $delivery['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Quality Assessment</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="delivery_id" value="<?php echo $delivery['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Quality Score (1-10)</label>
                                                        <input type="number" class="form-control" name="quality_score" 
                                                               min="1" max="10" step="0.1" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">pH Level</label>
                                                        <input type="number" class="form-control" name="ph_level" 
                                                               step="0.01" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Temperature (°C)</label>
                                                        <input type="number" class="form-control" name="temperature" 
                                                               step="0.1" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Fat Content (%)</label>
                                                        <input type="number" class="form-control" name="fat_content" 
                                                               step="0.01" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Notes</label>
                                                        <textarea class="form-control" name="notes" rows="3"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Save Assessment</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No pending quality assessments.</div>
            <?php endif; ?>
        </div>

        <!-- Recent Assessments -->
        <div class="quality-card">
            <h2 class="mb-4">Recent Quality Assessments</h2>
            <?php if(mysqli_num_rows($recent_assessments) > 0): ?>
                <div class="row">
                    <?php while($assessment = mysqli_fetch_assoc($recent_assessments)): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="quality-score <?php 
                                        echo $assessment['quality_score'] >= 8 ? 'score-good' : 
                                            ($assessment['quality_score'] >= 6 ? 'score-warning' : 'score-poor'); 
                                    ?>">
                                        <?php echo number_format($assessment['quality_score'], 1); ?>/10
                                    </div>
                                    
                                    <h5 class="card-title"><?php echo htmlspecialchars($assessment['farmer_name']); ?></h5>
                                    <p class="card-text text-muted">
                                        <?php echo date('M d, Y H:i', strtotime($assessment['delivery_date'])); ?>
                                    </p>
                                    
                                    <div class="metric-card">
                                        <div class="metric-label">pH Level</div>
                                        <div class="metric-value"><?php echo number_format($assessment['ph_level'], 2); ?></div>
                                    </div>
                                    
                                    <div class="metric-card">
                                        <div class="metric-label">Temperature</div>
                                        <div class="metric-value"><?php echo number_format($assessment['temperature'], 1); ?>°C</div>
                                    </div>
                                    
                                    <div class="metric-card">
                                        <div class="metric-label">Fat Content</div>
                                        <div class="metric-value"><?php echo number_format($assessment['fat_content'], 2); ?>%</div>
                                    </div>
                                    
                                    <?php if(!empty($assessment['quality_notes'])): ?>
                                        <div class="mt-3">
                                            <strong>Notes:</strong>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($assessment['quality_notes'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No recent quality assessments.</div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html> 