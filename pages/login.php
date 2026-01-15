<?php
/**
 * Login Page
 *
 * User authentication form
 */

$pageTitle = 'Login';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(getBaseUrl() . '/index.php');
}

// Get redirect URL if set
$redirectTo = isset($_GET['redirect']) ? sanitize($_GET['redirect']) : '';

// Handle form submission
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_name'] = $user['first_name'];

                    // Merge guest cart with user cart
                    $guestSessionId = session_id();
                    $stmt = $pdo->prepare("
                        UPDATE cart SET user_id = ?, session_id = NULL
                        WHERE session_id = ?
                    ");
                    $stmt->execute([$user['id'], $guestSessionId]);

                    // Handle remember me (set cookie for 30 days)
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                        // In production, store this token in DB and verify on subsequent visits
                    }

                    setFlashMessage('success', 'Welcome back, ' . sanitize($user['first_name']) . '!', 'success');

                    // Redirect to intended page or home
                    if ($redirectTo === 'checkout') {
                        redirect(getBaseUrl() . '/pages/checkout.php');
                    } elseif ($user['role'] === 'admin') {
                        redirect(getBaseUrl() . '/admin/index.php');
                    } else {
                        redirect(getBaseUrl() . '/index.php');
                    }
                } else {
                    $error = 'Invalid email or password.';
                }
            } catch (PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                $error = 'An error occurred. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Welcome Back</h1>
            <p>Sign in to your account to continue</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo sanitize($error); ?></div>
        <?php endif; ?>

        <form action="" method="POST" id="login-form">
            <?php echo csrfField(); ?>
            <?php if ($redirectTo): ?>
                <input type="hidden" name="redirect" value="<?php echo sanitize($redirectTo); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required
                       value="<?php echo sanitize($email); ?>"
                       placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required
                       placeholder="Enter your password">
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="remember" name="remember" class="form-check-input">
                    <label for="remember" class="form-check-label">Remember me for 30 days</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
                Sign In
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account?
            <a href="<?php echo getBaseUrl(); ?>/pages/register.php<?php echo $redirectTo ? '?redirect=' . sanitize($redirectTo) : ''; ?>">
                Create one now
            </a>
        </div>
    </div>

    <!-- Demo Credentials -->
    <div class="card mt-3">
        <div class="card-body">
            <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem;">Demo Accounts</h4>
            <div style="font-size: 0.8125rem; color: var(--gray-600);">
                <p style="margin-bottom: 0.5rem;">
                    <strong>Admin:</strong> admin@pharmacy.com<br>
                    <strong>Customer:</strong> john.doe@email.com<br>
                    <strong>Password:</strong> password123
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
