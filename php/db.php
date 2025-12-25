<?php
$host = 'mysql-elshanbackend.alwaysdata.net';  
$db   = 'elshanbackend_user';    
$user = '443596';                       
$pass = 'Elshan2006';

$config = include("config.php");

try {
    $conn = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>