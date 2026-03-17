<?php
require_once __DIR__ . '/../../../config/app.php';
unset($_SESSION['admin_logged_in']);
session_destroy();
header('Location: login.php');
exit();
?>