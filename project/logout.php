<?php
session_start();

// Clear session
session_unset();
session_destroy();

// Clear "Remember Me" cookie
setcookie('remember_me', '', time() - 3600, '/');

header('Location: login.php');
exit();
?>