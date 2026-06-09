<?php
session_start();
require_once __DIR__ . '/../config/connect.php';
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['ok' => false, 'msg' => 'Não autenticado']);
    exit;
}

$idUsuario = (int) $_SESSION['id_usuario'];
$acao      = $_POST['acao'] ?? '';

try {
    /* ── CRIAR ── */
    if ($acao === 'criar') {
        $descricao   = trim($_POST['descricao_gasto'] ?? '');
        $valor       = (float) str_replace(['.', ','], ['', '.'], trim($_POST['valor_gastos'] ?? '0'));
        $dataGasto   = $_POST['data_gasto']       ?? date('Y-m-d');
        $vencimento  = trim($_POST['vencimento_gasto'] ?? '');
        $status      = ($_POST['status'] ?? '') === 'pago' ? 'pago' : 'pendente';
        $idCategoria = !empty($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : null;
        $observacoes = trim($_POST['observacoes'] ?? '');

        if ($descricao === '' || $valor <= 0) {
            echo json_encode(['ok' => false, 'msg' => 'Preencha descrição e valor.']);
            exit;
        }

        $stmt = $conn->prepare("
            INSERT INTO gasto (id_usuario, id_categoria, descricao_gasto, observacoes,
                               valor_gastos, data_gasto, vencimento_gasto, status, pago_quando)
            VALUES (:u, :cat, :desc, :obs, :valor, :data, :venc, :status, :pago_q)
        ");
        $stmt->execute([
            ':u'      => $idUsuario,
            ':cat'    => $idCategoria,
            ':desc'   => $descricao,
            ':obs'    => $observacoes !== '' ? $observacoes : null,
            ':valor'  => $valor,
            ':data'   => $dataGasto,
            ':venc'   => $vencimento !== '' ? $vencimento : null,
            ':status' => $status,
            ':pago_q' => $status === 'pago' ? date('Y-m-d H:i:s') : null,
        ]);

        echo json_encode(['ok' => true]);
        exit;
    }

    /* ── EDITAR ── */
    if ($acao === 'editar') {
        $idGasto   = (int)  ($_POST['id_gasto']        ?? 0);
        $descricao = trim(  $_POST['descricao_gasto']   ?? '');
        $valor     = (float) str_replace(['.', ','], ['', '.'], trim($_POST['valor_gastos'] ?? '0'));
        $dataGasto = $_POST['data_gasto']               ?? date('Y-m-d');
        $vencimento = $_POST['vencimento_gasto']        ?? '';
        $status    = ($_POST['status'] ?? '') === 'pago' ? 'pago' : 'pendente';
        $idCategoria = !empty($_POST['id_categoria'])   ? (int)$_POST['id_categoria'] : null;
        $observacoes = trim($_POST['observacoes']        ?? '');

        if (!$idGasto || $descricao === '' || $valor <= 0) {
            echo json_encode(['ok' => false, 'msg' => 'Dados inválidos']);
            exit;
        }

        /* Verifica propriedade */
        $chk = $conn->prepare("SELECT id_gasto FROM gasto WHERE id_gasto=:id AND id_usuario=:u AND deletado_quando IS NULL LIMIT 1");
        $chk->execute([':id' => $idGasto, ':u' => $idUsuario]);
        if (!$chk->fetch()) {
            echo json_encode(['ok' => false, 'msg' => 'Gasto não encontrado']);
            exit;
        }

        $stmt = $conn->prepare("
            UPDATE gasto SET
                descricao_gasto  = :desc,
                valor_gastos     = :valor,
                data_gasto       = :data,
                vencimento_gasto = :venc,
                status           = :status,
                id_categoria     = :cat,
                observacoes      = :obs,
                pago_quando      = :pago_q
            WHERE id_gasto = :id AND id_usuario = :u
        ");
        $stmt->execute([
            ':desc'   => $descricao,
            ':valor'  => $valor,
            ':data'   => $dataGasto,
            ':venc'   => $vencimento !== '' ? $vencimento : null,
            ':status' => $status,
            ':cat'    => $idCategoria,
            ':obs'    => $observacoes !== '' ? $observacoes : null,
            ':pago_q' => $status === 'pago' ? date('Y-m-d H:i:s') : null,
            ':id'     => $idGasto,
            ':u'      => $idUsuario,
        ]);

        /* Nome/cor da categoria */
        $catNome = 'Sem categoria';
        $catCor  = '#40916C';
        if ($idCategoria) {
            $cs = $conn->prepare("SELECT nome_categoria, cor FROM categoria WHERE id_categoria=:id LIMIT 1");
            $cs->execute([':id' => $idCategoria]);
            $cat = $cs->fetch();
            if ($cat) { $catNome = $cat['nome_categoria']; $catCor = $cat['cor']; }
        }

        echo json_encode(['ok' => true, 'gasto' => [
            'id_gasto'        => $idGasto,
            'descricao_gasto' => $descricao,
            'valor_gastos'    => $valor,
            'data_gasto'      => $dataGasto,
            'vencimento_gasto'=> $vencimento !== '' ? $vencimento : null,
            'status'          => $status,
            'id_categoria'    => $idCategoria,
            'nome_categoria'  => $catNome,
            'cor_categoria'   => $catCor,
            'observacoes'     => $observacoes,
        ]]);
        exit;
    }

    /* ── APAGAR ── */
    if ($acao === 'apagar') {
        $idGasto = (int)($_POST['id_gasto'] ?? 0);
        if (!$idGasto) { echo json_encode(['ok' => false, 'msg' => 'ID inválido']); exit; }

        $stmt = $conn->prepare("UPDATE gasto SET deletado_quando=NOW() WHERE id_gasto=:id AND id_usuario=:u");
        $stmt->execute([':id' => $idGasto, ':u' => $idUsuario]);

        echo json_encode(['ok' => true]);
        exit;
    }

    echo json_encode(['ok' => false, 'msg' => 'Ação desconhecida']);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'msg' => 'Erro no servidor']);
}
