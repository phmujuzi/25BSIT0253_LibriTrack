<?php
require_once '../includes/config.php';
requireAdmin();

// Add student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']??'') === 'add') {
    $sid    = trim($_POST['student_id']);
    $name   = trim($_POST['full_name']);
    $email  = trim($_POST['email']);
    $course = trim($_POST['course']);
    $pass   = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM students WHERE student_id=?");
    $check->bind_param('s', $sid);
    $check->execute();
    if ($check->get_result()->fetch_assoc()) {
        setFlash('Student ID already exists.', 'error');
    } else {
        $stmt = $conn->prepare("INSERT INTO students (student_id, full_name, email, course, password) VALUES (?,?,?,?,?)");
        $stmt->bind_param('sssss', $sid, $name, $email, $course, $pass);
        $stmt->execute();
        setFlash("Student '$name' registered.");
    }
    header('Location: students.php'); exit;
}

// Toggle suspend
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $s  = $conn->query("SELECT status FROM students WHERE id=$id")->fetch_assoc();
    $new = $s['status'] === 'active' ? 'suspended' : 'active';
    $conn->query("UPDATE students SET status='$new' WHERE id=$id");
    setFlash('Student status updated.');
    header('Location: students.php'); exit;
}

$search = trim($_GET['search'] ?? '');
$where  = $search ? "WHERE full_name LIKE '%" . $conn->real_escape_string($search) . "%' OR student_id LIKE '%" . $conn->real_escape_string($search) . "%'" : '';
$students = $conn->query("
    SELECT s.*, 
           (SELECT COUNT(*) FROM borrowings WHERE student_id=s.id AND status='borrowed') AS active_borrows
    FROM students s $where ORDER BY s.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

include 'partials/header.php';
?>

<div class="page-title">Students</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <form class="search-bar" method="GET" style="margin:0;flex:1;max-width:400px">
        <input type="text" name="search" placeholder="🔍 Search students…" value="<?= e($search) ?>">
        <button type="submit" class="btn btn-green">Search</button>
        <?php if ($search): ?><a href="students.php" class="btn" style="background:#f0f0f0;color:#555">Clear</a><?php endif; ?>
    </form>
    <button class="btn btn-green" onclick="document.getElementById('add-modal').classList.add('open')">+ Register Student</button>
</div>

<div class="card">
    <div class="card-head">All Students (<?= count($students) ?>)</div>
    <table>
        <thead>
            <tr><th>Name</th><th>Student ID</th><th>Course</th><th>Active Borrows</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php foreach ($students as $s): ?>
        <tr>
            <td><?= e($s['full_name']) ?></td>
            <td><?= e($s['student_id']) ?></td>
            <td><?= e($s['course'] ?: '—') ?></td>
            <td style="text-align:center"><?= $s['active_borrows'] ?></td>
            <td><span class="badge badge-<?= $s['status'] ?>"><?= ucfirst($s['status']) ?></span></td>
            <td>
                <a href="?toggle=<?= $s['id'] ?>" class="btn btn-sm"
                   style="background:<?= $s['status']==='active'?'#fee2e2':'#d1fae5' ?>;color:<?= $s['status']==='active'?'#991b1b':'#065f46' ?>"
                   onclick="return confirm('Change student status?')">
                    <?= $s['status']==='active' ? 'Suspend' : 'Activate' ?>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($students)): ?>
        <tr><td colspan="6" style="text-align:center;padding:32px;color:#aaa">No students found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Student Modal -->
<div class="modal-overlay" id="add-modal">
    <div class="modal">
        <button class="modal-close" onclick="document.getElementById('add-modal').classList.remove('open')">✕</button>
        <h3>Register New Student</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-row">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>Student ID *</label>
                    <input type="text" name="student_id" placeholder="UNiK/2024/001" required>
                </div>
            </div>
            <div class="form-group">
                <label>Course</label>
                <input type="text" name="course" placeholder="e.g. Bachelor of Information Technology">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email">
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" required minlength="6">
            </div>
            <button type="submit" class="btn btn-green">Register Student</button>
        </form>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
