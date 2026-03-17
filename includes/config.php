<?php
session_start();

// Database
$host = 'localhost';
$user = 'root';
$pass = '';
$name = 'libritrack';

$conn = new mysqli($host, $user, $pass, $name);
if ($conn->connect_error) {
    die("<div style='font-family:sans-serif;padding:30px;background:#fee;border:2px solid red;border-radius:8px;margin:20px;'>
        <h2>Database Connection Error</h2>
        <p>Could not connect to the database. Please make sure:</p>
        <ul>
            <li>XAMPP MySQL is running (green light in XAMPP control panel)</li>
            <li>You imported <strong>database.sql</strong> into phpMyAdmin</li>
            <li>The database name is <strong>libritrack</strong></li>
        </ul>
        <p style='color:#888'>Error: " . $conn->connect_error . "</p>
    </div>");
}
$conn->set_charset('utf8mb4');

define('FINE_PER_DAY', 500);
define('LOAN_DAYS', 14);

// Flash message helpers
function setFlash($msg, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// Auth guards
function requireAdmin() {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ../index.php');
        exit;
    }
}
function requireStudent() {
    if (empty($_SESSION['student_id'])) {
        header('Location: ../index.php');
        exit;
    }
}

// Escape helper
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
