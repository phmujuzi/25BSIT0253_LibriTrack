<?php
require_once '../includes/config.php';
requireStudent();

$search = trim($_GET['search'] ?? '');
$cat    = trim($_GET['category'] ?? '');

$where = [];
if ($search) {
    $s = $conn->real_escape_string($search);
    $where[] = "(title LIKE '%$s%' OR author LIKE '%$s%')";
}
if ($cat) {
    $c = $conn->real_escape_string($cat);
    $where[] = "category = '$c'";
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$books = $conn->query("SELECT * FROM books $whereSQL ORDER BY title ASC")->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT DISTINCT category FROM books ORDER BY category")->fetch_all(MYSQLI_ASSOC);

include 'partials/header.php';
?>

<div class="page-title">Browse Catalog</div>

<form method="GET" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap">
    <input type="text" name="search" placeholder="🔍 Search by title or author…"
           value="<?= htmlspecialchars($search) ?>"
           style="flex:1;min-width:200px;padding:9px 14px;border:1px solid #ddd;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13.5px;outline:none">
    <select name="category"
            style="padding:9px 14px;border:1px solid #ddd;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13.5px;outline:none">
        <option value="">All Categories</option>
        <?php foreach ($categories as $c): ?>
        <option value="<?= htmlspecialchars($c['category']) ?>" <?= $cat===$c['category']?'selected':'' ?>>
            <?= htmlspecialchars($c['category']) ?>
        </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-green">Search</button>
    <?php if ($search || $cat): ?>
    <a href="catalog.php" class="btn" style="background:#f0f0f0;color:#555">Clear</a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="card-head">Books (<?= count($books) ?>)</div>
    <table>
        <thead>
            <tr><th>Title</th><th>Author</th><th>Category</th><th>Shelf</th><th>Availability</th></tr>
        </thead>
        <tbody>
        <?php foreach ($books as $b): ?>
        <tr>
            <td>
                <strong><?= htmlspecialchars($b['title']) ?></strong>
                <?php if ($b['isbn']): ?><small>ISBN: <?= htmlspecialchars($b['isbn']) ?></small><?php endif; ?>
            </td>
            <td><?= htmlspecialchars($b['author']) ?></td>
            <td>
                <span style="background:#e8f5ee;color:#1b4332;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600">
                    <?= htmlspecialchars($b['category']) ?>
                </span>
            </td>
            <td><?= htmlspecialchars($b['shelf'] ?: '—') ?></td>
            <td>
                <?php if ($b['copies_available'] > 0): ?>
                <span style="color:#27ae60;font-weight:700">✓ <?= $b['copies_available'] ?> available</span>
                <?php else: ?>
                <span style="color:#e74c3c;font-weight:700">✗ All out</span>
                <?php endif; ?>
                <small><?= $b['copies_total'] ?> total copies</small>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($books)): ?>
        <tr><td colspan="5" style="text-align:center;padding:32px;color:#aaa">No books found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'partials/footer.php'; ?>
