# Milk Cooperative System

A web-based system for managing milk collection, distribution, and payments between farmers, cooperative stations, and industries.

## Features

### Cooperative Station Admin
- Manage farmers and industries
- View and manage milk deliveries
- Process orders and payments
- Generate reports

### Farmer
- Record milk deliveries
- View delivery history
- Check milk balance
- View payment history
- Print statements

### Industry
- Place milk orders
- View order history
- Make payments
- Provide feedback
- Print statements

## Technical Stack

- Backend: PHP (Core)
- Frontend: Bootstrap 5, jQuery
- Database: MySQL
- Additional Libraries:
  - DataTables for table management
  - SweetAlert2 for notifications
  - mPDF for PDF generation

## Installation

1. Clone the repository to your web server directory:
   ```bash
   git clone https://github.com/yourusername/milk_cooperative_system.git
   ```

2. Create a MySQL database and import the schema:
   ```bash
   mysql -u root -p < db/schema.sql
   ```

3. Configure the database connection in `config/db.php`:
   ```php
   define('DB_SERVER', 'localhost');
   define('DB_USERNAME', 'your_username');
   define('DB_PASSWORD', 'your_password');
   define('DB_NAME', 'milk_cooperative');
   ```

4. Ensure your web server has write permissions for:
   - `assets/images/` (for uploaded files)
   - `temp/` (for PDF generation)

5. Access the system through your web browser:
   ```
   http://localhost/milk_cooperative_system
   ```

## Directory Structure

```
milk_cooperative_system/
│
├── index.php                   # Main entry
├── config/
│   └── db.php                  # DB connection
│
├── assets/
│   ├── css/                    # Bootstrap + custom styles
│   ├── js/                     # Bootstrap + custom JS
│   └── images/                 # System logos, icons
│
├── includes/
│   ├── header.php              # Common header
│   ├── footer.php              # Common footer
│   └── sidebar.php            # Sidebar based on user role
│
├── modules/
│   ├── auth/                   # Login, logout, registration
│   ├── coop/                   # Cooperative station features
│   ├── farmer/                 # Farmer features
│   └── industry/              # Industry features
│
├── api/
│   ├── fetch_data.php          # API for AJAX calls
│   └── save_data.php
│
└── db/
    └── schema.sql              # Database schema
```

## Security Features

- Password hashing using PHP's password_hash()
- Session-based authentication
- Input validation and sanitization
- CSRF protection
- XSS prevention

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details. 

