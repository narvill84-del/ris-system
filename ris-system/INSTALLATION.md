# Installation & Setup Guide

## System Requirements

- PHP 7.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server with mod_rewrite enabled
- Modern web browser (Chrome, Firefox, Safari, Edge)

## Installation Steps

### 1. Database Setup

1. Open phpMyAdmin or your MySQL client
2. Import the database schema:
   - Execute the SQL commands in `sql/database.sql`
   - Optionally, execute `sql/views-and-samples.sql` for views and sample data

### 2. Configuration

1. Open `config/database.php`
2. Update database credentials:
   ```php
   define('DB_HOST', 'localhost');      // Your database host
   define('DB_USER', 'root');           // Your database username
   define('DB_PASSWORD', '');           // Your database password
   define('DB_NAME', 'ris_form_system'); // Database name