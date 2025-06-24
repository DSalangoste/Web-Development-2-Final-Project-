<?php
session_start();
require_once 'db.php';
require_once 'session.php';
require_once 'includes/image_helpers.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to remove images.";
    header('Location: login.php');
    exit();
}

// Check if post ID is provided
if (!isset($_GET['post_id'])) {
    $_SESSION['error'] = "Invalid request.";
    header('Location: index.php');
    exit();
}

$post_id = (int)$_GET['post_id'];

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the user has permission to edit this post
    $stmt = $pdo->prepare("SELECT author_id FROM posts WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post || ($_SESSION['role'] !== 'admin' && $_SESSION['user_id'] !== $post['author_id'])) {
        $_SESSION['error'] = "You don't have permission to edit this post.";
        header('Location: index.php');
        exit();
    }
    
    // Remove the image using the existing function
    if (deletePostImage($pdo, $post_id)) {
        $_SESSION['success'] = "Image removed successfully!";
    } else {
        $_SESSION['error'] = "No image found to remove.";
    }
    
} catch (PDOException $e) {
    error_log("Error in remove_image.php: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while removing the image.";
}

// Redirect back to edit post page
header("Location: edit_post.php?id=" . $post_id);
exit();
?> 