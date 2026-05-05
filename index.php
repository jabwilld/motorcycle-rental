<?php
require_once __DIR__ . '/core.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$role = $_SESSION['role'];
if ($role === 'admin') {
    header("Location: admin/manage_users.php");
    exit();
} else if ($role === 'manager' || $role === 'employee') {
    header("Location: admin/index.php");
    exit();
} else {
    header("Location: logout.php");
    exit();
}
?>
