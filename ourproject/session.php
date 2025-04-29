<?php
session_start();

 
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

 
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role']; 
 
if ($role === "admin") {
    header("Location: admin_panel.php");
} else {
    header("Location: dashboard.php");
}
exit();
?>