<?php
session_start();
require_once 'db.php';
require_once 'includes/url_helpers.php';

// Initialize variables
$keyword = trim($_GET['query'] ?? '');
$category = $_GET['category'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;
$posts = [];
$categories = [];
$total_results = 0;
$total_pages = 0;
$total_posts = 0;

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all categories
    $cat_stmt = $pdo->query("
        SELECT c.*, COUNT(DISTINCT p.post_id) as post_count
        FROM categories c
        LEFT JOIN post_categories pc ON c.category_id = pc.category_id
        LEFT JOIN posts p ON pc.post_id = p.post_id
        GROUP BY c.category_id, c.name
        ORDER BY c.name ASC
    ");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total posts
    $total_posts_stmt = $pdo->query("SELECT COUNT(*) as total FROM posts");
    $total_posts = $total_posts_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Basic search query
    $base_query = "SELECT p.*, u.username 
             FROM posts p 
             LEFT JOIN users u ON p.author_id = u.user_id";
    $where_conditions = [];
    $params = [];
    
    // Add search conditions
    if (!empty($keyword)) {
        $where_conditions[] = "(p.title LIKE ? OR p.content LIKE ?)";
        $params[] = "%$keyword%";
        $params[] = "%$keyword%";
    }
    
    if ($category !== 'all') {
        $where_conditions[] = "p.post_id IN (SELECT post_id FROM post_categories WHERE category_id = ?)";
        $params[] = $category;
    }
    
    // Add WHERE clause if conditions exist
    if (!empty($where_conditions)) {
        $base_query .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    $base_query .= " ORDER BY p.created_at DESC";
    
    // Get total count first
    $count_stmt = $pdo->prepare($base_query);
    $count_stmt->execute($params);
    $total_results = $count_stmt->rowCount();
    
    // Add pagination to the base query
    $base_query .= " LIMIT " . intval($per_page) . " OFFSET " . intval($offset);
    
    // Execute final query
    $stmt = $pdo->prepare($base_query);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_pages = ceil($total_results / $per_page);
    
} catch (PDOException $e) {
    $_SESSION['error'] = "An error occurred while searching. Please try again.";
}

$pageTitle = 'Search';
include 'includes/header.php';
?>

<div class="container">
    <div class="search-page">
        <aside class="search-sidebar">
            <h2>Search Pages</h2>
            <form method="GET" action="<?php echo getBaseUrl(); ?>/search.php" class="search-form">
                <div class="form-group">
                    <label for="query">Keywords</label>
                    <div class="search-input-wrapper">
                        <input type="text" 
                               id="query"
                               name="query" 
                               value="<?= htmlspecialchars($keyword) ?>" 
                               placeholder="Enter keywords to search...">
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select name="category" id="category" class="category-select">
                        <option value="all">All Categories (<?= $total_posts ?>)</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['category_id']) ?>" 
                                    <?= $category == $cat['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?> 
                                (<?= intval($cat['post_count']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </aside>

        <main class="search-results">
            <?php if (!empty($keyword) || $category !== 'all'): ?>
                <div class="results-summary">
                    Found <?= $total_results ?> result<?= $total_results !== 1 ? 's' : '' ?>
                    <?php if (!empty($keyword)): ?>
                        for "<?= htmlspecialchars($keyword) ?>"
                    <?php endif; ?>
                    <?php if ($category !== 'all'): ?>
                        in category "<?= htmlspecialchars(array_values(array_filter($categories, fn($cat) => $cat['category_id'] == $category))[0]['name'] ?? '') ?>"
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($total_results === 0): ?>
                <div class="no-results">
                    <p>No pages found matching your search criteria.</p>
                    <?php if (!empty($keyword) || $category !== 'all'): ?>
                        <p>Try:</p>
                        <ul>
                            <li>Using different keywords</li>
                            <li>Checking for typos</li>
                            <li>Selecting a different category</li>
                            <li><a href="<?php echo getBaseUrl(); ?>/search.php">Clear all filters</a></li>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="post-container">
                    <?php foreach ($posts as $post): ?>
                        <article class="post">
                            <h2 class="post-title">
                                <a href="<?php echo getPostUrl($post['post_id'], $post['title']); ?>">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                            </h2>
                            
                            <div class="post-meta">
                                <span><i class="fas fa-user"></i> <?= htmlspecialchars($post['username']) ?></span>
                                <span><i class="fas fa-calendar"></i> <?= date('F j, Y \a\t g:i a', strtotime($post['created_at'])) ?></span>
                            </div>
                            
                            <div class="post-content">
                                <?= htmlspecialchars(substr(strip_tags($post['content']), 0, 200)) ?>...
                            </div>
                            
                            <div class="post-actions">
                                <a href="<?php echo getPostUrl($post['post_id'], $post['title']); ?>" class="button">
                                    <i class="fas fa-book-open"></i> Read More
                                </a>
                                <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $post['author_id'] || $_SESSION['role'] == 'admin')): ?>
                                    <a href="<?php echo getBaseUrl(); ?>/edit_post.php?id=<?= $post['post_id'] ?>" class="button secondary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="<?php echo getBaseUrl(); ?>/delete_post.php?id=<?= $post['post_id'] ?>" class="button delete" 
                                       onclick="return confirm('Are you sure you want to delete this post?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_results > $per_page): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="<?php echo getBaseUrl(); ?>/search.php?query=<?= urlencode($keyword) ?>&category=<?= $category ?>&page=<?= $page-1 ?>" class="button">Previous</a>
                        <?php endif; ?>

                        <?php
                        // Show first page
                        if ($page > 3) {
                            echo "<a href='" . getBaseUrl() . "/search.php?query=" . urlencode($keyword) . "&category=$category&page=1'>1</a>";
                            if ($page > 4) echo "<span>...</span>";
                        }

                        // Show pages around current page
                        for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++) {
                            if ($i == $page) {
                                echo "<span class='current-page'>$i</span>";
                            } else {
                                echo "<a href='" . getBaseUrl() . "/search.php?query=" . urlencode($keyword) . "&category=$category&page=$i'>$i</a>";
                            }
                        }

                        // Show last page
                        if ($page < $total_pages - 2) {
                            if ($page < $total_pages - 3) echo "<span>...</span>";
                            echo "<a href='" . getBaseUrl() . "/search.php?query=" . urlencode($keyword) . "&category=$category&page=$total_pages'>$total_pages</a>";
                        }
                        ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="<?php echo getBaseUrl(); ?>/search.php?query=<?= urlencode($keyword) ?>&category=<?= $category ?>&page=<?= $page+1 ?>" class="button">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
