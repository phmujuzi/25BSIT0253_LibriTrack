<?php
require_once '../includes/config.php';
requireAdmin();

// Add book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $title  = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn   = trim($_POST['isbn']);
    $cat    = trim($_POST['category']);
    $copies = max(1, (int)$_POST['copies']);
    $shelf  = trim($_POST['shelf']);
    $desc   = trim($_POST['description']);
    $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, category, copies_total, copies_available, shelf, description) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param('ssssiiss', $title, $author, $isbn, $cat, $copies, $copies, $shelf, $desc);
    $stmt->execute();
    setFlash("Book '$title' added successfully.");
    header('Location: books.php'); exit;
}

// Delete book
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $in_use = $conn->query("SELECT COUNT(*) FROM borrowings WHERE book_id=$id AND status='borrowed'")->fetch_row()[0];
    if ($in_use > 0) {
        setFlash('Cannot delete — book is currently borrowed.', 'error');
    } else {
        $conn->query("DELETE FROM books WHERE id=$id");
        setFlash('Book deleted.');
    }
    header('Location: books.php'); exit;
}

$search = trim($_GET['search'] ?? '');
$where  = $search ? "WHERE b.title LIKE '%" . $conn->real_escape_string($search) . "%' OR b.author LIKE '%" . $conn->real_escape_string($search) . "%' OR b.category LIKE '%" . $conn->real_escape_string($search) . "%'" : '';
$books  = $conn->query("SELECT * FROM books $where ORDER BY title ASC")->fetch_all(MYSQLI_ASSOC);

// Categories for dropdown
$cats = $conn->query("SELECT DISTINCT category FROM books ORDER BY category")->fetch_all(MYSQLI_ASSOC);

include 'partials/header.php';
?>

<div class="page-title">Books Catalog</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <form class="search-bar" method="GET" style="margin:0;flex:1;max-width:400px">
        <input type="text" name="search" placeholder="🔍 Search books…" value="<?= e($search) ?>">
        <button type="submit" class="btn btn-green">Search</button>
        <?php if ($search): ?><a href="books.php" class="btn" style="background:#f0f0f0;color:#555">Clear</a><?php endif; ?>
    </form>
    <button class="btn btn-green" onclick="document.getElementById('add-modal').classList.add('open')">+ Add Book</button>
</div>

<div class="card">
    <div class="card-head">All Books (<?= count($books) ?>)</div>
    <table>
        <thead>
            <tr><th>Title</th><th>Author</th><th>Category</th><th>Copies</th><th>Available</th><th>Shelf</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($books as $b): ?>
        <tr>
            <td><strong><?= e($b['title']) ?></strong></td>
            <td><?= e($b['author']) ?></td>
            <td><span style="background:#e8f5ee;color:#1b4332;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600"><?= e($b['category']) ?></span></td>
            <td><?= $b['copies_total'] ?></td>
            <td>
                <span style="font-weight:700;color:<?= $b['copies_available']>0?'#27ae60':'#e74c3c' ?>">
                    <?= $b['copies_available'] ?>
                </span>
            </td>
            <td><?= e($b['shelf']) ?></td>
            <td>
                <a href="?delete=<?= $b['id'] ?>" class="btn btn-red btn-sm"
                   onclick="return confirm('Delete this book?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($books)): ?>
        <tr><td colspan="7" style="text-align:center;padding:32px;color:#aaa">No books found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Book Modal -->
<div class="modal-overlay" id="add-modal">
    <div class="modal">
        <button class="modal-close" onclick="document.getElementById('add-modal').classList.remove('open')">✕</button>
        <h3>Add New Book</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-row">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Author *</label>
                    <input type="text" name="author" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Category *</label>
                    <input type="text" name="category" list="cat-list" required>
                    <datalist id="cat-list">
                        <?php foreach ($cats as $c): ?>
                        <option value="<?= e($c['category']) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Number of Copies</label>
                    <input type="number" name="copies" value="1" min="1">
                </div>
                <div class="form-group">
                    <label>Shelf Location</label>
                    <input type="text" name="shelf" placeholder="e.g. A1">
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"></textarea>
            </div>
            <button type="submit" class="btn btn-green">Add Book</button>
        </form>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
