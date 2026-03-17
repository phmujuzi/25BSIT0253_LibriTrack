<?php
require_once '../includes/config.php';
requireAdmin();

// Stats
$total_books     = $conn->query("SELECT COUNT(*) FROM books")->fetch_row()[0];
$total_students  = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0];
$total_borrowed  = $conn->query("SELECT COUNT(*) FROM borrowings WHERE status='borrowed'")->fetch_row()[0];
$total_overdue   = $conn->query("SELECT COUNT(*) FROM borrowings WHERE status='overdue' OR (status='borrowed' AND due_date < CURDATE())")->fetch_row()[0];

// Recent borrowings
$recent = $conn->query("
    SELECT br.*, s.full_name, s.student_id AS sid, b.title
    FROM borrowings br
    JOIN students s ON br.student_id = s.id
    JOIN books b ON br.book_id = b.id
    ORDER BY br.id DESC LIMIT 8
")->fetch_all(MYSQLI_ASSOC);

// Overdue list
$overdue = $conn->query("
    SELECT br.*, s.full_name, s.student_id AS sid, b.title,
           DATEDIFF(CURDATE(), br.due_date) AS days_late
    FROM borrowings br
    JOIN students s ON br.student_id = s.id
    JOIN books b ON br.book_id = b.id
    WHERE br.status='borrowed' AND br.due_date < CURDATE()
    ORDER BY days_late DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$flash = getFlash();
include 'partials/header.php';
?>

<div class="page-title">Dashboard</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📚</div>
        <div class="stat-num"><?= $total_books ?></div>
        <div class="stat-label">Total Books</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🎓</div>
        <div class="stat-num"><?= $total_students ?></div>
        <div class="stat-label">Students</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📖</div>
        <div class="stat-num"><?= $total_borrowed ?></div>
        <div class="stat-label">Currently Borrowed</div>
    </div>
    <div class="stat-card <?= $total_overdue > 0 ? 'stat-danger' : '' ?>">
        <div class="stat-icon">⚠️</div>
        <div class="stat-num"><?= $total_overdue ?></div>
        <div class="stat-label">Overdue</div>
    </div>
</div>

<div class="two-col">
    <!-- Recent borrowings -->
    <div class="card">
        <div class="card-head">Recent Borrowings</div>
        <table>
            <thead><tr><th>Student</th><th>Book</th><th>Due Date</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($recent as $r):
                $overdue_flag = ($r['status'] === 'borrowed' && $r['due_date'] < date('Y-m-d'));
                $badge = $overdue_flag ? 'overdue' : $r['status'];
            ?>
            <tr>
                <td><?= e($r['full_name']) ?><br><small><?= e($r['sid']) ?></small></td>
                <td><?= e(substr($r['title'],0,35)) ?><?= strlen($r['title'])>35?'…':'' ?></td>
                <td><?= date('d M Y', strtotime($r['due_date'])) ?></td>
                <td><span class="badge badge-<?= $badge ?>"><?= ucfirst($badge) ?></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recent)): ?>
            <tr><td colspan="4" style="text-align:center;color:#aaa;padding:24px">No borrowings yet</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Overdue -->
    <div class="card">
        <div class="card-head" style="color:#e74c3c">⚠ Overdue Books</div>
        <?php if (empty($overdue)): ?>
        <p style="padding:20px;color:#aaa;text-align:center">No overdue books 🎉</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Student</th><th>Book</th><th>Days Late</th></tr></thead>
            <tbody>
            <?php foreach ($overdue as $o): ?>
            <tr>
                <td><?= e($o['full_name']) ?></td>
                <td><?= e(substr($o['title'],0,30)) ?><?= strlen($o['title'])>30?'…':'' ?></td>
                <td style="color:#e74c3c;font-weight:700"><?= $o['days_late'] ?> days</td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
