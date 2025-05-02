<?php
session_start();
require_once 'db.php';
require_once 'session.php';
require_once 'includes/url_helpers.php';
require_once 'includes/image_helpers.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /FinalProject/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /FinalProject/index.php');
    exit();
}

$post_id = $_POST['post_id'] ?? null;
$title = trim($_POST['title'] ?? '');
$content = $_POST['content'] ?? '';
$selected_categories = $_POST['categories'] ?? [];

// Validate inputs
if (empty($post_id) || empty($title) || empty($content)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: /FinalProject/edit_post.php?id=" . $post_id);
    exit();
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check user permissions
    $stmt = $pdo->prepare("SELECT author_id FROM posts WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post || ($_SESSION['role'] !== 'admin' && $_SESSION['user_id'] != $post['author_id'])) {
        $_SESSION['error'] = "You do not have permission to edit this post.";
        header('Location: /FinalProject/index.php');
        exit();
    }

    // Generate slug from title
    $slug = generateSlug($title);

    $pdo->beginTransaction();

    // Update post
    $stmt = $pdo->prepare("
        UPDATE posts 
        SET title = ?, content = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE post_id = ?
    ");
    $stmt->execute([$title, $content, $post_id]);

    // Handle image uploads
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file = [
                'name' => $_FILES['images']['name'][$key],
                'type' => $_FILES['images']['type'][$key],
                'tmp_name' => $tmp_name,
                'error' => $_FILES['images']['error'][$key],
                'size' => $_FILES['images']['size'][$key]
            ];

            if ($file['error'] === UPLOAD_ERR_OK && isValidImage($file)) {
                saveImageToDatabase($pdo, $post_id, $file);
            }
        }
    }

    // Update categories
    $stmt = $pdo->prepare("DELETE FROM post_categories WHERE post_id = ?");
    $stmt->execute([$post_id]);

    if (!empty($selected_categories)) {
        $stmt = $pdo->prepare("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)");
        foreach ($selected_categories as $category_id) {
            $stmt->execute([$post_id, $category_id]);
        }
    }

    $pdo->commit();
    $_SESSION['success'] = "Post updated successfully!";
    header('Location: /FinalProject/id/' . $post_id . '/' . $slug . '/');
    exit();

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("Error in update_post.php: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while updating the post.";
    header("Location: /FinalProject/edit_post.php?id=" . $post_id);
    exit();
}
?>
