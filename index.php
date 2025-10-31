<?php
$password = "Sang##123";
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
echo $passwordHash;
