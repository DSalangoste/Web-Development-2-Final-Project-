<?php
session_start();
require_once 'db.php';
require_once 'includes/url_helpers.php';

$error = '';

// If user is already logged in, redirect to index
if (isset($_SESSION['user_id'])) {
    header("Location: " . getBaseUrl() . "/index.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get and sanitize the form data
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate input
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            throw new Exception("All fields are required.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match.");
        }

        if (strlen($password) < 4) {
            throw new Exception("Password must be at least 4 characters long.");
        }

        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Username already taken.");
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Email already registered.");
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $role = 'client';
        $stmt->execute([$username, $email, $hashed_password, $role]);

        // Set success message and redirect
        $_SESSION['success_message'] = "Registration successful! Please log in.";
        header("Location: " . getBaseUrl() . "/login.php");
        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = "Register";
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h1><i class="fas fa-user-plus"></i> Create Account</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo getBaseUrl(); ?>/register.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Username
                </label>
                <input type="text" name="username" id="username" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input type="email" name="email" id="email" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input type="password" name="password" id="password" required>
                <small>Must be at least 4 characters long</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    <i class="fas fa-lock"></i> Confirm Password
                </label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>

            <button type="submit" class="button primary full-width">
                <i class="fas fa-user-plus"></i> Register
            </button>
        </form>

        <div class="auth-links">
            <p>Already have an account? <a href="<?php echo getBaseUrl(); ?>/login.php">Login here</a></p>
        </div>
    </div>
</div>

<style>
.auth-container {
    max-width: 400px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.auth-card {
    background: #fff;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.auth-card h1 {
    margin: 0 0 1.5rem 0;
    color: var(--primary-color);
    font-size: 1.75rem;
    text-align: center;
}

.auth-form .form-group {
    margin-bottom: 1.25rem;
}

.auth-form label {
    display: block;
    margin-bottom: 0.5rem;
    color: #555;
    font-weight: 500;
}

.auth-form input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.auth-form input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.1);
}

.auth-form small {
    display: block;
    margin-top: 0.25rem;
    color: #666;
    font-size: 0.875rem;
}

.full-width {
    width: 100%;
    margin-top: 1rem;
}

.auth-links {
    margin-top: 1.5rem;
    text-align: center;
    color: #666;
}

.auth-links a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.auth-links a:hover {
    text-decoration: underline;
}

.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
    font-weight: 500;
}

.alert-danger {
    background-color: #fee;
    border: 1px solid #fcc;
    color: #c33;
}

.alert i {
    margin-right: 0.5rem;
}
</style>

<?php include 'includes/footer.php'; ?>
