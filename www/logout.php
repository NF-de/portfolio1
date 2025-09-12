<?php
session_start();
$_SESSION = [];
session_destroy();
header("Location: log_admin.php");
exit;
