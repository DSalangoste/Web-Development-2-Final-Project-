<?php
require_once 'db.php';
require_once 'session.php';
requireAdmin();

$pdo = new PDO(DB_DSN, DB_USER, DB_PASS);

// Handle category operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_action'])) {
    try {
        switch ($_POST['category_action']) {
            case 'add':
                if (!empty($_POST['category_name'])) {
                    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
                    $stmt->execute([trim($_POST['category_name'])]);
                    $_SESSION['success'] = "Category added successfully!";
                }
                break;
            case 'delete':
                if (!empty($_POST['category_id'])) {
                    $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
                    $stmt->execute([$_POST['category_id']]);
                    $_SESSION['success'] = "Category deleted successfully!";
                }
                break;
            case 'edit':
                if (!empty($_POST['category_id']) && !empty($_POST['category_name'])) {
                    $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE category_id = ?");
                    $stmt->execute([trim($_POST['category_name']), $_POST['category_id']]);
                    $_SESSION['success'] = "Category updated successfully!";
                }
                break;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . ($e->getCode() == 23000 ? "Category name already exists." : "Operation failed.");
    }
    header("Location: admindash.php");
    exit();
}

// Fetch blog posts with author and category information
$stmt = $pdo->query("SELECT posts.*, users.username, GROUP_CONCAT(c.name) as categories 
                     FROM posts 
                     JOIN users ON posts.author_id = users.user_id 
                     LEFT JOIN post_categories pc ON posts.post_id = pc.post_id
                     LEFT JOIN categories c ON pc.category_id = c.category_id
                     GROUP BY posts.post_id
                     ORDER BY created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch comment moderation queue
$stmt = $pdo->query("SELECT comments.*, users.username, posts.title as post_title 
                     FROM comments 
                     JOIN users ON comments.user_id = users.user_id 
                     JOIN posts ON comments.post_id = posts.post_id 
                     ORDER BY created_at DESC 
                     LIMIT 10");
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Admin Dashboard";
include 'includes/header.php';
?>

<div class="admin-dashboard">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-header">
        <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
        <div class="quick-actions">
            <a href="create_post.php" class="button primary"><i class="fas fa-plus"></i> New Post</a>
            <a href="index.php" class="button"><i class="fas fa-home"></i> View Site</a>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Category Management -->
        <div class="dashboard-card">
            <h2><i class="fas fa-tags"></i> Categories</h2>
            <form action="admindash.php" method="POST" class="category-form">
                <input type="hidden" name="category_action" value="add">
                <div class="input-group">
                    <input type="text" name="category_name" placeholder="New category name" required>
                    <button type="submit" class="button primary">Add</button>
                </div>
            </form>

            <div class="categories-list">
                <?php foreach ($categories as $category): ?>
                    <div class="category-item">
                        <form action="admindash.php" method="POST" class="category-edit-form">
                            <input type="hidden" name="category_action" value="edit">
                            <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
                            <div class="input-group">
                                <input type="text" name="category_name" value="<?= htmlspecialchars($category['name']) ?>" required>
                                <button type="submit" class="button secondary" title="Save">
                                    <i class="fas fa-save"></i>
                                </button>
                                <button type="submit" 
                                        formaction="admindash.php" 
                                        onclick="if(!confirm('Delete this category?')) return false; this.form.category_action.value='delete';" 
                                        class="button delete" 
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Posts -->
        <div class="dashboard-card">
            <h2><i class="fas fa-blog"></i> Recent Posts</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Categories</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?= htmlspecialchars($post['title']) ?></td>
                            <td><?= htmlspecialchars($post['username']) ?></td>
                            <td>
                                <?php if ($post['categories']): ?>
                                    <?php foreach (explode(',', $post['categories']) as $cat): ?>
                                        <span class="category-tag"><?= htmlspecialchars($cat) ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                            <td><?= date('Y-m-d H:i', strtotime($post['created_at'])) ?></td>
                            <td class="actions">
                                <a href="edit_post.php?id=<?= $post['post_id'] ?>" class="button secondary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_post.php?id=<?= $post['post_id'] ?>" 
                                   onclick="return confirm('Delete this post?')" 
                                   class="button delete" 
                                   title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <a href="view_post.php?id=<?= $post['post_id'] ?>" class="button" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Comments -->
        <div class="dashboard-card">
            <h2><i class="fas fa-comments"></i> Recent Comments</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Post</th>
                            <th>Comment By</th>
                            <th>Content</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td><?= htmlspecialchars($comment['post_title']) ?></td>
                            <td><?= htmlspecialchars($comment['username']) ?></td>
                            <td><?= htmlspecialchars(substr($comment['content'], 0, 100)) ?>...</td>
                            <td class="actions">
                                <a href="/FinalProject/delete_comment.php?id=<?= $comment['comment_id'] ?>&post_id=<?= $comment['post_id'] ?>" 
                                   onclick="return confirm('Delete this comment?')"
                                   class="button delete"
                                   title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <a href="/FinalProject/view_post.php?id=<?= $comment['post_id'] ?>#comment-<?= $comment['comment_id'] ?>" 
                                   class="button"
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.admin-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.dashboard-header h1 {
    margin: 0;
    color: #333;
}

.quick-actions {
    display: flex;
    gap: 10px;
}

.dashboard-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 20px;
}

.dashboard-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
    width: 100%;
}

.dashboard-card h2 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #333;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.category-form {
    margin-bottom: 20px;
}

.input-group {
    display: flex;
    gap: 8px;
}

.input-group input[type="text"] {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
}

.categories-list {
    display: grid;
    gap: 10px;
}

.category-item {
    background: #f8f9fa;
    border-radius: 4px;
    overflow: hidden;
}

.category-edit-form {
    padding: 8px;
}

.table-container {
    width: 100%;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    table-layout: fixed;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* Column widths */
table th:nth-child(1), /* Title */
table td:nth-child(1) {
    width: 30%;
}

table th:nth-child(2), /* Author */
table td:nth-child(2) {
    width: 15%;
}

table th:nth-child(3), /* Categories */
table td:nth-child(3) {
    width: 25%;
}

table th:nth-child(4), /* Created */
table td:nth-child(4) {
    width: 15%;
}

table th:nth-child(5), /* Actions */
table td:nth-child(5) {
    width: 15%;
}

th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.actions {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.button {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    text-decoration: none;
    transition: opacity 0.2s;
    background: #f0f0f0;
    color: #333;
    white-space: nowrap;
}

.button:hover {
    opacity: 0.9;
}

.button.primary {
    background: #007bff;
    color: white;
}

.button.secondary {
    background: #6c757d;
    color: white;
}

.button.delete {
    background: #dc3545;
    color: white;
}

.category-tag {
    display: inline-block;
    background: #e9ecef;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    margin: 2px;
    color: #666;
}

.alert {
    padding: 12px 16px;
    margin-bottom: 20px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-danger {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .actions {
        justify-content: flex-start;
    }

    table {
        font-size: 0.85rem;
    }

    th, td {
        padding: 8px;
    }

    .button {
        padding: 6px 10px;
        font-size: 0.85rem;
    }

    /* Adjust column widths for mobile */
    table th:nth-child(1),
    table td:nth-child(1) {
        width: 40%;
    }

    table th:nth-child(2),
    table td:nth-child(2) {
        width: 20%;
    }

    table th:nth-child(3),
    table td:nth-child(3) {
        width: 40%;
    }

    /* Hide date on mobile */
    table th:nth-child(4),
    table td:nth-child(4) {
        display: none;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
