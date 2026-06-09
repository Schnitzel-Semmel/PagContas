<?php
session_start();
require_once __DIR__ . '/../config/connect.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../pages/login.php');
    exit;
}

$idUsuario = (int) $_SESSION['id_usuario'];
$nome = trim($_POST['nome'] ?? '');
$modoSimplificado = isset($_POST['modo_simplificado']) ? 1 : 0;
$altoContraste = isset($_POST['alto_contraste']) ? 1 : 0;
$tipoAgendamento = $_POST['tipo_agendamento'] ?? 'desativado';
$intervaloDias = $_POST['intervalo_dias'] !== '' ? (int) $_POST['intervalo_dias'] : null;
$horarioEnvio = $_POST['horario_envio'] !== '' ? $_POST['horario_envio'] . ':00' : '09:00:00';

if (!in_array($tipoAgendamento, ['intervalo', 'personalizado', 'desativado'], true)) {
    $tipoAgendamento = 'desativado';
}

if ($nome === '') {
    header('Location: ../pages/configuracoes.php?status=erro');
    exit;
}

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare("
        UPDATE usuario
        SET nome = :nome,
            modo_simplificado = :modo_simplificado,
            alto_contraste = :alto_contraste
        WHERE id_usuario = :id_usuario
    ");
    $stmt->execute([
        ':nome' => $nome,
        ':modo_simplificado' => $modoSimplificado,
        ':alto_contraste' => $altoContraste,
        ':id_usuario' => $idUsuario,
    ]);

    $stmt = $conn->prepare("
        INSERT INTO config_relatorios_usuario (
            id_usuario,
            tipo_agendamento,
            intervalo_dias,
            horario_envio,
            fuso_horario,
            ativo
        ) VALUES (
            :id_usuario,
            :tipo_agendamento,
            :intervalo_dias,
            :horario_envio,
            'America/Sao_Paulo',
            1
        )
        ON DUPLICATE KEY UPDATE
            tipo_agendamento = VALUES(tipo_agendamento),
            intervalo_dias = VALUES(intervalo_dias),
            horario_envio = VALUES(horario_envio),
            ativo = VALUES(ativo)
    ");
    $stmt->execute([
        ':id_usuario' => $idUsuario,
        ':tipo_agendamento' => $tipoAgendamento,
        ':intervalo_dias' => $intervaloDias,
        ':horario_envio' => $horarioEnvio,
    ]);

    $conn->commit();

    $_SESSION['nome_usuario'] = $nome;

    header('Location: ../pages/configuracoes.php?status=salvo');
    exit;
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    header('Location: ../pages/configuracoes.php?status=erro');
    exit;
}
