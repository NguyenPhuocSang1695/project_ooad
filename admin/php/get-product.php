<?php
require_once('ProductManager.php');

$controller = new ProductManager();
$controller->handleGetProductRequest();
$controller->close();
