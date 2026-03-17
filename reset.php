<?php
// ============================================================
// LibriTrack — Password Reset Utility
// Place in: C:\xampp\htdocs\libritrack\reset.php
// Visit:    http://localhost/libritrack/reset.php
// DELETE this file after you are done!
// ============================================================

$conn = new mysqli('localhost', 'root', '', 'libritrack');
$db_error = $conn->connect_error;

$done = []; $errors = [];

if (!$db_error && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'reset_admin') {
        $u = trim($_POST['admin_username']);
        $p = password_hash(trim($_POST['admin_password']), PASSWORD_DEFAULT);
        $count = $conn->query("SELECT COUNT(*) FROM admins")->fetch_row()[0];
        if ($count == 0) {
            $s = $conn->prepare("INSERT INTO admins (username,password,full_name) VALUES (?,?,'Library Administrator')");
            $s->bind_param('ss',$u,$p); $s->execute();
            $done[] = "Admin account created — username: <strong>$u</strong>";
        } else {
            $s = $conn->prepare("UPDATE admins SET username=?,password=? WHERE id=1");
            $s->bind_param('ss',$u,$p); $s->execute();
            $done[] = "Admin (id=1) updated — username: <strong>$u</strong>";
        }
    }

    if ($action === 'add_admin') {
        $u = trim($_POST['new_u']); $p = password_hash(trim($_POST['new_p']),PASSWORD_DEFAULT);
        $n = trim($_POST['new_n']) ?: 'Administrator';
        $c = $conn->prepare("SELECT id FROM admins WHERE username=?");
        $c->bind_param('s',$u); $c->execute();
        if ($c->get_result()->fetch_assoc()) {
            $errors[] = "Username <strong>$u</strong> already exists.";
        } else {
            $s = $conn->prepare("INSERT INTO admins (username,password,full_name) VALUES (?,?,?)");
            $s->bind_param('sss',$u,$p,$n); $s->execute();
            $done[] = "New admin added — username: <strong>$u</strong>";
        }
    }

    if ($action === 'reset_one') {
        $sid = trim($_POST['student_id']);
        $p   = password_hash(trim($_POST['student_pass']),PASSWORD_DEFAULT);
        $s   = $conn->prepare("UPDATE students SET password=? WHERE student_id=?");
        $s->bind_param('ss',$p,$sid); $s->execute();
        if ($s->affected_rows > 0) $done[] = "Password reset for <strong>$sid</strong>";
        else $errors[] = "Student ID <strong>$sid</strong> not found.";
    }

    if ($action === 'reset_all') {
        $p = password_hash(trim($_POST['all_pass']),PASSWORD_DEFAULT);
        $conn->query("UPDATE students SET password='$p'");
        $done[] = "All " . $conn->affected_rows . " student passwords reset.";
    }
}

$admins   = $db_error ? [] : $conn->query("SELECT id,username,full_name FROM admins")->fetch_all(MYSQLI_ASSOC);
$students = $db_error ? [] : $conn->query("SELECT student_id,full_name,status FROM students ORDER BY student_id")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LibriTrack Reset Utility</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,sans-serif;background:#f0f2f0;padding:28px;color:#222}
h1{font-size:22px;color:#1b4332;margin-bottom:4px}
.sub{color:#888;font-size:13px;margin-bottom:22px}
.warn{background:#fff3cd;border:2px solid #ffc107;border-radius:8px;padding:13px 16px;margin-bottom:20px;font-size:13.5px;color:#856404}
.db-err{background:#fee;border:2px solid red;border-radius:8px;padding:13px 16px;margin-bottom:20px;font-size:14px;color:#c00}
.ok{background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:10px 14px;border-radius:6px;margin-bottom:10px;font-size:13.5px}
.err{background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:10px 14px;border-radius:6px;margin-bottom:10px;font-size:13.5px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px}
.card{background:#fff;border-radius:10px;padding:20px;border:1px solid #e0e0e0}
.card h2{font-size:15px;color:#1b4332;border-bottom:1px solid #eee;padding-bottom:8px;margin-bottom:14px}
.fg{margin-bottom:12px}
label{display:block;font-size:11.5px;font-weight:bold;color:#555;margin-bottom:4px;text-transform:uppercase}
input[type=text],input[type=password]{width:100%;padding:9px 10px;border:1px solid #ccc;border-radius:6px;font-size:13.5px}
input:focus{outline:none;border-color:#1b4332}
.hint{font-size:11.5px;color:#aaa;margin-top:3px}
.btn{padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-size:13.5px;font-weight:bold;font-family:Arial,sans-serif}
.g{background:#1b4332;color:#fff}.g:hover{background:#2d6a4f}
.r{background:#e74c3c;color:#fff}.r:hover{background:#c0392b}
table{width:100%;border-collapse:collapse;font-size:13px}
th{background:#f8f9f8;padding:8px 10px;text-align:left;border-bottom:1px solid #eee;font-size:11px;text-transform:uppercase;color:#888}
td{padding:8px 10px;border-bottom:1px solid #f5f5f5}
.tag{display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:bold}
.ta{background:#d4edda;color:#155724}.ts{background:#f8d7da;color:#721c24}
.full{grid-column:1/-1}
.goto{text-align:center;margin-top:12px}
.goto a{color:#1b4332;font-weight:bold;font-size:14px}
</style>
</head>
<body>
<h1>🔧 LibriTrack — Reset Utility</h1>
<p class="sub">Fix login credentials. <strong>Delete reset.php after use!</strong></p>

<div class="warn">⚠ <strong>Security warning:</strong> Anyone with this URL can change passwords. Delete <code>reset.php</code> from htdocs immediately after fixing your login.</div>

<?php if ($db_error): ?>
<div class="db-err">
    ❌ <strong>Database connection failed.</strong><br>
    Make sure XAMPP MySQL is running and the database <strong>libritrack</strong> exists.<br>
    <em>Error: <?= htmlspecialchars($db_error) ?></em>
</div>
<?php else: ?>

<?php foreach($done as $m): ?><div class="ok">✅ <?= $m ?></div><?php endforeach; ?>
<?php foreach($errors as $m): ?><div class="err">❌ <?= $m ?></div><?php endforeach; ?>

<div class="grid">

<div class="card">
  <h2>🔑 Reset Admin Password</h2>
  <form method="POST">
    <input type="hidden" name="action" value="reset_admin">
    <div class="fg"><label>Username</label><input type="text" name="admin_username" value="admin" required></div>
    <div class="fg"><label>New Password</label><input type="password" name="admin_password" placeholder="Enter new password" required><div class="hint">Updates admin with id=1, or creates one if none exist</div></div>
    <button class="btn g">Reset Admin Password</button>
  </form>
</div>

<div class="card">
  <h2>➕ Add New Admin Account</h2>
  <form method="POST">
    <input type="hidden" name="action" value="add_admin">
    <div class="fg"><label>Full Name</label><input type="text" name="new_n" placeholder="e.g. Head Librarian"></div>
    <div class="fg"><label>Username</label><input type="text" name="new_u" required></div>
    <div class="fg"><label>Password</label><input type="password" name="new_p" required></div>
    <button class="btn g">Add Admin</button>
  </form>
</div>

<div class="card">
  <h2>🎓 Reset One Student Password</h2>
  <form method="POST">
    <input type="hidden" name="action" value="reset_one">
    <div class="fg"><label>Student ID</label><input type="text" name="student_id" placeholder="e.g. UNiK/2022/001" required></div>
    <div class="fg"><label>New Password</label><input type="password" name="student_pass" required></div>
    <button class="btn g">Reset This Student</button>
  </form>
</div>

<div class="card">
  <h2>🔄 Reset ALL Students to One Password</h2>
  <form method="POST" onsubmit="return confirm('Reset ALL student passwords?')">
    <input type="hidden" name="action" value="reset_all">
    <div class="fg"><label>New Password for All Students</label><input type="password" name="all_pass" placeholder="e.g. student123" required><div class="hint">Every student will use this same password</div></div>
    <button class="btn r">Reset All Student Passwords</button>
  </form>
</div>

<div class="card">
  <h2>👤 Admin Accounts in Database</h2>
  <?php if (empty($admins)): ?><p style="color:#aaa;font-size:13px">No admins found — use Add Admin above.</p>
  <?php else: ?>
  <table>
    <thead><tr><th>ID</th><th>Username</th><th>Full Name</th></tr></thead>
    <tbody>
    <?php foreach($admins as $a): ?>
    <tr><td><?= $a['id'] ?></td><td><strong><?= htmlspecialchars($a['username']) ?></strong></td><td><?= htmlspecialchars($a['full_name']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<div class="card">
  <h2>🎓 Student Accounts (<?= count($students) ?>)</h2>
  <?php if (empty($students)): ?><p style="color:#aaa;font-size:13px">No students — re-import database.sql.</p>
  <?php else: ?>
  <table>
    <thead><tr><th>Student ID</th><th>Name</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach($students as $s): ?>
    <tr>
      <td><strong><?= htmlspecialchars($s['student_id']) ?></strong></td>
      <td><?= htmlspecialchars($s['full_name']) ?></td>
      <td><span class="tag t<?= $s['status'][0] ?>"><?= ucfirst($s['status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

</div>

<div class="goto"><a href="index.php">→ Go to LibriTrack Login Page</a></div>
<?php endif; ?>
</body>
</html>
