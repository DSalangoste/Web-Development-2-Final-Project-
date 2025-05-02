<?php
session_start();
require_once 'db.php';
require_once 'session.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if post ID is provided
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$post_id = $_GET['id'];

try {
    // First check if the user has permission to delete this post
    $stmt = $pdo->prepare("SELECT author_id FROM posts WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post || ($_SESSION['role'] !== 'admin' && $_SESSION['user_id'] !== $post['author_id'])) {
        $_SESSION['error'] = "You don't have permission to delete this post.";
        header('Location: index.php');
        exit();
    }
    
    $pdo->beginTransaction();
    
    // Delete post categories
    $stmt = $pdo->prepare("DELETE FROM post_categories WHERE post_id = ?");
    $stmt->execute([$post_id]);
    
    // Delete post comments
    $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
    $stmt->execute([$post_id]);
    
    // Delete the post
    $stmt = $pdo->prepare("DELETE FROM posts WHERE post_id = ?");
    $stmt->execute([$post_id]);
    
    $pdo->commit();
    
    $_SESSION['success'] = "Post deleted successfully!";
    header('Location: index.php');
    exit();
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error deleting post: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while deleting the post.";
    header('Location: index.php');
    exit();
}
