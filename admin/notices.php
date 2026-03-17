<?php
require_once '../includes/config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action']??'') === 'add') {
        $title = trim($_POST['title']);
        $body  = trim($_POST['body']);
        $stmt  = $conn->prepare("INSERT INTO notices (title, body) VALUES (?,?)");
        $stmt->bind_param('ss', $title, $body);
        $stmt->execute();
        setFlash('Notice posted.');
    }
    header('Location: notices.php'); exit;
}

if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM notices WHERE id=" . (int)$_GET['delete']);
    setFlash('Notice deleted.');
    header('Location: notices.php'); exit;
}

$notices = $conn->query("SELECT * FROM notices ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
include 'partials/header.php';
?>

<div class="page-title">Library Notices</div>

<div class="two-col">
    <div class="card">
        <div class="card-head">Active Notices (<?= count($notices) ?>)</div>
        <?php if (empty($notices)): ?>
        <p style="padding:24px;color:#aaa;text-align:center">No notices yet.</p>
        <?php endif; ?>
        <?php foreach ($notices as $n): ?>
        <div style="padding:16px 20px;border-bottom:1px solid #f5f5f5">
            <div style="display:flex;justify-content:space-between;align-items:flex-start">
                <div>
                    <div style="font-weight:700;font-size:14px;margin-bottom:4px"><?= e($n['title']) ?></div>
                    <div style="font-size:13.5px;color:#555;line-height:1.6"><?= nl2br(e($n['body'])) ?></div>
                    <div style="font-size:11.5px;color:#aaa;margin-top:6px"><?= date('d M Y, g:ia', strtotime($n['created_at'])) ?></div>
                </div>
                <a href="?delete=<?= $n['id'] ?>" class="btn btn-red btn-sm" style="margin-left:12px;flex-shrink:0"
                   onclick="return confirm('Delete this notice?')">Delete</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <div class="card-head">Post New Notice</div>
        <div style="padding:20px">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Library closed on Monday">
                </div>
                <div class="form-group">
                    <label>Message *</label>
                    <textarea name="body" required placeholder="Write the notice here…" style="min-height:120px"></textarea>
                </div>
                <button type="submit" class="btn btn-green">📢 Post Notice</button>
            </form>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
