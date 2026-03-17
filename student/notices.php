<?php
require_once '../includes/config.php';
requireStudent();

$notices = $conn->query("SELECT * FROM notices ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
include 'partials/header.php';
?>

<div class="page-title">Library Notices</div>

<?php if (empty($notices)): ?>
<div class="card" style="text-align:center;padding:48px">
    <div style="font-size:40px;margin-bottom:12px">📭</div>
    <p style="color:#aaa">No notices at this time.</p>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:14px">
<?php foreach ($notices as $n): ?>
<div class="card">
    <div style="padding:20px 22px">
        <div style="font-family:'Playfair Display',serif;font-size:17px;font-weight:700;color:#1b4332;margin-bottom:8px">
            <?= htmlspecialchars($n['title']) ?>
        </div>
        <div style="font-size:14px;color:#444;line-height:1.7">
            <?= nl2br(htmlspecialchars($n['body'])) ?>
        </div>
        <div style="font-size:12px;color:#aaa;margin-top:10px">
            📅 <?= date('d M Y, g:ia', strtotime($n['created_at'])) ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php include 'partials/footer.php'; ?>
