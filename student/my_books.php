<?php
require_once '../includes/config.php';
requireStudent();
$sid = $_SESSION['student_id'];

$books = $conn->query("
    SELECT br.*, b.title, b.author, b.category, b.isbn, b.shelf,
           DATEDIFF(br.due_date, CURDATE()) AS days_left,
           DATEDIFF(CURDATE(), br.due_date) AS days_late
    FROM borrowings br
    JOIN books b ON br.book_id = b.id
    WHERE br.student_id = $sid AND br.status = 'borrowed'
    ORDER BY br.due_date ASC
")->fetch_all(MYSQLI_ASSOC);

include 'partials/header.php';
?>

<div class="page-title">My Current Books</div>

<?php if (empty($books)): ?>
<div class="card" style="text-align:center;padding:64px">
    <div style="font-size:48px;margin-bottom:14px">📭</div>
    <p style="color:#888;font-size:15px">You have no books currently borrowed.</p>
    <a href="catalog.php" class="btn btn-green" style="margin-top:16px">Browse Catalog →</a>
</div>
<?php else: ?>
<div class="card">
    <div class="card-head">Borrowed Books (<?= count($books) ?>)</div>
    <table>
        <thead>
            <tr><th>Book</th><th>Category</th><th>Borrowed On</th><th>Due Date</th><th>Shelf</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($books as $b):
            $late = $b['days_late'] > 0;
            $soon = !$late && $b['days_left'] <= 3;
        ?>
        <tr>
            <td>
                <strong><?= htmlspecialchars($b['title']) ?></strong>
                <small><?= htmlspecialchars($b['author']) ?></small>
            </td>
            <td><span style="background:#e8f5ee;color:#1b4332;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600"><?= htmlspecialchars($b['category']) ?></span></td>
            <td><?= date('d M Y', strtotime($b['borrow_date'])) ?></td>
            <td>
                <span style="font-weight:600;color:<?= $late?'#e74c3c':($soon?'#e67e22':'#27ae60') ?>">
                    <?= date('d M Y', strtotime($b['due_date'])) ?>
                </span>
                <?php if ($late): ?>
                <br><small style="color:#e74c3c;font-weight:700">⚠ <?= $b['days_late'] ?> day<?= $b['days_late']!=1?'s':'' ?> overdue · Fine: UGX <?= number_format($b['days_late'] * FINE_PER_DAY) ?></small>
                <?php elseif ($soon): ?>
                <br><small style="color:#e67e22">⏰ <?= $b['days_left'] ?> day<?= $b['days_left']!=1?'s':'' ?> remaining</small>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($b['shelf'] ?: '—') ?></td>
            <td><span class="badge badge-<?= $late?'overdue':'borrowed' ?>"><?= $late?'Overdue':'Borrowed' ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div style="margin-top:16px;padding:14px 18px;background:#fef3cd;border-radius:10px;font-size:13px;color:#856404">
    <strong>Reminder:</strong> Return books by the due date to avoid fines of UGX <?= number_format(FINE_PER_DAY) ?> per day. Visit the library counter to return your books.
</div>
<?php endif; ?>

<?php include 'partials/footer.php'; ?>
