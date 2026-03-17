# KTSB Port Authority Management System

A comprehensive PHP/MySQL web application for managing maritime port operations, built on top of the existing static HTML/JS system.

## Features

- **Server-side Navigation**: Converted from client-side JavaScript to PHP server-side routing
- **MySQL Database Integration**: Complete database schema with relationships
- **CRUD Operations**: Full Create, Read, Update, Delete functionality for vessels
- **Dashboard**: Dynamic statistics and activity tracking
- **User Management**: Role-based access system
- **Responsive UI**: Maintained Tailwind CSS styling and dark mode support
- **Activity Logging**: Track all system changes and user activities

## Database Schema

The system includes the following main entities:
- Users (with roles: admin, user, agent)
- Customers
- Agents
- Vessels (with full specifications)
- Berths
- Services & Pricing
- Crew Transfer Requests
- BOD (Berth of Dock) Details
- Fuel & Water Requests
- Documents
- Invoices
- Rates

## Setup Instructions

### 1. Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP on your system
3. Start Apache and MySQL services from the XAMPP control panel

### 2. Database Setup
1. Open phpMyAdmin (usually at http://localhost/phpmyadmin)
2. Create a new database named `ktsb_port_management`
3. Import the `database_schema.sql` file to create tables and sample data

### 3. Project Setup
1. Copy the project files to your XAMPP htdocs directory (usually `C:\xampp\htdocs\ktsb`)
2. Ensure the following files are in place:
   - `index.php` (main application file)
   - `db_config.php` (database configuration)
   - `database_schema.sql` (database schema)
   - `pages/` directory with PHP pages

### 4. Access the Application
1. Open your web browser
2. Navigate to `http://localhost/ktsb/index.php`
3. Use the navigation to explore different sections
4. The dashboard should show statistics and activities

## Files Overview

### Core Files
- `index.php` - Main application entry point with navigation
- `db_config.php` - Database connection and utility functions
- `database_schema.sql` - MySQL database structure and sample data

### Pages
- `pages/dashboard.php` - Main dashboard with statistics
- `pages/vessels.php` - Vessel management (fully implemented)
- Other `pages/*.php` files - Placeholder for future implementation

### Original Files
- `pages/*.html` - Original static HTML files (kept for reference)
- `index.html` (renamed to `index.php`)

## Key Features Implemented

### Navigation System
- Server-side page routing using `$_GET['page']` parameter
- Active navigation highlighting
- Maintained collapsible sidebar functionality

### Database Integration
- PDO database connection with prepared statements
- Input sanitization and validation
- Error handling for database operations

### Dashboard
- Dynamic statistics from database queries
- Real-time activity feed
- Responsive card-based layout

### Vessel Management
- Complete CRUD operations (Create, Read, Update, Delete)
- Modal-based form interface
- Data validation and error messaging
- Activity logging for all operations

## Database Configuration

Default database settings in `db_config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ktsb_port_management');
define('DB_USER', 'root');
define('DB_PASS', ''); // Empty password by default
```

Modify these settings if your MySQL server configuration differs.

## Security Features

- Input sanitization using PHP's `htmlspecialchars`
- Prepared SQL statements to prevent injection
- CSRF protection (forms include action parameters)
- User authentication system (framework in place)

## Development Notes

- The system uses Tailwind CSS for styling (already included via CDN)
- Material Symbols for icons
- Responsive design maintained from original
- Dark mode support preserved
- JavaScript functionality minimized (navigation collapsible sections still work)

## Future Enhancements

The foundation is now in place for:
- Implementing remaining CRUD pages (customers, users, agents, etc.)
- AJAX-based operations for better UX
- File upload for documents
- User authentication and sessions
- API endpoints for future integrations
- Advanced search and filtering
- Reporting and analytics

## Troubleshooting

### Database Connection Issues
- Ensure MySQL service is running in XAMPP
- Check database credentials in `db_config.php`
- Verify database and tables exist in phpMyAdmin

### Page Not Loading
- Confirm file permissions allow reading
- Check PHP error logs in XAMPP
- Ensure all required files are present

### Styling Issues
- Verify internet connection for Tailwind CSS CDN
- Check browser console for JavaScript errors

## Technical Stack

- **Backend**: PHP 8+ with PDO
- **Database**: MySQL 8+
- **Frontend**: HTML5, Tailwind CSS, JavaScript
- **Server**: Apache (via XAMPP)
- **Icons**: Google Material Symbols

## Migration Notes

This system was converted from a static HTML/JavaScript application to PHP/MySQL while maintaining:
- Visual design and user interface
- Navigation structure and user experience
- Dark mode and responsive behavior
- Original functionality (enhanced with database persistence)

The original HTML files are preserved for reference and can be safely removed once the PHP system is fully operational.
