<?php
session_start();
require_once __DIR__ . '/../config/connect.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../pages/login.php');
    exit;
}

$idUsuario = (int) $_SESSION['id_usuario'];
$acao = $_POST['acao'] ?? '';

function voltarCategorias(string $status = ''): void
{
    $sufixo = $status !== '' ? '?status=' . urlencode($status) : '';
    header('Location: ../pages/categorias.php' . $sufixo);
    exit;
}

try {
    if ($acao === 'criar') {
        $nome = trim($_POST['nome_categoria'] ?? '');
        $cor = trim($_POST['cor'] ?? '#239a55');
        $meta = $_POST['meta_mensal'] !== ''
            ? (float) str_replace(['.', ','], ['', '.'], trim($_POST['meta_mensal']))
            : null;

        if ($nome === '') {
            voltarCategorias('erro');
        }

        $stmt = $conn->prepare("
            INSERT INTO categoria (id_usuario, nome_categoria, cor, meta_mensal, is_system, is_active)
            VALUES (:id_usuario, :nome_categoria, :cor, :meta_mensal, 0, 1)
        ");
        $stmt->execute([
            ':id_usuario' => $idUsuario,
            ':nome_categoria' => $nome,
            ':cor' => $cor,
            ':meta_mensal' => $meta,
        ]);

        voltarCategorias('salvo');
    }

    if ($acao === 'apagar') {
        $idCategoria = (int) ($_POST['id_categoria'] ?? 0);

        $stmt = $conn->prepare("
            UPDATE categoria
            SET is_active = 0
            WHERE id_categoria = :id_categoria
              AND id_usuario = :id_usuario
              AND is_system = 0
        ");
        $stmt->execute([
            ':id_categoria' => $idCategoria,
            ':id_usuario' => $idUsuario,
        ]);

        voltarCategorias('apagado');
    }

    if ($acao === 'editar_meta') {
        $idCategoria = (int) ($_POST['id_categoria'] ?? 0);
        $meta = $_POST['meta_mensal'] !== ''
            ? (float) str_replace(['.', ','], ['', '.'], trim($_POST['meta_mensal']))
            : null;

        $stmt = $conn->prepare("
            UPDATE categoria
            SET meta_mensal = :meta_mensal
            WHERE id_categoria = :id_categoria
              AND is_active = 1
              AND (id_usuario IS NULL OR id_usuario = :id_usuario)
        ");
        $stmt->execute([
            ':meta_mensal' => $meta,
            ':id_categoria' => $idCategoria,
            ':id_usuario' => $idUsuario,
        ]);

        voltarCategorias('meta');
    }

    voltarCategorias();
} catch (PDOException $e) {
    voltarCategorias('erro');
}
