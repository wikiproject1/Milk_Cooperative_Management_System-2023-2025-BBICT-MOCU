<?php
class MilkDelivery {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Search farmers by ID or name
    public function searchFarmers($searchTerm) {
        $searchTerm = "%$searchTerm%";
        $stmt = $this->conn->prepare("
            SELECT f.*, 
                   (SELECT delivery_date 
                    FROM milk_deliveries 
                    WHERE farmer_id = f.id 
                    ORDER BY delivery_date DESC, delivery_time DESC 
                    LIMIT 1) as last_delivery_date
            FROM farmers f 
            WHERE f.farmer_id LIKE ? OR f.name LIKE ?
            ORDER BY f.name
            LIMIT 10
        ");
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Get farmer details
    public function getFarmerDetails($farmerId) {
        $stmt = $this->conn->prepare("
            SELECT f.*, 
                   (SELECT delivery_date 
                    FROM milk_deliveries 
                    WHERE farmer_id = f.id 
                    ORDER BY delivery_date DESC, delivery_time DESC 
                    LIMIT 1) as last_delivery_date
            FROM farmers f 
            WHERE f.id = ?
        ");
        $stmt->bind_param("i", $farmerId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Record new milk delivery
    public function recordDelivery($farmerId, $quantity, $qualityGrade, $remarks, $createdBy) {
        $stmt = $this->conn->prepare("
            INSERT INTO milk_deliveries 
            (farmer_id, delivery_date, delivery_time, quantity, quality_grade, remarks, created_by) 
            VALUES (?, CURDATE(), CURTIME(), ?, ?, ?, ?)
        ");
        $stmt->bind_param("idssi", $farmerId, $quantity, $qualityGrade, $remarks, $createdBy);
        return $stmt->execute();
    }
    
    // Get farmer's recent deliveries
    public function getFarmerRecentDeliveries($farmerId, $limit = 5) {
        $stmt = $this->conn->prepare("
            SELECT * FROM milk_deliveries 
            WHERE farmer_id = ? 
            ORDER BY delivery_date DESC, delivery_time DESC 
            LIMIT ?
        ");
        $stmt->bind_param("ii", $farmerId, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
}
?> 