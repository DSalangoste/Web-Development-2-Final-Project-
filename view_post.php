<?php
session_start();
require_once 'db.php';
require_once 'includes/url_helpers.php';

// Get post ID from URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$post_id) {
    header('Location: ' . getBaseUrl() . '/index.php');
    exit();
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get post with author information, categories, and images in one query
    $query = "
        SELECT 
            p.*, 
            u.username, 
            GROUP_CONCAT(DISTINCT c.name) as categories,
            GROUP_CONCAT(DISTINCT i.file_path) as image_paths
        FROM posts p
        LEFT JOIN users u ON p.author_id = u.user_id
        LEFT JOIN post_categories pc ON p.post_id = pc.post_id
        LEFT JOIN categories c ON pc.category_id = c.category_id
        LEFT JOIN images i ON p.post_id = i.post_id
        WHERE p.post_id = :post_id
        GROUP BY p.post_id
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        header('Location: ' . getBaseUrl() . '/index.php');
        exit();
    }

    // Verify the slug if provided
    if (isset($_GET['slug']) && $_GET['slug'] !== generateSlug($post['title'])) {
        header('Location: ' . getPostUrl($post_id, $post['title']));
        exit();
    }

    // Process image paths
    $images = $post['image_paths'] ? explode(',', $post['image_paths']) : [];

    // Get comments for this post
    $stmt = $pdo->prepare("
        SELECT c.*, u.username 
        FROM comments c 
        LEFT JOIN users u ON c.user_id = u.user_id 
        WHERE c.post_id = :post_id 
        ORDER BY c.created_at DESC
    ");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pageTitle = htmlspecialchars($post['title']) . " - Rising Sun Travel Blog";
    include 'includes/header.php';
?>

<div class="container">
    <article class="blog-post">
        <header class="post-header">
            <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
            
            <div class="post-meta">
                <span class="post-author">
                    <i class="fas fa-user"></i> <?= htmlspecialchars($post['username']) ?>
                </span>
                <span class="post-date">
                    <i class="fas fa-calendar"></i> <?= date('F j, Y', strtotime($post['created_at'])) ?>
                </span>
                <?php if ($post['categories']): ?>
                    <span class="post-categories">
                        <i class="fas fa-tags"></i>
                        <?= htmlspecialchars($post['categories']) ?>
                    </span>
                <?php endif; ?>
            </div>
        </header>

        <?php if ($images): ?>
            <div class="post-images">
                <?php foreach($images as $image): ?>
                    <img src="<?php echo getBaseUrl(); ?>/<?= htmlspecialchars($image) ?>" alt="Post image" loading="lazy">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="post-content">
            <?= $post['content'] ?>
        </div>

        <?php if (isset($_SESSION['user_id']) && 
                  ($_SESSION['user_id'] == $post['author_id'] || $_SESSION['role'] == 'admin')): ?>
            <div class="post-actions">
                <a href="<?php echo getBaseUrl(); ?>/edit_post.php?id=<?= $post['post_id'] ?>" class="button edit">
                    <i class="fas fa-edit"></i> Edit Post
                </a>
                <a href="<?php echo getBaseUrl(); ?>/delete_post.php?id=<?= $post['post_id'] ?>" 
                   class="button delete"
                   onclick="return confirm('Are you sure you want to delete this post?');">
                    <i class="fas fa-trash"></i> Delete Post
                </a>
            </div>
        <?php endif; ?>
    </article>

    <section class="comments-section">
        <h2>Comments</h2>

        <?php if (isset($_SESSION['user_id'])): ?>
            <form action="<?php echo getBaseUrl(); ?>/create_comment.php" method="POST" class="comment-form">
                <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error'] ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <textarea name="content" rows="4" required 
                              placeholder="Write your comment here..."
                              class="form-control"><?= $_SESSION['comment_content'] ?? '' ?></textarea>
                    <?php unset($_SESSION['comment_content']); ?>
                </div>

                <div class="form-group captcha-group">
                    <?php if (extension_loaded('gd')): ?>
                        <img src="<?php echo getBaseUrl(); ?>/includes/generate_captcha.php" alt="CAPTCHA" class="captcha-image">
                    <?php else: ?>
                        <div class="text-captcha">
                            <span id="captcha-text"></span>
                            <button type="button" onclick="refreshCaptcha()" class="button secondary">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                    <?php endif; ?>
                    <input type="text" name="captcha_input" required placeholder="Enter CAPTCHA code" class="form-control">
                </div>

                <button type="submit" class="button primary">Post Comment</button>
            </form>
            
            <?php if (!extension_loaded('gd')): ?>
            <script>
            function refreshCaptcha() {
                fetch('<?php echo getBaseUrl(); ?>/includes/generate_captcha.php')
                    .then(response => response.text())
                    .then(captcha => {
                        document.getElementById('captcha-text').textContent = captcha;
                    });
            }
            // Load initial CAPTCHA
            refreshCaptcha();
            </script>
            <?php endif; ?>
        <?php else: ?>
            <p>Please <a href="<?php echo getBaseUrl(); ?>/login.php">log in</a> to leave a comment.</p>
        <?php endif; ?>

        <div class="comments-list">
            <?php if ($comments): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="comment-meta">
                            <span class="comment-author">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($comment['username']) ?>
                            </span>
                            <span class="comment-date">
                                <i class="fas fa-clock"></i> 
                                <?= date('F j, Y g:i a', strtotime($comment['created_at'])) ?>
                            </span>
                        </div>
                        <div class="comment-content">
                            <?= nl2br(htmlspecialchars($comment['content'])) ?>
                        </div>
                        <?php if (isset($_SESSION['user_id']) && 
                                  ($_SESSION['user_id'] == $comment['user_id'] || 
                                   $_SESSION['user_id'] == $post['author_id'] || 
                                   $_SESSION['role'] == 'admin')): ?>
                            <div class="comment-actions">
                                <a href="<?php echo getBaseUrl(); ?>/delete_comment.php?id=<?= $comment['comment_id'] ?>&post_id=<?= $post_id ?>" 
                                   class="delete-comment"
                                   onclick="return confirm('Are you sure you want to delete this comment?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-comments">No comments yet. Be the first to comment!</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<style>
    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem;
    }

    .blog-post {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .post-header {
        margin-bottom: 2rem;
    }

    .post-title {
        color: var(--primary-color);
        margin: 0 0 1rem 0;
        font-size: 2.5rem;
    }

    .post-meta {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }

    .post-meta > span {
        margin-right: 1rem;
        display: inline-flex;
        align-items: center;
    }

    .post-meta i {
        margin-right: 0.5rem;
        color: var(--primary-color);
    }

    .post-images {
        margin: 2rem 0;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1rem;
    }

    .post-images img {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 8px;
    }

    .post-content {
        line-height: 1.8;
        color: #333;
        margin-bottom: 2rem;
    }

    .post-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #eee;
    }

    .comments-section {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 2rem;
    }

    .comments-section h2 {
        color: var(--primary-color);
        margin: 0 0 1.5rem 0;
    }

    .comment-form {
        margin-bottom: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #555;
        font-weight: 500;
    }

    .captcha-group {
        margin-bottom: 1rem;
    }

    .captcha-group input {
        margin-top: 0.5rem;
    }

    .captcha-container {
        display: flex;
        gap: 1rem;
        align-items: center;
        margin-top: 0.5rem;
    }

    .captcha-image {
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .form-control {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.1);
    }

    .comments-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .comment {
        padding: 1rem;
        border: 1px solid #eee;
        border-radius: 4px;
        background: #f8f9fa;
    }

    .comment-meta {
        display: flex;
        gap: 1rem;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        color: #666;
    }

    .comment-content {
        line-height: 1.6;
    }

    .comment-actions {
        margin-top: 0.5rem;
        text-align: right;
    }

    .delete-comment {
        color: #e74c3c;
        text-decoration: none;
        font-size: 0.9rem;
    }

    .delete-comment:hover {
        color: #c0392b;
    }

    .no-comments {
        text-align: center;
        color: #666;
        font-style: italic;
    }

    .alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 4px;
    }

    .alert-danger {
        background-color: #fee2e2;
        border: 1px solid #fecaca;
        color: #dc2626;
    }

    .text-captcha {
        background: #f5f5f5;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .text-captcha span {
        font-family: monospace;
        font-size: 1.2em;
        letter-spacing: 2px;
        font-weight: bold;
        color: #2c3e50;
        background: white;
        padding: 5px 10px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }

    .text-captcha button {
        padding: 5px 10px;
        font-size: 0.9em;
    }

    @media (max-width: 768px) {
        .container {
            padding: 1rem;
        }

        .post-title {
            font-size: 2rem;
        }

        .post-images {
            grid-template-columns: 1fr;
        }

        .post-meta {
            flex-direction: column;
            gap: 0.5rem;
        }

        .post-actions {
            flex-direction: column;
        }
    }
</style>

<?php
    include 'includes/footer.php';
} catch (PDOException $e) {
    error_log("Error in view_post.php: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while loading the post.";
    header("Location: " . getBaseUrl() . "/index.php");
    exit();
}
?>