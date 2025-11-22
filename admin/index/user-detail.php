<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý người dùng</title>
  
  <link href="../icon/css/all.css" rel="stylesheet">
  <link href="../style/generall.css" rel="stylesheet">
  <link href="../style/main1.css" rel="stylesheet">
  <link href="../style/LogInfo.css" rel="stylesheet">
  <link href="asset/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/add-user-modal.css">
  <!-- Customer table CSS loaded last - contains all customer page styles -->
  <script src="../js/customer-search.js" defer></script>
  <script src="../js/add-user.js" defer></script>
  <script src="../js/edit-user.js" defer></script>
  <script src="../js/delete-user.js" defer></script>
</head>
<body>
    <!-- header -->
<?php
    require_once 'header_sidebar.php';
    require_once '../php/connect.php';
    require_once '../php/UserManager.php';
    require_once '../php/User.php';
?>
</body>
<script src="asset/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../js/checklog.js"></script>
<script src="../js/main.js"></script>
    
</html>