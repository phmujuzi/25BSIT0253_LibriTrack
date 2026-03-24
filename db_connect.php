<?php
// ============================================================
// db_connect.php — Database Configuration File
// LibriTrack Library Management System
// University of Kisubi (UNiK)
// ============================================================

$host     = 'localhost';
$username = 'root';
$password = '';
$database = 'libritrack';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("<div style='font-family:sans-serif;padding:30px;background:#fee;border:2px solid red;border-radius:8px;margin:20px;'>
        <h2>Database Connection Error</h2>
        <p>Could not connect. Make sure XAMPP MySQL is running and <strong>libritrack</strong> database exists.</p>
        <p style='color:#888'>Error: " . $conn->connect_error . "</p>
    </div>");
}

$conn->set_charset('utf8mb4');
?>
