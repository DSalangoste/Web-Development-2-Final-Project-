<?php
session_start();
require_once 'db.php';
require_once 'session.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Handle category creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create' && !empty($_POST['category_name'])) {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
                $stmt->execute([trim($_POST['category_name'])]);
                $success = "Category created successfully!";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    $error = "This category already exists.";
                } else {
                    $error = "Error creating category.";
                }
            }
        } elseif ($_POST['action'] === 'delete' && !empty($_POST['category_id'])) {
            try {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
                $stmt->execute([$_POST['category_id']]);
                $success = "Category deleted successfully!";
            } catch (PDOException $e) {
                $error = "Error deleting category.";
            }
        } elseif ($_POST['action'] === 'update' && !empty($_POST['category_id']) && isset($_POST['category_name'])) {
            try {
                $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE category_id = ?");
                $stmt->execute([trim($_POST['category_name']), $_POST['category_id']]);
                $success = "Category updated successfully!";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "This category name already exists.";
                } else {
                    $error = "Error updating category.";
                }
            }
        }
    }
}

// Fetch all categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Manage Categories";
include 'includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-tags"></i> Manage Categories</h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="category-section">
        <!-- Create Category Form -->
        <div class="category-form">
            <h2>Create New Category</h2>
            <form action="manage_categories.php" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label for="category_name">Category Name:</label>
                    <input type="text" id="category_name" name="category_name" required>
                </div>
                <button type="submit" class="button primary">
                    <i class="fas fa-plus"></i> Create Category
                </button>
            </form>
        </div>

        <!-- Categories List -->
        <div class="categories-list">
            <h2>Existing Categories</h2>
            <?php if (count($categories) > 0): ?>
                <div class="category-items">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-item" id="category-<?php echo $category['category_id']; ?>">
                            <form action="manage_categories.php" method="POST" class="category-edit-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                <input type="text" name="category_name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                <div class="category-actions">
                                    <button type="submit" class="button secondary" title="Update">
                                        <i class="fas fa-save"></i>
                                    </button>
                                    <button type="submit" formaction="manage_categories.php" 
                                            formmethod="POST" 
                                            onclick="this.form.action.value='delete';" 
                                            class="button delete" 
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-categories">No categories found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.category-section {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2rem;
    margin-top: 2rem;
}

.category-form {
    background: #fff;
    padding: 1.5rem;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.category-form h2 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    color: #333;
}

.categories-list {
    background: #fff;
    padding: 1.5rem;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.categories-list h2 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    color: #333;
}

.category-items {
    display: grid;
    gap: 1rem;
}

.category-item {
    background: #f8f9fa;
    border-radius: 4px;
    overflow: hidden;
}

.category-edit-form {
    display: flex;
    align-items: center;
    padding: 0.5rem;
}

.category-edit-form input[type="text"] {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-right: 0.5rem;
}

.category-actions {
    display: flex;
    gap: 0.5rem;
}

.category-actions button {
    padding: 0.5rem;
    min-width: 40px;
}

.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
    font-weight: 500;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.no-categories {
    text-align: center;
    color: #666;
    padding: 1rem;
}

@media (max-width: 768px) {
    .category-section {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
