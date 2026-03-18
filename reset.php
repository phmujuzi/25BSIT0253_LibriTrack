<?php
// LibriTrack - Emergency Reset
// Visit: http://localhost/libritrack/reset.php
// DELETE after use!

$conn = new mysqli('localhost', 'root', '', 'libritrack');

if ($conn->connect_error) {
    die("<div style='font-family:Arial;padding:30px;background:#fee;border:2px solid red;border-radius:8px;margin:20px'>
        <h2>❌ Cannot connect to database</h2>
        <p>Make sure XAMPP MySQL is running and database <strong>libritrack</strong> exists in phpMyAdmin.</p>
        <p style='color:#888'>Error: " . $conn->connect_error . "</p>
    </div>");
}

$msg = '';
$msgtype = '';

// Just reset everything to defaults on page load automatically
if (isset($_GET['reset'])) {
    $pass = password_hash('password', PASSWORD_DEFAULT);
    
    // Check if admin exists
    $count = $conn->query("SELECT COUNT(*) FROM admins")->fetch_row()[0];
    if ($count == 0) {
        $conn->query("INSERT INTO admins (username, password, full_name) VALUES ('admin', '$pass', 'Library Administrator')");
        $msg = "Admin account created!";
    } else {
        $conn->query("UPDATE admins SET username='admin', password='$pass'");
        $msg = "Admin reset to: username=<strong>admin</strong> password=<strong>password</strong>";
    }
    
    // Reset all students too
    $conn->query("UPDATE students SET password='$pass'");
    $msg .= "<br>All student passwords reset to: <strong>password</strong>";
    $msgtype = 'ok';
}

// Custom reset via form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username']);
    $p = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $count = $conn->query("SELECT COUNT(*) FROM admins")->fetch_row()[0];
    if ($count == 0) {
        $conn->query("INSERT INTO admins (username, password, full_name) VALUES ('$u', '$p', 'Library Administrator')");
    } else {
        $stmt = $conn->prepare("UPDATE admins SET username=?, password=? WHERE id=1");
        $stmt->bind_param('ss', $u, $p);
        $stmt->execute();
    }
    $msg = "Admin updated — username: <strong>$u</strong> password: <strong>" . htmlspecialchars($_POST['password']) . "</strong>";
    $msgtype = 'ok';
}

$admins   = $conn->query("SELECT id, username, full_name FROM admins")->fetch_all(MYSQLI_ASSOC);
$students = $conn->query("SELECT student_id, full_name, status FROM students")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>LibriTrack Reset</title>
<style>
* { margin:0; padding:0; box-sizing:border-box }
body { font-family:Arial,sans-serif; background:#f0f2f0; padding:30px; }
h1 { color:#1b4332; margin-bottom:6px; }
.sub { color:#888; font-size:13px; margin-bottom:24px; }
.warn { background:#fff3cd; border:2px solid #ffc107; border-radius:8px; padding:14px; margin-bottom:20px; font-size:14px; color:#856404; }
.ok  { background:#d4edda; border:1px solid #27ae60; border-radius:8px; padding:14px; margin-bottom:20px; font-size:14px; color:#155724; }
.big-btn { display:inline-block; padding:16px 32px; background:#1b4332; color:#fff; border-radius:10px; text-decoration:none; font-size:16px; font-weight:bold; margin-bottom:24px; }
.big-btn:hover { background:#2d6a4f; }
.grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
.card { background:#fff; border-radius:10px; padding:20px; border:1px solid #e0e0e0; }
h2 { font-size:15px; color:#1b4332; border-bottom:1px solid #eee; padding-bottom:8px; margin-bottom:14px; }
label { display:block; font-size:12px; font-weight:bold; color:#555; margin-bottom:4px; }
input { width:100%; padding:9px; border:1px solid #ccc; border-radius:6px; font-size:14px; margin-bottom:12px; }
input:focus { outline:none; border-color:#1b4332; }
.btn { padding:10px 22px; background:#1b4332; color:#fff; border:none; border-radius:6px; font-size:14px; font-weight:bold; cursor:pointer; }
.btn:hover { background:#2d6a4f; }
table { width:100%; border-collapse:collapse; font-size:13px; }
th { background:#f8f9f8; padding:8px; text-align:left; border-bottom:1px solid #eee; font-size:11px; text-transform:uppercase; color:#888; }
td { padding:8px; border-bottom:1px solid #f5f5f5; }
.tag { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:bold; }
.active { background:#d4edda; color:#155724; }
.goto { margin-top:20px; text-align:center; }
.goto a { color:#1b4332; font-weight:bold; font-size:15px; }
</style>
</head>
<body>

<h1>🔧 LibriTrack — Reset Utility</h1>
<p class="sub">Fix login credentials instantly. Delete this file after use.</p>

<div class="warn">⚠ Delete <code>reset.php</code> from htdocs after you fix your login.</div>

<?php if ($msg): ?>
<div class="<?= $msgtype ?>"><?= $msg ?> — <a href="index.php" style="color:#155724;font-weight:bold">Go Login Now →</a></div>
<?php endif; ?>

<!-- ONE CLICK RESET -->
<a href="?reset=1" class="big-btn" onclick="return confirm('Reset admin to admin/password and all students to password?')">
    ⚡ One-Click Reset Everything to Defaults
</a>

<div class="grid">

    <!-- Custom admin reset -->
    <div class="card">
        <h2>🔑 Set Custom Admin Password</h2>
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" value="admin" required>
            <label>New Password</label>
            <input type="password" name="password" placeholder="Type new password" required>
            <button type="submit" class="btn">Reset Admin</button>
        </form>
    </div>

    <!-- Current accounts -->
    <div class="card">
        <h2>👤 Admin Accounts</h2>
        <?php if (empty($admins)): ?>
        <p style="color:#aaa;font-size:13px">No admins — click the big button above.</p>
        <?php else: ?>
        <table>
            <thead><tr><th>ID</th><th>Username</th><th>Name</th></tr></thead>
            <tbody>
            <?php foreach ($admins as $a): ?>
            <tr><td><?= $a['id'] ?></td><td><strong><?= htmlspecialchars($a['username']) ?></strong></td><td><?= htmlspecialchars($a['full_name']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <h2 style="margin-top:16px">🎓 Students (<?= count($students) ?>)</h2>
        <?php if (empty($students)): ?>
        <p style="color:#aaa;font-size:13px">No students — re-import database.sql.</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Student ID</th><th>Name</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($students as $s): ?>
            <tr>
                <td><strong><?= htmlspecialchars($s['student_id']) ?></strong></td>
                <td><?= htmlspecialchars($s['full_name']) ?></td>
                <td><span class="tag active"><?= ucfirst($s['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<div class="goto"><a href="index.php">→ Go to LibriTrack Login</a></div>

</body>
</html>
