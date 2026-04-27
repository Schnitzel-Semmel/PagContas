<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

$tituloPagina = $tituloPagina ?? 'PagContas';
$cssPagina = $cssPagina ?? null;
$nomeUsuario = $_SESSION['nome_usuario'] ?? 'Usuário';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8'); ?></title>

    <link rel="stylesheet" href="../css/global.css">

    <?php if (!empty($cssPagina)): ?>
        <link rel="stylesheet" href="../css/<?= htmlspecialchars($cssPagina, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
</head>
<body>
    <header style="background:#ffffff;border-bottom:1px solid #dbe3ee;padding:16px 24px;">
        <div style="max-width:1100px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:16px;">
            <div>
                <strong style="font-size:1.2rem;color:#239a55;">PagContas</strong>
            </div>

            <div>
                <a href="../pages/categorias.php" style="color:#239a55;text-decoration:none;font-weight:600;">Categorias</a>
            </div>

            <div>
                <a href="../pages/configuracoes.php" style="color:#239a55;text-decoration:none;font-weight:600;">Configurações</a>
            </div>

            <div>
                <a href="../pages/contas.php" style="color:#239a55;text-decoration:none;font-weight:600;">Contas</a>
            </div>

            <div>
                <a href="../pages/gastos.php" style="color:#239a55;text-decoration:none;font-weight:600;">Gastos</a>
            </div>

            <div style="display:flex;align-items:center;gap:16px;">
                <span style="color:#374151;">Olá, <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="../actions/logout_action.php" style="color:#b91c1c;text-decoration:none;font-weight:600;">Sair</a>
            </div>
        </div>
    </header>
