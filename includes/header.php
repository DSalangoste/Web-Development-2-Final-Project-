<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/url_helpers.php';
$currentUserRole = getCurrentUserRole();
$isLoggedIn = isset($_SESSION['user_id']);

// Get the base URL dynamically
$base_url = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . " - " : ""; ?>Rising Sun Travel Blog</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="header-left">
                <h1 class="site-title">
                    <a href="<?php echo $base_url; ?>/index.php">Rising Sun Travel Blog</a>
                </h1>
                <nav class="main-nav">
                    <a href="<?php echo $base_url; ?>/index.php">Home</a>
                </nav>
            </div>
            
            <div class="header-center">
                <form action="<?php echo $base_url; ?>/search.php" method="GET" class="nav-search">
                    <input type="text" name="query" placeholder="Search posts..." required>
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="header-right">
                <?php if ($isLoggedIn): ?>
                    <span class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <?php if ($currentUserRole === 'admin'): ?>
                        <a href="<?php echo $base_url; ?>/admindash.php" class="admin-link">
                            <i class="fas fa-cog"></i>
                            Admin Dashboard
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo $base_url; ?>/create_post.php" class="button create-post">
                        <i class="fas fa-plus"></i>
                        Create Post
                    </a>
                    <a href="<?php echo $base_url; ?>/logout.php" class="button secondary">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                <?php else: ?>
                    <a href="<?php echo $base_url; ?>/login.php" class="button">Login</a>
                    <a href="<?php echo $base_url; ?>/register.php" class="button secondary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>
    <main>
        <div class="container">
