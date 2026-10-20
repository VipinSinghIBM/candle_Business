<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_user']);
setFlash('info', 'You have been logged out of the Atelier Admin Portal.');
header('Location: login.php');
exit;
