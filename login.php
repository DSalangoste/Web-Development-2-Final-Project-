<?php
session_start();
require_once 'db.php';
require_once 'includes/url_helpers.php';

$error = '';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . getBaseUrl() . "/index.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // Prepare statement to prevent SQL injection
        $stmt = $pdo->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the user exists and verify password
        if ($user && password_verify($password, $user['password'])) {
            // Store user session data
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect to index page for all users
            header("Location: " . getBaseUrl() . "/index.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } catch (PDOException $e) {
        $error = "An error occurred. Please try again later.";
        // Log the error for administrators
        error_log("Login error: " . $e->getMessage());
    }
}

// Check for unauthorized access attempt
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error = "You must be an administrator to access that page.";
}

include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h1><i class="fas fa-sign-in-alt"></i> Login</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo getBaseUrl(); ?>/login.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Username
                </label>
                <input type="text" name="username" id="username" required>
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" class="button primary full-width">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="auth-links">
            <p>Don't have an account? <a href="<?php echo getBaseUrl(); ?>/register.php">Register here</a></p>
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

.button.primary {
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 4px;
    padding: 0.75rem;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.button.primary:hover {
    background-color: #c0392b;
}
</style>

<?php include 'includes/footer.php'; ?>
