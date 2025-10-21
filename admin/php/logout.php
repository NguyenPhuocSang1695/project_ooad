<?php 
session_name('admin_session');
session_start();
session_unset();
header("Location: ../index.php"); 
exit(); 
?>