<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to delete comments.";
    header('Location: /FinalProject/login.php');
    exit();
}

// Get comment ID and post ID
$comment_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : null;

if (!$comment_id || !$post_id) {
    $_SESSION['error'] = "Invalid request.";
    header('Location: /FinalProject/index.php');
    exit();
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get comment details
    $stmt = $pdo->prepare("
        SELECT c.*, p.author_id as post_author_id 
        FROM comments c 
        JOIN posts p ON c.post_id = p.post_id 
        WHERE c.comment_id = ?
    ");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment || $comment['post_id'] != $post_id) {
        $_SESSION['error'] = "Comment not found.";
        header("Location: /FinalProject/view_post.php?id=$post_id");
        exit();
    }

    // Check if user has permission to delete
    if ($_SESSION['user_id'] != $comment['user_id'] && 
        $_SESSION['user_id'] != $comment['post_author_id'] && 
        $_SESSION['role'] != 'admin') {
        $_SESSION['error'] = "You don't have permission to delete this comment.";
        header("Location: /FinalProject/view_post.php?id=$post_id");
        exit();
    }

    // Delete the comment
    $stmt = $pdo->prepare("DELETE FROM comments WHERE comment_id = ?");
    $stmt->execute([$comment_id]);

    $_SESSION['success'] = "Comment deleted successfully.";

} catch (PDOException $e) {
    error_log("Error in delete_comment.php: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while deleting the comment.";
}

// Redirect back to the post
header("Location: /FinalProject/view_post.php?id=$post_id");
exit();
?>
