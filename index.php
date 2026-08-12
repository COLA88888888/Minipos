<?php
session_start();
if (!empty($_SESSION['checked']) && !empty($_SESSION['user_id'])) {
    header("Location: home/dashboard.php");
    exit();
}
header("Location: auth/login.php");
exit();
