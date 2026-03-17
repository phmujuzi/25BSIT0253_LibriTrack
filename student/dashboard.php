<?php
require_once '../includes/config.php';
requireStudent();
$sid = $_SESSION['student_id'];

$borrowed   = $conn->query("SELECT COUNT(*) FROM borrowings WHERE student_id=$sid AND status='borrowed'")->fetch_row()[0];
$overdue    = $conn->query("SELECT COUNT(*) FROM borrowings WHERE student_id=$sid AND status='borrowed' AND due_date < CURDATE()")->fetch_row()[0];
$total_ever = $conn->query("SELECT COUNT(*) FROM borrowings WHERE student_id=$sid")->fetch_row()[0];

// Current books
$current = $conn->query("
    SELECT br.*, b.title, b.author, b.category, b.shelf,
           DATEDIFF(br.due_date, CURDATE()) AS days_left,
           DATEDIFF(CURDATE(), br.due_date) AS days_late
    FROM borrowings br
    JOIN books b ON br.book_id = b.id
    WHERE br.student_id = $sid AND br.status = 'borrowed'
    ORDER BY br.due_date ASC
")->fetch_all(MYSQLI_ASSOC);

// Notices
$notices = $conn->query("SELECT * FROM notices ORDER BY created_at DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);

include 'partials/header.php';
?>

<div class="page-title">Welcome, <?= htmlspecialchars(explode(' ', $_SESSION['student_name'])[0]) ?>! 👋</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📖</div>
        <div class="stat-num"><?= $borrowed ?></div>
        <div class="stat-label">Currently Borrowed</div>
    </div>
    <div class="stat-card" style="border-left-color:<?= $overdue>0?'#e74c3c':'#1b4332' ?>">
        <div class="stat-icon">⚠️</div>
        <div class="stat-num" style="color:<?= $overdue>0?'#e74c3c':'#1b4332' ?>"><?= $overdue ?></div>
        <div class="stat-label">Overdue Books</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📚</div>
        <div class="stat-num"><?= $total_ever ?></div>
        <div class="stat-label">Total Books Borrowed</div>
    </div>
</div>

<div class="two-col">
    <!-- Current books -->
    <div class="card">
        <div class="card-head">📖 My Current Books</div>
        <?php if (empty($current)): ?>
        <div style="padding:32px;text-align:center;color:#aaa">
            <div style="font-size:40px;margin-bottom:10px">📭</div>
            <p>No books borrowed.<br><a href="catalog.php" style="color:#1b4332;font-weight:600">Browse the catalog →</a></p>
        </div>
        <?php else: ?>
        <?php foreach ($current as $b):
            $late = $b['days_late'] > 0;
            $soon = !$late && $b['days_left'] <= 3;
        ?>
        <div style="padding:16px 20px;border-bottom:1px solid #f5f5f5;border-left:4px solid <?= $late?'#e74c3c':($soon?'#f39c12':'#27ae60') ?>">
            <div style="font-weight:700;font-size:14px"><?= htmlspecialchars($b['title']) ?></div>
            <div style="font-size:13px;color:#888;margin:3px 0">by <?= htmlspecialchars($b['author']) ?></div>
            <div style="font-size:12.5px;margin-top:6px;display:flex;gap:16px;flex-wrap:wrap">
                <span>📅 Due: <strong style="color:<?= $late?'#e74c3c':($soon?'#e67e22':'#27ae60') ?>">
                    <?= date('d M Y', strtotime($b['due_date'])) ?>
                </strong></span>
                <?php if ($late): ?>
                <span style="color:#e74c3c;font-weight:700">⚠ <?= $b['days_late'] ?> day<?= $b['days_late']!=1?'s':'' ?> overdue</span>
                <?php elseif ($soon): ?>
                <span style="color:#e67e22;font-weight:600">⏰ <?= $b['days_left'] ?> day<?= $b['days_left']!=1?'s':'' ?> left</span>
                <?php else: ?>
                <span style="color:#27ae60"><?= $b['days_left'] ?> days left</span>
                <?php endif; ?>
                <span style="color:#aaa">Shelf: <?= htmlspecialchars($b['shelf'] ?: '—') ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Notices -->
    <div class="card">
        <div class="card-head">📢 Library Notices</div>
        <?php if (empty($notices)): ?>
        <p style="padding:24px;color:#aaa;text-align:center">No notices.</p>
        <?php endif; ?>
        <?php foreach ($notices as $n): ?>
        <div style="padding:16px 20px;border-bottom:1px solid #f5f5f5">
            <div style="font-weight:700;font-size:13.5px;margin-bottom:4px"><?= htmlspecialchars($n['title']) ?></div>
            <div style="font-size:13px;color:#555;line-height:1.6"><?= nl2br(htmlspecialchars($n['body'])) ?></div>
            <div style="font-size:11.5px;color:#aaa;margin-top:6px"><?= date('d M Y', strtotime($n['created_at'])) ?></div>
        </div>
        <?php endforeach; ?>
        <?php if (!empty($notices)): ?>
        <div style="padding:12px 20px"><a href="notices.php" style="font-size:13px;color:#1b4332;font-weight:600">View all →</a></div>
        <?php endif; ?>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
