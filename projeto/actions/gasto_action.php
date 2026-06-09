<?php
session_start();
require_once __DIR__ . '/../config/connect.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../pages/login.php');
    exit;
}

$idUsuario = (int) $_SESSION['id_usuario'];
$acao = $_POST['acao'] ?? '';
$origem = $_POST['origem'] ?? 'gastos';

function voltarGastos(string $status = '', string $origem = 'gastos'): void
{
    $pagina = $origem === 'contas' ? 'contas.php' : 'gastos.php';
    $sufixo = $status !== '' ? '?status=' . urlencode($status) : '';
    header('Location: ../pages/' . $pagina . $sufixo);
    exit;
}

try {
    if ($acao === 'criar') {
        $descricao = trim($_POST['descricao_gasto'] ?? '');
        $valor = str_replace(['.', ','], ['', '.'], trim($_POST['valor_gastos'] ?? '0'));
        $dataGasto = $_POST['data_gasto'] ?? date('Y-m-d');
        $vencimento = $_POST['vencimento_gasto'] ?? '';
        $status = $_POST['status'] === 'pago' ? 'pago' : 'pendente';
        $idCategoria = !empty($_POST['id_categoria']) ? (int) $_POST['id_categoria'] : null;
        $observacoes = trim($_POST['observacoes'] ?? '');

        if ($descricao === '' || (float) $valor <= 0 || $dataGasto === '') {
            voltarGastos('erro', $origem);
        }

        $stmt = $conn->prepare("
            INSERT INTO gasto (
                id_usuario,
                id_categoria,
                descricao_gasto,
                observacoes,
                valor_gastos,
                data_gasto,
                vencimento_gasto,
                status,
                pago_quando
            ) VALUES (
                :id_usuario,
                :id_categoria,
                :descricao_gasto,
                :observacoes,
                :valor_gastos,
                :data_gasto,
                :vencimento_gasto,
                :status,
                :pago_quando
            )
        ");

        $stmt->execute([
            ':id_usuario' => $idUsuario,
            ':id_categoria' => $idCategoria,
            ':descricao_gasto' => $descricao,
            ':observacoes' => $observacoes !== '' ? $observacoes : null,
            ':valor_gastos' => (float) $valor,
            ':data_gasto' => $dataGasto,
            ':vencimento_gasto' => $vencimento !== '' ? $vencimento : null,
            ':status' => $status,
            ':pago_quando' => $status === 'pago' ? date('Y-m-d H:i:s') : null,
        ]);

        voltarGastos('salvo', $origem);
    }

    if ($acao === 'alternar_status') {
        $idGasto = (int) ($_POST['id_gasto'] ?? 0);

        $stmt = $conn->prepare("
            SELECT status
            FROM gasto
            WHERE id_gasto = :id_gasto
              AND id_usuario = :id_usuario
              AND deletado_quando IS NULL
            LIMIT 1
        ");
        $stmt->execute([
            ':id_gasto' => $idGasto,
            ':id_usuario' => $idUsuario,
        ]);
        $gasto = $stmt->fetch();

        if (!$gasto) {
            voltarGastos('erro', $origem);
        }

        $novoStatus = $gasto['status'] === 'pago' ? 'pendente' : 'pago';

        $stmt = $conn->prepare("
            UPDATE gasto
            SET status = :status,
                pago_quando = :pago_quando
            WHERE id_gasto = :id_gasto
              AND id_usuario = :id_usuario
        ");
        $stmt->execute([
            ':status' => $novoStatus,
            ':pago_quando' => $novoStatus === 'pago' ? date('Y-m-d H:i:s') : null,
            ':id_gasto' => $idGasto,
            ':id_usuario' => $idUsuario,
        ]);

        voltarGastos('atualizado', $origem);
    }

    if ($acao === 'apagar') {
        $idGasto = (int) ($_POST['id_gasto'] ?? 0);

        $stmt = $conn->prepare("
            UPDATE gasto
            SET deletado_quando = NOW()
            WHERE id_gasto = :id_gasto
              AND id_usuario = :id_usuario
        ");
        $stmt->execute([
            ':id_gasto' => $idGasto,
            ':id_usuario' => $idUsuario,
        ]);

        voltarGastos('apagado', $origem);
    }

    if ($acao === 'apagar_vencidas') {
        $stmt = $conn->prepare("
            UPDATE gasto
            SET deletado_quando = NOW()
            WHERE id_usuario = :id_usuario
              AND deletado_quando IS NULL
              AND vencimento_gasto IS NOT NULL
              AND status = 'pendente'
              AND vencimento_gasto < CURDATE()
        ");
        $stmt->execute([':id_usuario' => $idUsuario]);

        voltarGastos('apagado', $origem);
    }

    voltarGastos('', $origem);
} catch (PDOException $e) {
    voltarGastos('erro', $origem);
}
