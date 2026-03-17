<?php
require_once '../includes/config.php';
requireStudent();
$sid = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update') {
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $stmt  = $conn->prepare("UPDATE students SET email=?, phone=? WHERE id=?");
        $stmt->bind_param('ssi', $email, $phone, $sid);
        $stmt->execute();
        setFlash('Profile updated.');
        header('Location: profile.php'); exit;
    }

    if ($action === 'password') {
        $current = $_POST['current_password'];
        $new     = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        $student = $conn->query("SELECT password FROM students WHERE id=$sid")->fetch_assoc();
        if (!password_verify($current, $student['password'])) {
            setFlash('Current password is incorrect.', 'error');
        } elseif ($new !== $confirm) {
            setFlash('New passwords do not match.', 'error');
        } elseif (strlen($new) < 6) {
            setFlash('Password must be at least 6 characters.', 'error');
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE students SET password=? WHERE id=?");
            $stmt->bind_param('si', $hash, $sid);
            $stmt->execute();
            setFlash('Password changed successfully.');
        }
        header('Location: profile.php'); exit;
    }
}

$student = $conn->query("SELECT * FROM students WHERE id=$sid")->fetch_assoc();
$stats   = $conn->query("SELECT
    COUNT(*) AS total,
    SUM(status='borrowed') AS active,
    SUM(status='returned') AS returned
    FROM borrowings WHERE student_id=$sid")->fetch_assoc();

include 'partials/header.php';
?>

<div class="page-title">My Profile</div>

<div class="two-col">
    <!-- Info card -->
    <div>
        <div class="card" style="margin-bottom:20px">
            <div class="card-head">Account Information</div>
            <div style="padding:20px">
                <div style="text-align:center;margin-bottom:20px">
                    <div style="width:72px;height:72px;border-radius:50%;background:#1b4332;color:#fff;font-size:28px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif">
                        <?= strtoupper(substr($student['full_name'],0,1)) ?>
                    </div>
                    <div style="font-family:'Playfair Display',serif;font-size:18px;font-weight:700;margin-top:10px"><?= htmlspecialchars($student['full_name']) ?></div>
                    <div style="font-size:13px;color:#888"><?= htmlspecialchars($student['student_id']) ?></div>
                    <div style="font-size:12px;color:#aaa;margin-top:4px"><?= htmlspecialchars($student['course'] ?: '') ?></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;text-align:center">
                    <div style="background:#f0f9f4;border-radius:8px;padding:12px">
                        <div style="font-size:20px;font-weight:700;color:#1b4332"><?= $stats['active'] ?></div>
                        <div style="font-size:11px;color:#888">Active</div>
                    </div>
                    <div style="background:#f0f9f4;border-radius:8px;padding:12px">
                        <div style="font-size:20px;font-weight:700;color:#1b4332"><?= $stats['returned'] ?></div>
                        <div style="font-size:11px;color:#888">Returned</div>
                    </div>
                    <div style="background:#f0f9f4;border-radius:8px;padding:12px">
                        <div style="font-size:20px;font-weight:700;color:#1b4332"><?= $stats['total'] ?></div>
                        <div style="font-size:11px;color:#888">Total</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-head">Update Contact Info</div>
            <div style="padding:20px">
                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" value="<?= htmlspecialchars($student['full_name']) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" value="<?= htmlspecialchars($student['student_id']) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" placeholder="+256 7XX XXX XXX">
                    </div>
                    <button type="submit" class="btn btn-green">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Change password -->
    <div class="card">
        <div class="card-head">Change Password</div>
        <div style="padding:20px">
            <form method="POST">
                <input type="hidden" name="action" value="password">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-green">Update Password</button>
            </form>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
