<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$tituloPagina = $tituloPagina ?? 'PagContas';
$cssPagina = $cssPagina ?? null;
$nomeUsuario = $_SESSION['nome_usuario'] ?? 'Usuario';
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

            <div style="display:flex;align-items:center;gap:16px;">
                <span style="color:#374151;">Ola, <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="../actions/logout_action.php" style="color:#b91c1c;text-decoration:none;font-weight:600;">Sair</a>
            </div>
        </div>
    </header>
