<?php
/**
 * Registration Page
 *
 * New user registration form with validation
 */

$pageTitle = 'Create Account';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(getBaseUrl() . '/index.php');
}

// Get redirect URL if set
$redirectTo = isset($_GET['redirect']) ? sanitize($_GET['redirect']) : '';

// Form data
$formData = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'address' => ''
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        // Get and sanitize form data
        $formData['first_name'] = trim($_POST['first_name'] ?? '');
        $formData['last_name'] = trim($_POST['last_name'] ?? '');
        $formData['email'] = trim($_POST['email'] ?? '');
        $formData['phone'] = trim($_POST['phone'] ?? '');
        $formData['address'] = trim($_POST['address'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate first name
        if (empty($formData['first_name'])) {
            $errors['first_name'] = 'First name is required.';
        } elseif (strlen($formData['first_name']) < 2) {
            $errors['first_name'] = 'First name must be at least 2 characters.';
        }

        // Validate last name
        if (empty($formData['last_name'])) {
            $errors['last_name'] = 'Last name is required.';
        } elseif (strlen($formData['last_name']) < 2) {
            $errors['last_name'] = 'Last name must be at least 2 characters.';
        }

        // Validate email
        if (empty($formData['email'])) {
            $errors['email'] = 'Email is required.';
        } elseif (!isValidEmail($formData['email'])) {
            $errors['email'] = 'Please enter a valid email address.';
        } else {
            // Check if email already exists
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$formData['email']]);
                if ($stmt->fetch()) {
                    $errors['email'] = 'This email is already registered.';
                }
            } catch (PDOException $e) {
                error_log("Error checking email: " . $e->getMessage());
            }
        }

        // Validate password
        if (empty($password)) {
            $errors['password'] = 'Password is required.';
        } else {
            $passwordValidation = validatePassword($password);
            if (!$passwordValidation['valid']) {
                $errors['password'] = $passwordValidation['message'];
            }
        }

        // Validate password confirmation
        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        // If no errors, create user
        if (empty($errors)) {
            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (email, password, first_name, last_name, phone, address, role)
                    VALUES (?, ?, ?, ?, ?, ?, 'customer')
                ");
                $stmt->execute([
                    $formData['email'],
                    $hashedPassword,
                    $formData['first_name'],
                    $formData['last_name'],
                    $formData['phone'] ?: null,
                    $formData['address'] ?: null
                ]);

                $userId = $pdo->lastInsertId();

                // Auto-login after registration
                session_regenerate_id(true);
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_role'] = 'customer';
                $_SESSION['user_name'] = $formData['first_name'];

                // Merge guest cart
                $guestSessionId = session_id();
                $stmt = $pdo->prepare("
                    UPDATE cart SET user_id = ?, session_id = NULL
                    WHERE session_id = ?
                ");
                $stmt->execute([$userId, $guestSessionId]);

                setFlashMessage('success', 'Welcome to PharmaCare, ' . sanitize($formData['first_name']) . '!', 'success');

                // Redirect
                if ($redirectTo === 'checkout') {
                    redirect(getBaseUrl() . '/pages/checkout.php');
                } else {
                    redirect(getBaseUrl() . '/index.php');
                }

            } catch (PDOException $e) {
                error_log("Registration error: " . $e->getMessage());
                $errors['general'] = 'An error occurred during registration. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Create Account</h1>
            <p>Join PharmaCare for exclusive benefits</p>
        </div>

        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-error"><?php echo sanitize($errors['general']); ?></div>
        <?php endif; ?>

        <form action="" method="POST" id="register-form">
            <?php echo csrfField(); ?>
            <?php if ($redirectTo): ?>
                <input type="hidden" name="redirect" value="<?php echo sanitize($redirectTo); ?>">
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name"
                           class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>"
                           required value="<?php echo sanitize($formData['first_name']); ?>"
                           placeholder="John">
                    <?php if (isset($errors['first_name'])): ?>
                        <span class="form-error"><?php echo sanitize($errors['first_name']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name"
                           class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>"
                           required value="<?php echo sanitize($formData['last_name']); ?>"
                           placeholder="Doe">
                    <?php if (isset($errors['last_name'])): ?>
                        <span class="form-error"><?php echo sanitize($errors['last_name']); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address *</label>
                <input type="email" id="email" name="email"
                       class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                       required value="<?php echo sanitize($formData['email']); ?>"
                       placeholder="john@example.com">
                <?php if (isset($errors['email'])): ?>
                    <span class="form-error"><?php echo sanitize($errors['email']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control"
                       value="<?php echo sanitize($formData['phone']); ?>"
                       placeholder="+1 (555) 123-4567">
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Address</label>
                <textarea id="address" name="address" class="form-control" rows="2"
                          placeholder="Your shipping address"><?php echo sanitize($formData['address']); ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password *</label>
                <input type="password" id="password" name="password"
                       class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                       required placeholder="Create a strong password">
                <?php if (isset($errors['password'])): ?>
                    <span class="form-error"><?php echo sanitize($errors['password']); ?></span>
                <?php else: ?>
                    <span class="form-text">At least 8 characters with uppercase, lowercase, and number</span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password"
                       class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>"
                       required placeholder="Confirm your password">
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="form-error"><?php echo sanitize($errors['confirm_password']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="terms" name="terms" class="form-check-input" required>
                    <label for="terms" class="form-check-label">
                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
                Create Account
            </button>
        </form>

        <div class="auth-footer">
            Already have an account?
            <a href="<?php echo getBaseUrl(); ?>/pages/login.php<?php echo $redirectTo ? '?redirect=' . sanitize($redirectTo) : ''; ?>">
                Sign in
            </a>
        </div>
    </div>
</div>

<script>
// Client-side password validation
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    let message = [];

    if (password.length < 8) message.push('At least 8 characters');
    if (!/[A-Z]/.test(password)) message.push('One uppercase letter');
    if (!/[a-z]/.test(password)) message.push('One lowercase letter');
    if (!/[0-9]/.test(password)) message.push('One number');

    const helpText = this.nextElementSibling;
    if (helpText && helpText.classList.contains('form-text')) {
        if (message.length > 0) {
            helpText.textContent = 'Needs: ' + message.join(', ');
            helpText.style.color = '#dc3545';
        } else {
            helpText.textContent = 'Password strength: Good';
            helpText.style.color = '#28a745';
        }
    }
});

// Confirm password match
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;

    if (confirmPassword && password !== confirmPassword) {
        this.classList.add('is-invalid');
    } else {
        this.classList.remove('is-invalid');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
