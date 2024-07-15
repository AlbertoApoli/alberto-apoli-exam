<?php

$host = 'localhost';
$port = 3306;
$dbName = 'youtube_db';
$username = 'root';
$password = '';

$dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // echo 'Database connected...';
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
