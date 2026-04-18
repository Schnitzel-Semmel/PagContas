<?php

$host = "localhost";
$dbname = "pagcontasdb";
$user = "root";
$pass = "";
$port = "3306";

try {
    $conn = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexao com o banco de dados: " . $e->getMessage());
}