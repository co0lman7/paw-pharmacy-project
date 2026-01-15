<?php
/**
 * Database Configuration
 *
 * This file contains the database connection settings and provides
 * a PDO connection instance for the application.
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'pharmacy_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// PDO options for secure and efficient database operations
$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

/**
 * Get database connection
 *
 * @return PDO Database connection instance
 * @throws PDOException If connection fails
 */
function getDBConnection() {
    global $pdoOptions;

    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdoOptions);
        } catch (PDOException $e) {
            // Log error and display user-friendly message
            error_log("Database connection failed: " . $e->getMessage());
            die("Sorry, we're experiencing technical difficulties. Please try again later.");
        }
    }

    return $pdo;
}

// Create a global database connection for convenience
try {
    $pdo = getDBConnection();
} catch (Exception $e) {
    // Connection error is handled in getDBConnection()
}
