<?php
// ============================================================
// catalog.php — Dynamic Featured Books
// Assignment 4: From Static to Data-Driven
// Uses tbl_content fetched via a while loop
// ============================================================
require_once '../db_connect.php';        // separate config file
require_once '../includes/config.php';
requireStudent();

// Fetch all rows from tbl_content
$result = $conn->query(
    "SELECT id, title, description, image_url FROM tbl_content ORDER BY id ASC"
);

include 'partials/header.php';
?>

<div class="page-title">
    Featured Books
    <span style="display:inline-block;background:#1b4332;color:#fff;padding:3px 12px;
                 border-radius:20px;font-size:12px;font-weight:600;margin-left:8px;vertical-align:middle">
        <?= $result ? $result->num_rows : 0 ?> books
    </span>
</div>
<p style="color:#888;font-size:13.5px;margin-bottom:24px">
    Recommended reading from the UNiK Library collection
</p>

<!-- Book cards grid -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:20px">

<?php
// -------------------------------------------------------
// DYNAMIC LOOP — fetches every row from tbl_content
// and injects it into a single HTML card template.
// Add a new row in phpMyAdmin → appears here instantly.
// -------------------------------------------------------
if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
?>
    <div style="background:#fff;border-radius:14px;overflow:hidden;
                box-shadow:0 2px 8px rgba(0,0,0,.06);
                transition:transform .2s,box-shadow .2s"
         onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.1)'"
         onmouseout="this.style.transform='';this.style.boxShadow='0 2px 8px rgba(0,0,0,.06)'">

        <img src="<?= htmlspecialchars($row['image_url']) ?>"
             alt="<?= htmlspecialchars($row['title']) ?>"
             onerror="this.src='https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=400&q=80'"
             style="width:100%;height:170px;object-fit:cover;display:block">

        <div style="padding:16px">
            <div style="font-size:11px;font-weight:700;color:#c8961e;
                        text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px">
                Book #<?= $row['id'] ?>
            </div>
            <div style="font-family:'Playfair Display',serif;font-size:16px;
                        font-weight:700;color:#111;margin-bottom:7px;line-height:1.3">
                <?= htmlspecialchars($row['title']) ?>
            </div>
            <div style="font-size:13px;color:#666;line-height:1.6">
                <?= htmlspecialchars($row['description']) ?>
            </div>
        </div>
    </div>
<?php
    } // end while

} else {
    // -------------------------------------------------------
    // EMPTY STATE — handles "No records found" scenario
    // -------------------------------------------------------
?>
    <div style="grid-column:1/-1;text-align:center;padding:60px 20px;
                background:#fff;border-radius:14px;color:#aaa">
        <div style="font-size:48px;margin-bottom:12px">📭</div>
        <h3 style="font-size:18px;color:#888;margin-bottom:8px">No Records Found</h3>
        <p style="font-size:13.5px">
            No featured books yet.<br>
            Add rows to the <strong>tbl_content</strong> table in phpMyAdmin to display books here.
        </p>
    </div>
<?php
}

if ($result) $result->free();
?>

</div><!-- /grid -->

<?php include 'partials/footer.php'; ?>
