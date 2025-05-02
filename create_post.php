<?php
session_start();
require_once 'db.php';
require_once 'session.php';
require_once 'includes/url_helpers.php';
require_once 'includes/image_helpers.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    // Get all categories
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $selected_categories = $_POST['categories'] ?? [];
        
        // Validate inputs
        $errors = [];
        if (empty($title)) {
            $errors[] = "Title is required";
        } elseif (strlen($title) > 255) {
            $errors[] = "Title must be less than 255 characters";
        }
        
        if (empty($content)) {
            $errors[] = "Content is required";
        }

        if (empty($errors)) {
            $pdo->beginTransaction();
            
            try {
                // Generate slug from title
                $slug = generateSlug($title);
                
                // Insert post
                $stmt = $pdo->prepare("
                    INSERT INTO posts (title, content, author_id, slug, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ");
                $stmt->execute([$title, $content, $_SESSION['user_id'], $slug]);
                $post_id = $pdo->lastInsertId();

                // Handle image upload
                if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == UPLOAD_ERR_OK) {
                    if (!isValidImage($_FILES['post_image'])) {
                        $errors[] = "Invalid image file. Please upload a valid image (JPEG, PNG, GIF, or WebP) under 10MB.";
                    } else {
                        if (!saveImageToDatabase($pdo, $post_id, $_FILES['post_image'])) {
                            $errors[] = "Failed to save image. The post was created without an image.";
                        }
                    }
                }

                // Insert categories
                if (!empty($selected_categories)) {
                    $stmt = $pdo->prepare("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)");
                    foreach ($selected_categories as $category_id) {
                        $stmt->execute([$post_id, $category_id]);
                    }
                }

                $pdo->commit();
                $_SESSION['success'] = "Post created successfully!";
                header('Location: ' . getPostUrl($post_id, $slug));
                exit();

            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "An error occurred while creating the post. Please try again.";
                error_log($e->getMessage());
            }
        }
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $errors[] = "An error occurred while accessing the database.";
}

$pageTitle = "Create New Post";
include 'includes/header.php';
?>

<div class="container">
    <div class="content-wrapper">
        <div class="page-header">
            <h1>Create New Post</h1>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="post-form" enctype="multipart/form-data" id="post-form">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" 
                       value="<?= isset($title) ? htmlspecialchars($title) : '' ?>" 
                       class="form-control" required>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <input type="hidden" name="content" id="content">
                <div id="editor-container"></div>
            </div>

            <div class="form-group">
                <label>Categories</label>
                <div class="categories-list">
                    <?php foreach ($categories as $category): ?>
                        <label class="category-item">
                            <input type="checkbox" name="categories[]" 
                                   value="<?= $category['category_id'] ?>"
                                   <?= isset($selected_categories) && in_array($category['category_id'], $selected_categories) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="post_image">Post Image (Optional)</label>
                <input type="file" id="post_image" name="post_image" 
                       accept="image/jpeg,image/png,image/gif,image/webp" 
                       class="form-control-file">
                <small class="form-text">Upload an image for your post. Allowed types: JPEG, PNG, GIF, WebP (max 10MB)</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="button primary">Create Post</button>
                <a href="index.php" class="button">Cancel</a>
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

    // Set initial content if it exists (for form validation failure)
    <?php if (isset($_POST['content'])): ?>
    quill.root.innerHTML = <?php echo json_encode($_POST['content']); ?>;
    <?php endif; ?>

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

<?php include 'includes/footer.php'; ?>
