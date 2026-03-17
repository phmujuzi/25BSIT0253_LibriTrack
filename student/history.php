<?php
require_once '../includes/config.php';
requireStudent();
$sid = $_SESSION['student_id'];

$history = $conn->query("
    SELECT br.*, b.title, b.author, b.category,
           DATEDIFF(CURDATE(), br.due_date) AS days_late
    FROM borrowings br
    JOIN books b ON br.book_id = b.id
    WHERE br.student_id = $sid
    ORDER BY br.id DESC
")->fetch_all(MYSQLI_ASSOC);

include 'partials/header.php';
?>

<div class="page-title">Borrowing History</div>

<div class="card">
    <div class="card-head">All Borrowings (<?= count($history) ?>)</div>
    <?php if (empty($history)): ?>
    <p style="padding:32px;text-align:center;color:#aaa">No borrowing history yet.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr><th>#</th><th>Book</th><th>Category</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($history as $i => $r):
            $late = ($r['status']==='borrowed' && $r['days_late'] > 0);
            $badge = $late ? 'overdue' : $r['status'];
        ?>
        <tr>
            <td style="color:#ccc"><?= $i+1 ?></td>
            <td><strong><?= htmlspecialchars($r['title']) ?></strong><small><?= htmlspecialchars($r['author']) ?></small></td>
            <td><span style="background:#e8f5ee;color:#1b4332;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600"><?= htmlspecialchars($r['category']) ?></span></td>
            <td><?= date('d M Y', strtotime($r['borrow_date'])) ?></td>
            <td><?= date('d M Y', strtotime($r['due_date'])) ?></td>
            <td><?= $r['return_date'] ? date('d M Y', strtotime($r['return_date'])) : '—' ?></td>
            <td><span class="badge badge-<?= $badge ?>"><?= ucfirst($badge) ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>
