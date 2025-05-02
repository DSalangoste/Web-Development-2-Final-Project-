<?php
require_once 'session.php';
require_once 'includes/url_helpers.php';
$currentUserRole = getCurrentUserRole();
$isLoggedIn = isset($_SESSION['user_id']);

// Get the base URL dynamically
$base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) : 'Rising Sun Travel Blog'; ?></title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        header {
            background-color: var(--secondary-color);
            padding: 1rem;
            color: white;
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .site-title {
            margin: 0;
            font-size: 1.8rem;
            white-space: nowrap;
        }
        
        .site-title a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .site-title i {
            font-size: 1.5rem;
        }
        
        .main-nav {
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        
        .nav-links {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 3px;
            transition: background-color 0.3s;
        }
        
        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .nav-search {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-search input {
            padding: 0.5rem;
            border: none;
            border-radius: 3px;
            min-width: 200px;
        }
        
        .nav-search button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 0.5rem;
        }
        
        .admin-link {
            background-color: var(--primary-color) !important;
        }
        
        .admin-link:hover {
            background-color: #c0392b !important;
        }
        
        .welcome-text {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .button {
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 3px;
            transition: background-color 0.3s;
        }
        
        .button:hover {
            background-color: #c0392b;
        }
        
        .button.secondary {
            background-color: #34495e;
        }
        
        .button.secondary:hover {
            background-color: #2c3e50;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <h1 class="site-title">
                <a href="index.php">
                    Rising Sun Travel Blog
                </a>
            </h1>
            <nav class="main-nav">
                <div class="nav-links">
                    <a href="index.php">Home</a>
                    <form action="search.php" method="GET" class="nav-search">
                        <input type="text" name="query" placeholder="Search posts..." required>
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                    <?php if ($isLoggedIn): ?>
                        <span class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                        <?php if ($currentUserRole === 'admin'): ?>
                            <a href="admindash.php" class="admin-link"><i class="fas fa-cog"></i> Admin Dashboard</a>
                        <?php endif; ?>
                        <a href="create_post.php" class="button"><i class="fas fa-plus"></i> Create Post</a>
                        <a href="logout.php" class="button secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="register.php">Register</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <main class="container">
