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

// Get post ID from URL
$post_id = $_GET['id'] ?? null;

if (!$post_id) {
    header('Location: /FinalProject/index.php');
    exit();
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch post details
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, GROUP_CONCAT(pc.category_id) as category_ids 
        FROM posts p 
        JOIN users u ON p.author_id = u.user_id 
        LEFT JOIN post_categories pc ON p.post_id = pc.post_id 
        WHERE p.post_id = ? 
        GROUP BY p.post_id
    ");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check user permissions
    if (!$post || ($_SESSION['role'] !== 'admin' && $_SESSION['user_id'] != $post['author_id'])) {
        $_SESSION['error'] = "You do not have permission to edit this post.";
        header('Location: /FinalProject/index.php');
        exit();
    }

    // Get existing categories
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get existing post categories
    $post_categories = $post['category_ids'] ? explode(',', $post['category_ids']) : [];

    $pageTitle = "Edit Post - " . htmlspecialchars($post['title']);
    include 'includes/header.php';
?>

<div class="container">
    <div class="content-wrapper">
        <div class="page-header">
            <h1>Edit Post</h1>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form action="update_post.php" method="POST" enctype="multipart/form-data" class="post-form" id="post-form">
            <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
            <input type="hidden" name="content" id="content">
            
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($post['title']) ?>" required class="form-control">
            </div>

            <div class="form-group">
                <label for="editor">Content</label>
                <div id="editor-container"></div>
            </div>

            <div class="form-group">
                <label>Categories</label>
                <div class="categories-list">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-item">
                            <input type="checkbox" 
                                   id="category_<?= $category['category_id'] ?>" 
                                   name="categories[]" 
                                   value="<?= $category['category_id'] ?>"
                                   <?= in_array($category['category_id'], $post_categories) ? 'checked' : '' ?>>
                            <label for="category_<?= $category['category_id'] ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="image">Add Images</label>
                <input type="file" id="image" name="images[]" accept="image/*" multiple class="form-control-file">
                <small class="form-text">You can select multiple images. Supported formats: JPG, PNG, GIF, WebP</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="button primary">Update Post</button>
                <a href="/FinalProject/view_post.php?id=<?= $post['post_id'] ?>" class="button">Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- Include Quill stylesheet -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<style>
.content-wrapper {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin-top: 2rem;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    color: var(--primary-color);
    font-size: 2rem;
    margin: 0;
}

.post-form {
    max-width: 800px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-color);
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.form-control:focus {
    border-color: var(--primary-color);
    outline: none;
}

.form-control-file {
    display: block;
    width: 100%;
    padding: 0.5rem 0;
}

.form-text {
    display: block;
    margin-top: 0.25rem;
    color: var(--text-muted);
    font-size: 0.875rem;
}

#editor-container {
    height: 375px;
    margin-bottom: 1rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
}

.ql-toolbar.ql-snow {
    border-color: var(--border-color);
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
}

.ql-container.ql-snow {
    border-color: var(--border-color);
    border-bottom-left-radius: 4px;
    border-bottom-right-radius: 4px;
}

.categories-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    padding: 1rem;
    background: var(--bg-light);
    border-radius: 4px;
}

.category-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.category-item input[type="checkbox"] {
    margin: 0;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.button {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.2s;
}

.button.primary {
    background-color: var(--primary-color);
    color: white;
}

.button.primary:hover {
    background-color: var(--primary-dark);
}

.button:not(.primary) {
    background-color: var(--bg-light);
    color: var(--text-color);
}

.button:not(.primary):hover {
    background-color: var(--border-color);
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
}

.alert-danger {
    background-color: var(--error-bg);
    color: var(--error-text);
    border: 1px solid var(--error-border);
}

.alert-success {
    background-color: var(--success-bg);
    color: var(--success-text);
    border: 1px solid var(--success-border);
}

@media (max-width: 768px) {
    .content-wrapper {
        padding: 1rem;
    }

    .categories-list {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .button {
        width: 100%;
        text-align: center;
    }
}
</style>

<!-- Include Quill library -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    // Initialize Quill editor
    var quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Write your post content here...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    // Set initial content
    quill.root.innerHTML = <?php echo json_encode($post['content']); ?>;

    // Handle form submission
    document.getElementById('post-form').onsubmit = function() {
        // Get editor content and update hidden input
        var content = quill.root.innerHTML;
        document.getElementById('content').value = content;
        
        // Basic validation
        if (content.trim() === '<p><br></p>' || content.trim() === '') {
            alert('Please enter some content for your post.');
            return false;
        }
        return true;
    };
</script>

<?php
    include 'includes/footer.php';
} catch (PDOException $e) {
    error_log("Error in edit_post.php: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while loading the post.";
    header("Location: /FinalProject/index.php");
    exit();
}
?>
