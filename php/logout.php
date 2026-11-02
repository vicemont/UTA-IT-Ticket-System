<?php
session_start();
$_SE = [];
session_destroy();
header('../userdashboard.php');
exit();