<?php

if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
    header("HTTP/1.1 403 Forbidden");
    exit("Acesso negado.");
}

$host = '127.0.0.1';
$dbname = 'restaurante_db';
$username = 'root';
$password = 'root';
$charset = 'utf8mb4';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";


    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
        PDO::ATTR_EMULATE_PREPARES => false,              
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

} catch (PDOException $e) {
    
    die("Erro catastrófico na conexão com o banco de dados: " . $e->getMessage());
}