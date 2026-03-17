<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LibriTrack Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
    --green: #1b4332;
    --green2: #2d6a4f;
    --gold: #c8961e;
    --sidebar: 240px;
    --red: #e74c3c;
}
body { font-family: 'DM Sans', sans-serif; background: #f4f6f4; display: flex; min-height: 100vh; }

/* Sidebar */
.sidebar {
    width: var(--sidebar);
    background: var(--green);
    position: fixed; top: 0; left: 0; height: 100vh;
    display: flex; flex-direction: column;
    overflow-y: auto;
}
.sidebar-brand {
    padding: 22px 20px;
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.sidebar-brand h2 {
    font-family: 'Playfair Display', serif;
    color: #fff; font-size: 20px;
}
.sidebar-brand p { color: rgba(255,255,255,.4); font-size: 11px; margin-top: 2px; }

.sidebar nav { padding: 12px 0; flex: 1; }
.nav-label {
    padding: 10px 20px 4px;
    font-size: 10px; font-weight: 600;
    color: rgba(255,255,255,.3);
    text-transform: uppercase; letter-spacing: 1.5px;
}
.nav-link {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 20px;
    color: rgba(255,255,255,.65);
    text-decoration: none; font-size: 13.5px; font-weight: 500;
    transition: all .15s;
}
.nav-link:hover { background: rgba(255,255,255,.08); color: #fff; }
.nav-link.active { background: rgba(255,255,255,.12); color: #fff; border-left: 3px solid var(--gold); }
.nav-link span { font-size: 15px; width: 18px; text-align: center; }

.sidebar-footer {
    padding: 16px 20px;
    border-top: 1px solid rgba(255,255,255,.08);
    color: rgba(255,255,255,.35); font-size: 11.5px;
}
.sidebar-footer a { color: rgba(255,255,255,.4); text-decoration: none; }
.sidebar-footer a:hover { color: #fff; }

/* Main */
.main { margin-left: var(--sidebar); flex: 1; display: flex; flex-direction: column; }
.topbar {
    background: #fff; padding: 14px 28px;
    display: flex; justify-content: space-between; align-items: center;
    border-bottom: 1px solid #e8ece8;
    position: sticky; top: 0; z-index: 10;
}
.topbar-title { font-family: 'Playfair Display', serif; font-size: 14px; color: #888; }
.topbar-right {
    display: flex; align-items: center; gap: 12px;
    font-size: 13px; color: #555;
}
.admin-pill {
    background: var(--green); color: #fff;
    padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;
}

.content { padding: 24px 28px; flex: 1; }

/* Page title */
.page-title {
    font-family: 'Playfair Display', serif;
    font-size: 24px; color: var(--green);
    margin-bottom: 20px;
}

/* Flash */
.flash {
    padding: 11px 16px; border-radius: 8px;
    margin-bottom: 18px; font-size: 13.5px; font-weight: 500;
}
.flash.success { background: #e8f5ee; color: #1e6e3e; border-left: 3px solid #27ae60; }
.flash.error   { background: #fce8e8; color: #991b1b; border-left: 3px solid #e74c3c; }
.flash.info    { background: #e8f0fe; color: #1a4a8a; border-left: 3px solid #3b82f6; }

/* Stats grid */
.stats-grid {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 16px; margin-bottom: 24px;
}
.stat-card {
    background: #fff; border-radius: 12px;
    padding: 20px; border-left: 4px solid var(--green);
}
.stat-card.stat-danger { border-left-color: var(--red); }
.stat-icon { font-size: 22px; margin-bottom: 8px; }
.stat-num { font-size: 28px; font-weight: 700; color: var(--green); }
.stat-card.stat-danger .stat-num { color: var(--red); }
.stat-label { font-size: 12px; color: #888; margin-top: 2px; font-weight: 500; }

/* Two column layout */
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

/* Card */
.card { background: #fff; border-radius: 12px; overflow: hidden; }
.card-head {
    padding: 16px 20px; font-weight: 700; font-size: 14px;
    border-bottom: 1px solid #f0f0f0; color: var(--green);
}

/* Table */
table { width: 100%; border-collapse: collapse; }
th {
    padding: 10px 14px; text-align: left;
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; color: #888;
    background: #fafafa; border-bottom: 1px solid #f0f0f0;
}
td { padding: 11px 14px; border-bottom: 1px solid #f8f8f8; font-size: 13.5px; }
td small { color: #aaa; font-size: 11.5px; display: block; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: #fafdf8; }

/* Badges */
.badge {
    display: inline-block; padding: 3px 9px; border-radius: 20px;
    font-size: 11px; font-weight: 700; text-transform: uppercase;
}
.badge-borrowed  { background: #fef3cd; color: #856404; }
.badge-returned  { background: #d1fae5; color: #065f46; }
.badge-overdue   { background: #fee2e2; color: #991b1b; }
.badge-active    { background: #d1fae5; color: #065f46; }
.badge-suspended { background: #fee2e2; color: #991b1b; }

/* Buttons */
.btn {
    padding: 8px 18px; border: none; border-radius: 8px;
    font-family: 'DM Sans', sans-serif; font-size: 13px;
    font-weight: 600; cursor: pointer; text-decoration: none;
    display: inline-block; transition: opacity .15s;
}
.btn:hover { opacity: .85; }
.btn-green { background: var(--green); color: #fff; }
.btn-red   { background: #fee2e2; color: #991b1b; }
.btn-sm    { padding: 5px 12px; font-size: 12px; }

/* Forms */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { margin-bottom: 14px; }
.form-group label { display: block; font-size: 12.5px; font-weight: 600; color: #555; margin-bottom: 5px; }
.form-group input,
.form-group select,
.form-group textarea {
    width: 100%; padding: 9px 12px;
    border: 1px solid #ddd; border-radius: 8px;
    font-family: 'DM Sans', sans-serif; font-size: 13.5px;
    color: #222; outline: none; transition: border-color .15s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { border-color: var(--green2); }
.form-group textarea { resize: vertical; min-height: 80px; }

/* Search bar */
.search-bar {
    display: flex; gap: 10px; margin-bottom: 16px; align-items: center;
}
.search-bar input {
    flex: 1; padding: 9px 14px;
    border: 1px solid #ddd; border-radius: 8px;
    font-family: 'DM Sans', sans-serif; font-size: 13.5px;
    outline: none;
}
.search-bar input:focus { border-color: var(--green2); }

/* Modal */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.5); z-index: 100;
    align-items: center; justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal {
    background: #fff; border-radius: 16px;
    padding: 28px; width: 90%; max-width: 520px;
    max-height: 90vh; overflow-y: auto;
}
.modal h3 {
    font-family: 'Playfair Display', serif;
    font-size: 18px; color: var(--green); margin-bottom: 20px;
}
.modal-close {
    float: right; background: none; border: none;
    font-size: 20px; cursor: pointer; color: #aaa;
}
.modal-close:hover { color: #333; }
</style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <h2>📚 LibriTrack</h2>
        <p>Admin Portal · UNiK</p>
    </div>
    <nav>
        <div class="nav-label">Main</div>
        <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>"><span>📊</span> Dashboard</a>

        <div class="nav-label">Library</div>
        <a href="books.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='books.php'?'active':'' ?>"><span>📚</span> Books</a>
        <a href="borrow.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='borrow.php'?'active':'' ?>"><span>🔄</span> Issue / Return</a>
        <a href="borrowings.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='borrowings.php'?'active':'' ?>"><span>📋</span> All Borrowings</a>

        <div class="nav-label">People</div>
        <a href="students.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='students.php'?'active':'' ?>"><span>🎓</span> Students</a>

        <div class="nav-label">System</div>
        <a href="notices.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='notices.php'?'active':'' ?>"><span>📢</span> Notices</a>
    </nav>
    <div class="sidebar-footer">
        👤 <?= e($_SESSION['admin_name']) ?><br>
        <a href="logout.php">Sign out →</a>
    </div>
</aside>

<div class="main">
    <div class="topbar">
        <div class="topbar-title">University of Kisubi Library</div>
        <div class="topbar-right">
            <span class="admin-pill">Admin</span>
        </div>
    </div>
    <div class="content">
    <?php $f = getFlash(); if ($f): ?>
    <div class="flash <?= $f['type'] ?>"><?= e($f['msg']) ?></div>
    <?php endif; ?>
