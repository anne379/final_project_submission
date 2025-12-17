<?php
// includes/db_connect.php

$host = 'sql100.infinityfree.com';
$db_name = 'if0_40705682_bankassist';
$username = 'if0_40705682';
$password = 'KA180LrHYBE9xQo';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    // Enable error reporting for debugging, turn off in production
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

