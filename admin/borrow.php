<?php
require_once '../includes/config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Issue book
    if ($action === 'issue') {
        $sid = trim($_POST['student_id']);
        $bid = (int)$_POST['book_id'];

        // Get student
        $s = $conn->prepare("SELECT * FROM students WHERE student_id=?");
        $s->bind_param('s', $sid);
        $s->execute();
        $student = $s->get_result()->fetch_assoc();

        if (!$student) {
            setFlash('Student ID not found.', 'error');
        } elseif ($student['status'] === 'suspended') {
            setFlash('Student account is suspended.', 'error');
        } else {
            // Check active borrows
            $active = $conn->query("SELECT COUNT(*) FROM borrowings WHERE student_id={$student['id']} AND status='borrowed'")->fetch_row()[0];
            if ($active >= 4) {
                setFlash('Student already has 4 books borrowed (maximum).', 'error');
            } else {
                // Check book
                $book = $conn->query("SELECT * FROM books WHERE id=$bid")->fetch_assoc();
                if (!$book || $book['copies_available'] < 1) {
                    setFlash('Book not available.', 'error');
                } else {
                    $due = date('Y-m-d', strtotime('+' . LOAN_DAYS . ' days'));
                    $today = date('Y-m-d');
                    $stmt = $conn->prepare("INSERT INTO borrowings (student_id, book_id, borrow_date, due_date, status) VALUES (?,?,?,?,'borrowed')");
                    $stmt->bind_param('iiss', $student['id'], $bid, $today, $due);
                    $stmt->execute();
                    $conn->query("UPDATE books SET copies_available = copies_available - 1 WHERE id=$bid");
                    setFlash("'{$book['title']}' issued to {$student['full_name']}. Due: " . date('d M Y', strtotime($due)));
                }
            }
        }
        header('Location: borrow.php'); exit;
    }

    // Return book
    if ($action === 'return') {
        $borrow_id = (int)$_POST['borrow_id'];
        $b = $conn->query("SELECT * FROM borrowings WHERE id=$borrow_id")->fetch_assoc();
        if ($b) {
            $today = date('Y-m-d');
            $conn->query("UPDATE borrowings SET status='returned', return_date='$today' WHERE id=$borrow_id");
            $conn->query("UPDATE books SET copies_available = copies_available + 1 WHERE id={$b['book_id']}");
            setFlash('Book returned successfully.');
        }
        header('Location: borrow.php'); exit;
    }
}

// Books list for dropdown
$books_list = $conn->query("SELECT id, title, author, copies_available FROM books WHERE copies_available > 0 ORDER BY title")->fetch_all(MYSQLI_ASSOC);

// Active borrowings
$active_borrows = $conn->query("
    SELECT br.*, s.full_name, s.student_id AS sid, b.title AS book_title,
           DATEDIFF(CURDATE(), br.due_date) AS days_late
    FROM borrowings br
    JOIN students s ON br.student_id = s.id
    JOIN books b ON br.book_id = b.id
    WHERE br.status = 'borrowed'
    ORDER BY br.due_date ASC
")->fetch_all(MYSQLI_ASSOC);

include 'partials/header.php';
?>

<div class="page-title">Issue / Return Books</div>

<div class="two-col" style="margin-bottom:24px">
    <!-- Issue form -->
    <div class="card">
        <div class="card-head">📖 Issue a Book</div>
        <div style="padding:20px">
            <form method="POST">
                <input type="hidden" name="action" value="issue">
                <div class="form-group">
                    <label>Student ID *</label>
                    <input type="text" name="student_id" placeholder="e.g. UNiK/2022/001" required>
                </div>
                <div class="form-group">
                    <label>Book *</label>
                    <select name="book_id" required>
                        <option value="">— Select a book —</option>
                        <?php foreach ($books_list as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= e($b['title']) ?> — <?= e($b['author']) ?> (<?= $b['copies_available'] ?> left)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="font-size:12.5px;color:#888;margin-bottom:14px">Loan period: <?= LOAN_DAYS ?> days · Fine: UGX <?= number_format(FINE_PER_DAY) ?>/day overdue</div>
                <button type="submit" class="btn btn-green">Issue Book</button>
            </form>
        </div>
    </div>

    <!-- Quick stats -->
    <div class="card">
        <div class="card-head">📊 Quick Stats</div>
        <div style="padding:20px">
            <?php
            $total_out   = count($active_borrows);
            $overdue_cnt = 0;
            foreach ($active_borrows as $ab) { if ($ab['days_late'] > 0) $overdue_cnt++; }
            ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div style="background:#f0f9f4;border-radius:10px;padding:16px;text-align:center">
                    <div style="font-size:28px;font-weight:700;color:#1b4332"><?= $total_out ?></div>
                    <div style="font-size:12px;color:#888">Books Out</div>
                </div>
                <div style="background:<?= $overdue_cnt>0?'#fef2f2':'#f0f9f4' ?>;border-radius:10px;padding:16px;text-align:center">
                    <div style="font-size:28px;font-weight:700;color:<?= $overdue_cnt>0?'#e74c3c':'#1b4332' ?>"><?= $overdue_cnt ?></div>
                    <div style="font-size:12px;color:#888">Overdue</div>
                </div>
            </div>
            <div style="margin-top:16px;font-size:13px;color:#888">Max books per student: 4</div>
        </div>
    </div>
</div>

<!-- Active borrowings table -->
<div class="card">
    <div class="card-head">Currently Borrowed (<?= count($active_borrows) ?>)</div>
    <table>
        <thead>
            <tr><th>Student</th><th>Book</th><th>Borrowed</th><th>Due Date</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php foreach ($active_borrows as $r):
            $late = $r['days_late'] > 0;
        ?>
        <tr>
            <td><?= e($r['full_name']) ?><br><small><?= e($r['sid']) ?></small></td>
            <td><?= e($r['book_title']) ?></td>
            <td><?= date('d M Y', strtotime($r['borrow_date'])) ?></td>
            <td style="color:<?= $late?'#e74c3c':'#222' ?>;font-weight:<?= $late?'700':'400' ?>">
                <?= date('d M Y', strtotime($r['due_date'])) ?>
                <?php if ($late): ?><br><small style="color:#e74c3c">⚠ <?= $r['days_late'] ?> days overdue</small><?php endif; ?>
            </td>
            <td><span class="badge badge-<?= $late?'overdue':'borrowed' ?>"><?= $late?'Overdue':'Borrowed' ?></span></td>
            <td>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="action" value="return">
                    <input type="hidden" name="borrow_id" value="<?= $r['id'] ?>">
                    <button type="submit" class="btn btn-green btn-sm">✓ Return</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($active_borrows)): ?>
        <tr><td colspan="6" style="text-align:center;padding:32px;color:#aaa">No active borrowings</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'partials/footer.php'; ?>
