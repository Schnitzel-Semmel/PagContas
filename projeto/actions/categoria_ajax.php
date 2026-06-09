<?php
session_start();
require_once __DIR__ . '/../config/connect.php';
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['ok' => false]);
    exit;
}

$idUsuario = (int) $_SESSION['id_usuario'];
$acao      = $_POST['acao'] ?? '';

try {
    /* ── CRIAR ── */
    if ($acao === 'criar') {
        $nome  = trim($_POST['nome_categoria'] ?? '');
        $cor   = trim($_POST['cor']            ?? '#40916C');
        $icone = trim($_POST['icone']          ?? '');
        $metaStr = trim($_POST['meta_mensal']  ?? '');
        $meta  = $metaStr !== '' ? (float) str_replace(['.', ','], ['', '.'], $metaStr) : null;

        if ($nome === '') { echo json_encode(['ok' => false, 'msg' => 'Nome obrigatório']); exit; }

        $stmt = $conn->prepare("INSERT INTO categoria (id_usuario, nome_categoria, cor, meta_mensal, is_system, is_active) VALUES (:u,:nome,:cor,:meta,0,1)");
        $stmt->execute([':u' => $idUsuario, ':nome' => $nome, ':cor' => $cor, ':meta' => $meta]);
        $newId = (int) $conn->lastInsertId();

        echo json_encode(['ok' => true, 'id_categoria' => $newId, 'nome_categoria' => $nome, 'cor' => $cor, 'icone' => $icone, 'is_system' => 0]);
        exit;
    }

    /* ── RENOMEAR ── */
    if ($acao === 'renomear') {
        $idCategoria = (int)($_POST['id_categoria'] ?? 0);
        $nome        = trim($_POST['nome_categoria'] ?? '');
        if (!$idCategoria || $nome === '') { echo json_encode(['ok' => false]); exit; }

        $stmt = $conn->prepare("UPDATE categoria SET nome_categoria=:nome WHERE id_categoria=:id AND id_usuario=:u AND is_system=0 AND is_active=1");
        $stmt->execute([':nome' => $nome, ':id' => $idCategoria, ':u' => $idUsuario]);

        echo json_encode(['ok' => true]);
        exit;
    }

    /* ── APAGAR ── */
    if ($acao === 'apagar') {
        $idCategoria = (int)($_POST['id_categoria'] ?? 0);

        $stmt = $conn->prepare("UPDATE categoria SET is_active=0 WHERE id_categoria=:id AND id_usuario=:u AND is_system=0");
        $stmt->execute([':id' => $idCategoria, ':u' => $idUsuario]);

        echo json_encode(['ok' => true]);
        exit;
    }

    /* ── GASTOS DA CATEGORIA (mês atual) ── */
    if ($acao === 'gastos_categoria') {
        $idCategoria = (int)($_POST['id_categoria'] ?? 0);
        if (!$idCategoria) { echo json_encode(['ok' => false]); exit; }

        $inicioMes = date('Y-m-01');
        $fimMes    = date('Y-m-t');

        $catStmt = $conn->prepare("SELECT nome_categoria, cor FROM categoria WHERE id_categoria=:id AND is_active=1 AND (id_usuario IS NULL OR id_usuario=:u) LIMIT 1");
        $catStmt->execute([':id' => $idCategoria, ':u' => $idUsuario]);
        $cat = $catStmt->fetch();
        if (!$cat) { echo json_encode(['ok' => false]); exit; }

        $gStmt = $conn->prepare("
            SELECT id_gasto, id_categoria, descricao_gasto, observacoes,
                   valor_gastos, data_gasto, vencimento_gasto, status
            FROM gasto
            WHERE id_usuario=:u AND id_categoria=:cat AND deletado_quando IS NULL
              AND data_gasto BETWEEN :i AND :f
            ORDER BY data_gasto DESC, id_gasto DESC
        ");
        $gStmt->execute([':u' => $idUsuario, ':cat' => $idCategoria, ':i' => $inicioMes, ':f' => $fimMes]);
        $gastos = $gStmt->fetchAll(PDO::FETCH_ASSOC);

        $total = array_sum(array_map(fn($g) => (float)$g['valor_gastos'], $gastos));

        echo json_encode([
            'ok'     => true,
            'nome'   => $cat['nome_categoria'],
            'cor'    => $cat['cor'],
            'total'  => $total,
            'gastos' => $gastos,
        ]);
        exit;
    }

    echo json_encode(['ok' => false, 'msg' => 'Ação inválida']);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'msg' => 'Erro no servidor']);
}
