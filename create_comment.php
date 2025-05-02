<?php
session_start();
require_once 'db.php';
require_once 'includes/url_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /FinalProject/index.php');
    exit();
}

// Check login status first
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to comment.";
    header('Location: /FinalProject/login.php');
    exit();
}

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : null;
$content = trim($_POST['content'] ?? '');
$captcha_input = trim($_POST['captcha_input'] ?? '');

// Validate post_id
if (!$post_id) {
    $_SESSION['error'] = "Invalid post.";
    header('Location: /FinalProject/index.php');
    exit();
}

// Get post details for redirect
try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $stmt = $pdo->prepare("SELECT title FROM posts WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        $_SESSION['error'] = "Post not found.";
        header('Location: /FinalProject/index.php');
        exit();
    }
    
    // Store redirect URL
    $redirect_url = getPostUrl($post_id, $post['title']);
    
} catch (PDOException $e) {
    error_log("Error in create_comment.php: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred. Please try again.";
    header('Location: /FinalProject/index.php');
    exit();
}

// Validate content
if (empty($content)) {
    $_SESSION['error'] = "Comment content cannot be empty.";
    header('Location: ' . $redirect_url);
    exit();
}

// Validate CAPTCHA
if (!isset($_SESSION['captcha']) || strcasecmp($captcha_input, $_SESSION['captcha']) != 0) {
    $_SESSION['comment_content'] = $content; // Save the comment content
    $_SESSION['error'] = "Invalid CAPTCHA code. Please try again.";
    header('Location: ' . $redirect_url);
    exit();
}

// Clear used CAPTCHA
unset($_SESSION['captcha']);

try {
    // Insert comment
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$post_id, $_SESSION['user_id'], $content]);
    
    $_SESSION['success'] = "Comment added successfully!";
    
} catch (PDOException $e) {
    error_log("Error in create_comment.php: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while adding your comment.";
}

header('Location: ' . $redirect_url);
exit();
