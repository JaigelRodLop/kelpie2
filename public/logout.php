<?php
require_once __DIR__ . '/../src/config/auth.php';

logoutUser();

header("Location: index.php");
exit;
?>
