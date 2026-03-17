<?php
require_once 'includes/config.php';

// Already logged in?
if (!empty($_SESSION['admin_id'])) { header('Location: admin/dashboard.php'); exit; }
if (!empty($_SESSION['student_id'])) { header('Location: student/dashboard.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($role === 'admin') {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            header('Location: admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid admin username or password.';
        }
    } elseif ($role === 'student') {
        $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        if ($student && password_verify($password, $student['password'])) {
            if ($student['status'] === 'suspended') {
                $error = 'Your account is suspended. Please contact the library.';
            } else {
                $_SESSION['student_id'] = $student['id'];
                $_SESSION['student_name'] = $student['full_name'];
                $_SESSION['student_number'] = $student['student_id'];
                header('Location: student/dashboard.php');
                exit;
            }
        } else {
            $error = 'Invalid student ID or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LibriTrack — University of Kisubi Library</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'DM Sans', sans-serif;
    min-height: 100vh;
    background: #0e1c12;
    display: flex;
    align-items: center;
    justify-content: center;
}
/* Background pattern */
body::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
        radial-gradient(ellipse 80% 60% at 20% 40%, rgba(27,67,50,.7) 0%, transparent 70%),
        radial-gradient(ellipse 60% 50% at 80% 70%, rgba(200,150,30,.15) 0%, transparent 60%);
    pointer-events: none;
}

.login-wrap {
    width: 100%;
    max-width: 440px;
    padding: 20px;
    position: relative;
    z-index: 1;
}

/* Brand */
.brand {
    text-align: center;
    margin-bottom: 32px;
}
.brand-logo {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #1b4332, #2d6a4f);
    border-radius: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-bottom: 14px;
    box-shadow: 0 8px 32px rgba(0,0,0,.4);
}
.brand h1 {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    color: #fff;
    letter-spacing: -0.5px;
}
.brand p {
    color: rgba(255,255,255,.45);
    font-size: 13px;
    margin-top: 4px;
}

/* Card */
.card {
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 20px;
    padding: 32px;
    backdrop-filter: blur(20px);
}

/* Tabs */
.tabs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    background: rgba(0,0,0,.3);
    border-radius: 10px;
    padding: 4px;
    margin-bottom: 28px;
}
.tab-btn {
    padding: 9px;
    border: none;
    border-radius: 7px;
    background: transparent;
    color: rgba(255,255,255,.5);
    font-family: 'DM Sans', sans-serif;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.tab-btn.active {
    background: #1b4332;
    color: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.3);
}

/* Form */
.form-group { margin-bottom: 16px; }
.form-group label {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: rgba(255,255,255,.55);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.form-group input {
    width: 100%;
    padding: 12px 14px;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 10px;
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    transition: border-color .2s;
    outline: none;
}
.form-group input:focus {
    border-color: rgba(200,150,30,.6);
    background: rgba(255,255,255,.09);
}
.form-group input::placeholder { color: rgba(255,255,255,.25); }

.hint {
    font-size: 12px;
    color: rgba(255,255,255,.3);
    margin-top: 4px;
}

.error-box {
    background: rgba(220,50,50,.15);
    border: 1px solid rgba(220,50,50,.3);
    color: #f87171;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13.5px;
    margin-bottom: 16px;
}

.submit-btn {
    width: 100%;
    padding: 13px;
    background: linear-gradient(135deg, #1b4332, #2d6a4f);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .2s, transform .1s;
    margin-top: 8px;
}
.submit-btn:hover { opacity: .9; }
.submit-btn:active { transform: scale(.99); }

.creds {
    margin-top: 20px;
    padding: 12px 14px;
    background: rgba(200,150,30,.08);
    border: 1px solid rgba(200,150,30,.2);
    border-radius: 8px;
    font-size: 12px;
    color: rgba(255,255,255,.45);
    line-height: 1.7;
}
.creds strong { color: rgba(255,200,80,.7); }
</style>
</head>
<body>
<div class="login-wrap">
    <div class="brand">
        <div class="brand-logo">📚</div>
        <h1>LibriTrack</h1>
        <p>University of Kisubi — Library System</p>
    </div>

    <div class="card">
        <!-- Role tabs -->
        <div class="tabs">
            <button class="tab-btn active" id="tab-student" onclick="switchTab('student')">🎓 Student</button>
            <button class="tab-btn" id="tab-admin" onclick="switchTab('admin')">🔑 Admin</button>
        </div>

        <?php if ($error): ?>
        <div class="error-box">⚠ <?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="login-form">
            <input type="hidden" name="role" id="role-input" value="student">

            <div class="form-group">
                <label id="username-label">Student ID</label>
                <input type="text" name="username" id="username-input"
                       placeholder="e.g. UNiK/2022/001" required
                       value="<?= e($_POST['username'] ?? '') ?>">
                <div class="hint" id="username-hint">Enter your university student ID</div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="submit-btn">Sign In →</button>
        </form>

        <div class="creds">
            <strong>Demo credentials:</strong><br>
            Admin: <strong>admin</strong> / <strong>password</strong><br>
            Student ID: <strong>UNiK/2022/001</strong> / <strong>password</strong>
        </div>
    </div>
</div>

<script>
function switchTab(role) {
    document.getElementById('role-input').value = role;
    document.getElementById('tab-student').classList.toggle('active', role === 'student');
    document.getElementById('tab-admin').classList.toggle('active', role === 'admin');
    if (role === 'admin') {
        document.getElementById('username-label').textContent = 'Username';
        document.getElementById('username-input').placeholder = 'e.g. admin';
        document.getElementById('username-hint').textContent = 'Enter your admin username';
    } else {
        document.getElementById('username-label').textContent = 'Student ID';
        document.getElementById('username-input').placeholder = 'e.g. UNiK/2022/001';
        document.getElementById('username-hint').textContent = 'Enter your university student ID';
    }
}
// If there was a POST error, keep the right tab active
<?php if (!empty($_POST['role'])): ?>
switchTab('<?= e($_POST['role']) ?>');
<?php endif; ?>
</script>
</body>
</html>
