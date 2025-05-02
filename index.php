<?php
session_start();
require_once 'db.php';
require_once 'includes/url_helpers.php';

try {
    // Fetch categories with post count
    $stmt = $pdo->query("
        SELECT c.category_id, c.name, COUNT(DISTINCT p.post_id) as post_count 
        FROM categories c 
        LEFT JOIN post_categories pc ON c.category_id = pc.category_id 
        LEFT JOIN posts p ON pc.post_id = p.post_id 
        GROUP BY c.category_id, c.name 
        ORDER BY c.name
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get selected category if any
    $selected_category = isset($_GET['id']) ? (int)$_GET['id'] : null;

    // Fetch posts with username and category info
    $query = "
        SELECT DISTINCT p.*, u.username, GROUP_CONCAT(c.name) as categories,
               (SELECT file_path FROM images WHERE post_id = p.post_id LIMIT 1) as featured_image
        FROM posts p 
        JOIN users u ON p.author_id = u.user_id 
        LEFT JOIN post_categories pc ON p.post_id = pc.post_id 
        LEFT JOIN categories c ON pc.category_id = c.category_id
    ";

    if ($selected_category) {
        $query .= " WHERE pc.category_id = :category_id";
    }

    $query .= " GROUP BY p.post_id ORDER BY p.created_at DESC";

    $stmt = $pdo->prepare($query);
    if ($selected_category) {
        $stmt->bindParam(':category_id', $selected_category);
    }
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "An error occurred while accessing the database.";
}

$pageTitle = "Home";
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="categories-widget">
                <h3><i class="fas fa-tags"></i> Categories</h3>
                <ul class="categories-list">
                    <li class="<?= !$selected_category ? 'active' : '' ?>">
                        <a href="<?php echo getBaseUrl(); ?>/">
                            All Posts
                            <span class="post-count"><?= count($posts) ?></span>
                        </a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <li class="<?= $selected_category == $category['category_id'] ? 'active' : '' ?>">
                            <a href="<?= getCategoryUrl($category['category_id'], $category['name']) ?>">
                                <?= htmlspecialchars($category['name']) ?>
                                <span class="post-count"><?= $category['post_count'] ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <div class="post-container">
                <?php if (count($posts) > 0): ?>
                    <?php foreach ($posts as $post): ?>
                        <article class="post-preview">
                            <?php if (!empty($post['featured_image'])): ?>
                                <div class="post-preview-image">
                                    <img src="<?php echo getBaseUrl(); ?>/<?= htmlspecialchars($post['featured_image']) ?>" 
                                         alt="<?= htmlspecialchars($post['title']) ?>"
                                         loading="lazy">
                                </div>
                            <?php endif; ?>
                            <div class="post-preview-content">
                                <h2 class="post-title">
                                    <a href="<?= getPostUrl($post['post_id'], $post['title']) ?>">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                </h2>
                                <div class="post-meta">
                                    <span class="post-author">
                                        <i class="fas fa-user"></i> <?= htmlspecialchars($post['username']) ?>
                                    </span>
                                    <span class="post-date">
                                        <i class="fas fa-calendar"></i> <?= date('F j, Y', strtotime($post['created_at'])) ?>
                                    </span>
                                    <?php if ($post['categories']): ?>
                                        <span class="post-categories">
                                            <i class="fas fa-tags"></i> <?= htmlspecialchars($post['categories']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="post-excerpt">
                                    <?= nl2br(htmlspecialchars(substr(strip_tags($post['content']), 0, 200))) ?>...
                                </div>
                                <div class="post-actions">
                                    <a href="<?= getPostUrl($post['post_id'], $post['title']) ?>" class="button primary">
                                        <i class="fas fa-book-reader"></i> Read More
                                    </a>
                                    <?php if (isset($_SESSION['user_id']) && ($_SESSION['role'] === 'admin' || $_SESSION['user_id'] === $post['author_id'])): ?>
                                        <a href="<?php echo getBaseUrl(); ?>/edit_post.php?id=<?= $post['post_id'] ?>" class="button edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="<?php echo getBaseUrl(); ?>/delete_post.php?id=<?= $post['post_id'] ?>" 
                                           class="button delete" 
                                           onclick="return confirm('Are you sure you want to delete this post?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-posts">
                        <i class="fas fa-newspaper fa-3x"></i>
                        <p>No posts available in this category.</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="<?php echo getBaseUrl(); ?>/create_post.php" class="button primary">
                                <i class="fas fa-plus"></i> Create Your First Post
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<style>
.container-fluid {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.row {
    display: flex;
    gap: 2rem;
}

/* Sidebar */
.sidebar {
    flex: 0 0 250px;
}

.categories-widget {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.categories-widget h3 {
    color: var(--primary-color);
    margin: 0 0 1rem 0;
    font-size: 1.2rem;
}

.categories-widget h3 i {
    margin-right: 0.5rem;
}

.categories-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.categories-list li {
    margin-bottom: 0.5rem;
}

.categories-list a {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.categories-list a:hover {
    background: #f8f9fa;
    color: var(--primary-color);
}

.categories-list li.active a {
    background: var(--primary-color);
    color: white;
}

.post-count {
    background: rgba(0, 0, 0, 0.1);
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-size: 0.8rem;
}

/* Main Content */
.main-content {
    flex: 1;
}

.post-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.post-preview {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    display: flex;
}

.post-preview-image {
    flex: 0 0 300px;
    max-width: 300px;
    overflow: hidden;
}

.post-preview-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.post-preview:hover .post-preview-image img {
    transform: scale(1.05);
}

.post-preview-content {
    flex: 1;
    padding: 1.5rem;
}

.post-title {
    margin: 0 0 1rem 0;
}

.post-title a {
    color: var(--primary-color);
    text-decoration: none;
    transition: color 0.3s;
}

.post-title a:hover {
    color: #c0392b;
}

.post-meta {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.post-meta > span {
    margin-right: 1rem;
}

.post-meta i {
    margin-right: 0.5rem;
}

.post-excerpt {
    color: #333;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.post-actions {
    display: flex;
    gap: 0.5rem;
}

.button {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.3s;
}

.button i {
    margin-right: 0.5rem;
}

.button.primary {
    background-color: var(--primary-color);
    color: white;
}

.button.primary:hover {
    background-color: #c0392b;
}

.button.edit {
    background-color: #3498db;
    color: white;
}

.button.edit:hover {
    background-color: #2980b9;
}

.button.delete {
    background-color: #e74c3c;
    color: white;
}

.button.delete:hover {
    background-color: #c0392b;
}

.no-posts {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.no-posts i {
    color: #ddd;
    margin-bottom: 1rem;
}

.no-posts p {
    color: #666;
    margin-bottom: 1.5rem;
}

@media (max-width: 992px) {
    .row {
        flex-direction: column;
    }

    .sidebar {
        flex: none;
        width: 100%;
    }

    .post-preview {
        flex-direction: column;
    }

    .post-preview-image {
        flex: none;
        max-width: none;
        height: 200px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
