<?php
require_once '../includes/config.php';
requireAdmin();

$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$where = [];
if ($filter === 'borrowed') $where[] = "br.status='borrowed' AND br.due_date >= CURDATE()";
elseif ($filter === 'overdue') $where[] = "br.status='borrowed' AND br.due_date < CURDATE()";
elseif ($filter === 'returned') $where[] = "br.status='returned'";

if ($search) {
    $s = $conn->real_escape_string($search);
    $where[] = "(s.full_name LIKE '%$s%' OR s.student_id LIKE '%$s%' OR b.title LIKE '%$s%')";
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$rows = $conn->query("
    SELECT br.*, s.full_name, s.student_id AS sid, b.title, b.author,
           DATEDIFF(CURDATE(), br.due_date) AS days_late
    FROM borrowings br
    JOIN students s ON br.student_id = s.id
    JOIN books b ON br.book_id = b.id
    $whereSQL
    ORDER BY br.id DESC
    LIMIT 200
")->fetch_all(MYSQLI_ASSOC);

$counts = [];
foreach (['all'=>'', 'borrowed'=>"status='borrowed' AND due_date >= CURDATE()", 'overdue'=>"status='borrowed' AND due_date < CURDATE()", 'returned'=>"status='returned'"] as $k=>$w) {
    $counts[$k] = $conn->query("SELECT COUNT(*) FROM borrowings" . ($w ? " WHERE $w" : ''))->fetch_row()[0];
}

include 'partials/header.php';
?>

<div class="page-title">All Borrowings</div>

<!-- Filter tabs -->
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
    <?php foreach (['all'=>'All','borrowed'=>'Active','overdue'=>'Overdue','returned'=>'Returned'] as $k=>$label): ?>
    <a href="?filter=<?= $k ?>&search=<?= urlencode($search) ?>"
       style="padding:7px 16px;border-radius:20px;text-decoration:none;font-size:13px;font-weight:600;
              background:<?= $filter===$k?'#1b4332':'#fff' ?>;
              color:<?= $filter===$k?'#fff':'#555' ?>;
              border:1px solid <?= $filter===$k?'#1b4332':'#ddd' ?>">
        <?= $label ?> (<?= $counts[$k] ?>)
    </a>
    <?php endforeach; ?>
</div>

<form class="search-bar" method="GET" style="margin-bottom:16px">
    <input type="hidden" name="filter" value="<?= e($filter) ?>">
    <input type="text" name="search" placeholder="🔍 Search by student, ID, or book title…" value="<?= e($search) ?>">
    <button type="submit" class="btn btn-green">Search</button>
    <?php if ($search): ?><a href="?filter=<?= e($filter) ?>" class="btn" style="background:#f0f0f0;color:#555">Clear</a><?php endif; ?>
</form>

<div class="card">
    <div class="card-head">Records (<?= count($rows) ?>)</div>
    <table>
        <thead>
            <tr><th>#</th><th>Student</th><th>Book</th><th>Borrowed</th><th>Due Date</th><th>Returned</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $i => $r):
            $late = ($r['status']==='borrowed' && $r['days_late'] > 0);
            $badge = $late ? 'overdue' : $r['status'];
        ?>
        <tr>
            <td style="color:#ccc"><?= $i+1 ?></td>
            <td><?= e($r['full_name']) ?><br><small><?= e($r['sid']) ?></small></td>
            <td><?= e($r['title']) ?><br><small><?= e($r['author']) ?></small></td>
            <td><?= date('d M Y', strtotime($r['borrow_date'])) ?></td>
            <td style="color:<?= $late?'#e74c3c':'inherit' ?>">
                <?= date('d M Y', strtotime($r['due_date'])) ?>
                <?php if ($late): ?><br><small style="color:#e74c3c"><?= $r['days_late'] ?>d overdue</small><?php endif; ?>
            </td>
            <td><?= $r['return_date'] ? date('d M Y', strtotime($r['return_date'])) : '—' ?></td>
            <td><span class="badge badge-<?= $badge ?>"><?= ucfirst($badge) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
        <tr><td colspan="7" style="text-align:center;padding:32px;color:#aaa">No records found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'partials/footer.php'; ?>
