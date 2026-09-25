# RIS System

The application source is in `ris-system/`. Set your web-server document root to that directory, or open the repository root and use the root redirect.

## Requirements

- PHP 8.1+ with `mysqli`
- MySQL 5.7+ or MariaDB 10.4+
- Apache, Nginx, or PHP's built-in server

## Setup

1. Create a database named `ris_system`.
2. Import `sql/ris_system.sql/ris_system.sql`.
3. Apply `sql/migrate.sql` to existing installations.
4. Configure the environment variables documented below.
5. Serve the `ris-system` directory:

```bash
cd ris-system
php -S localhost:8000