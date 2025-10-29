<?php
$password = "Sang##123";
$passwordHash = password_hash($password, PASSWORD_BCRYPT);
echo $passwordHash;
