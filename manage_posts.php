<?php
session_start();
require_once 'db.php';
require_once 'session.php';

// Only admin or logged-in users can access
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get sorting parameters
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validate sort column
$allowedSorts = ['title', 'created_at', 'updated_at'];
if (!in_array($sort, $allowedSorts)) {
    $sort = 'created_at';
}

// Validate order
$allowedOrders = ['ASC', 'DESC'];
if (!in_array(strtoupper($order), $allowedOrders)) {
    $order = 'DESC';
}

// Fetch posts with author and category information
$query = "
    SELECT DISTINCT p.*, u.username, GROUP_CONCAT(c.name) as categories
    FROM posts p
    LEFT JOIN users u ON p.author_id = u.user_id
    LEFT JOIN post_categories pc ON p.post_id = pc.post_id
    LEFT JOIN categories c ON pc.category_id = c.category_id
    GROUP BY p.post_id
    ORDER BY p.$sort $order
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all categories for the filter
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Manage Posts";
include 'includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-list"></i> Posts List</h1>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="create_post.php" class="button primary">
                <i class="fas fa-plus"></i> Create New Post
            </a>
        <?php endif; ?>
    </div>

    <div class="filter-section">
        <div class="sort-options">
            <span>Sort by:</span>
            <a href="?sort=title&order=<?php echo $sort === 'title' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>" 
               class="sort-link <?php echo $sort === 'title' ? 'active' : ''; ?>">
                Title 
                <?php if ($sort === 'title'): ?>
                    <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                <?php endif; ?>
            </a>
            <a href="?sort=created_at&order=<?php echo $sort === 'created_at' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>" 
               class="sort-link <?php echo $sort === 'created_at' ? 'active' : ''; ?>">
                Created Date
                <?php if ($sort === 'created_at'): ?>
                    <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                <?php endif; ?>
            </a>
            <a href="?sort=updated_at&order=<?php echo $sort === 'updated_at' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>" 
               class="sort-link <?php echo $sort === 'updated_at' ? 'active' : ''; ?>">
                Updated Date
                <?php if ($sort === 'updated_at'): ?>
                    <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <div class="posts-list">
        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
                <div class="post-item">
                    <div class="post-title">
                        <h3>
                            <a href="view_post.php?id=<?php echo $post['post_id']; ?>">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h3>
                        <?php if ($post['categories']): ?>
                            <div class="post-categories">
                                <?php foreach (explode(',', $post['categories']) as $category): ?>
                                    <span class="category-tag"><?php echo htmlspecialchars($category); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="post-meta">
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($post['username']); ?></span>
                        <span><i class="fas fa-calendar"></i> Created: <?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                        <span><i class="fas fa-clock"></i> Updated: <?php echo date('M j, Y', strtotime($post['updated_at'])); ?></span>
                    </div>
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['user_id'] === $post['author_id']): ?>
                        <div class="post-actions">
                            <a href="edit_post.php?id=<?php echo $post['post_id']; ?>" class="button secondary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_post.php?id=<?php echo $post['post_id']; ?>" class="button delete">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-posts">
                <p>No posts found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.filter-section {
    background: #fff;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.sort-options {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.sort-options span {
    color: #666;
}

.sort-link {
    color: #333;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.sort-link:hover {
    background-color: #f0f0f0;
}

.sort-link.active {
    background-color: var(--primary-color);
    color: white;
}

.post-item {
    background: #fff;
    padding: 1.5rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.post-title h3 {
    margin: 0 0 0.5rem 0;
}

.post-title a {
    color: #333;
    text-decoration: none;
}

.post-title a:hover {
    color: var(--primary-color);
}

.post-categories {
    margin: 0.5rem 0;
}

.category-tag {
    background: #f0f0f0;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.875rem;
    color: #666;
    margin-right: 0.5rem;
}

.post-meta {
    color: #666;
    font-size: 0.875rem;
    margin: 0.5rem 0;
}

.post-meta span {
    margin-right: 1rem;
}

.post-meta i {
    margin-right: 0.25rem;
}

.post-actions {
    margin-top: 1rem;
    display: flex;
    gap: 0.5rem;
}

.no-posts {
    text-align: center;
    padding: 2rem;
    background: #fff;
    border-radius: 4px;
    color: #666;
}
</style>

<?php include 'includes/footer.php'; ?>
