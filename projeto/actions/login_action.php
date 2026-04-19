<?php
session_start();
require_once __DIR__ . '/../config/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit;
}

$telefone = trim($_POST['telefone'] ?? '');
$senha = $_POST['senha'] ?? '';
$telefoneNumeros = preg_replace('/\D+/', '', $telefone);

try {
    $sql = 'SELECT id_usuario, nome, telefone, senha_hash FROM usuario WHERE telefone = :telefone AND status = :status LIMIT 1';
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':telefone' => $telefoneNumeros,
        ':status' => 'ativo'
    ]);

    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
        header('Location: ../pages/login.php?erro=usuarioErro=' . urlencode($telefone));
        exit;
    }

    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nome_usuario'] = $usuario['nome'];
    $_SESSION['telefone_usuario'] = $usuario['telefone'];

    header('Location: ../pages/dashboard.php');
    exit;
} catch (PDOException $e) {
    header('Location: ../pages/login.php?erro=sistema');
    exit;
}
