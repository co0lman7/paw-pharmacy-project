<?php
/**
 * Authentication Actions Handler
 *
 * Handles logout and other auth-related actions
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$action = isset($_GET['action']) ? sanitize($_GET['action']) : '';

switch ($action) {
    case 'logout':
        // Clear session
        $_SESSION = [];

        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        // Destroy session
        session_destroy();

        // Redirect to home
        header("Location: " . getBaseUrl() . "/index.php");
        exit();
        break;

    default:
        // Invalid action, redirect to home
        header("Location: " . getBaseUrl() . "/index.php");
        exit();
}
