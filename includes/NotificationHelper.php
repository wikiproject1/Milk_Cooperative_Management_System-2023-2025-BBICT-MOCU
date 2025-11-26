<?php
class NotificationHelper {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Send registration notification to user
     * @param string $type Type of user (farmer/industry)
     * @param string $username Username for login
     * @param string $password Password for login
     * @param string $email User's email
     * @param string $phone User's phone number
     * @param string $name User's full name
     * @return bool Success status
     */
    public function sendRegistrationNotification($type, $username, $password, $email, $phone, $name) {
        // Send email notification
        if(!empty($email)) {
            $this->sendEmailNotification($type, $username, $password, $email, $name);
        }
        
        // Send SMS notification
        if(!empty($phone)) {
            $this->sendSMSNotification($type, $username, $password, $phone, $name);
        }
        
        return true;
    }
    
    /**
     * Send email notification
     */
    private function sendEmailNotification($type, $username, $password, $email, $name) {
        $subject = "Welcome to Milk Cooperative System";
        
        $message = "Dear " . $name . ",\n\n";
        $message .= "Welcome to the Milk Cooperative System. Your account has been created successfully.\n\n";
        $message .= "Your login credentials are:\n";
        $message .= "Username: " . $username . "\n";
        $message .= "Password: " . $password . "\n\n";
        $message .= "Please login at: " . $this->getLoginUrl($type) . "\n\n";
        $message .= "For security reasons, please change your password after your first login.\n\n";
        $message .= "Best regards,\nMilk Cooperative System Team";
        
        $headers = "From: noreply@milkcooperative.com\r\n";
        $headers .= "Reply-To: support@milkcooperative.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        mail($email, $subject, $message, $headers);
    }
    
    /**
     * Send SMS notification
     */
    private function sendSMSNotification($type, $username, $password, $phone, $name) {
        // TODO: Implement SMS gateway integration
        // For now, we'll just log the SMS details
        $message = "Welcome to Milk Cooperative System. Your account has been created. Username: " . $username . ", Password: " . $password;
        
        // Log SMS details
        $sql = "INSERT INTO sms_logs (phone, message, status) VALUES (?, ?, 'pending')";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $phone, $message);
        mysqli_stmt_execute($stmt);
    }
    
    /**
     * Get login URL based on user type
     */
    private function getLoginUrl($type) {
        $base_url = "http://" . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF']));
        return $base_url . "/modules/auth/login.php?type=" . $type;
    }
}
?> 