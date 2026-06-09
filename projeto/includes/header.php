<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

$tituloPagina = $tituloPagina ?? 'PagContas';
$tituloHeader = $tituloHeader ?? 'PagContas';
$cssPagina    = $cssPagina ?? null;
$nomeUsuario  = $_SESSION['nome_usuario'] ?? 'Usuário';
$paginaAtual  = basename($_SERVER['PHP_SELF'] ?? '');
$inicialUsuario = mb_strtoupper(mb_substr($nomeUsuario, 0, 1, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8'); ?></title>
  <script>
    /* Aplica preferências visuais antes do render para evitar flash */
    (function(){
      var cl = document.documentElement.classList;
      if (localStorage.getItem('theme')    === 'dark') cl.add('dark');
      if (localStorage.getItem('contrast') === '1')    cl.add('high-contrast');
    })();
  </script>
  <link rel="stylesheet" href="../css/global.css">
  <?php if (!empty($cssPagina)): ?>
    <link rel="stylesheet" href="../css/<?= htmlspecialchars($cssPagina, ENT_QUOTES, 'UTF-8'); ?>">
  <?php endif; ?>
  <link rel="stylesheet" href="../css/desktop.css">
</head>
<body>
<header class="top-bar">
  <a href="dashboard.php" class="top-bar__title">PagContas</a>
  <div class="top-bar__avatar-wrap" id="topBarAvatarWrap">
    <button class="top-bar__icon" id="topBarAvatar" aria-haspopup="true" aria-expanded="false"
            aria-label="Menu do usuário: <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?>">
      <div id="topBarAvatarInner" style="width:32px;height:32px;border-radius:50%;background:var(--green-dark);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;color:#fff;">
        <?= $inicialUsuario; ?>
      </div>
    </button>
    <div class="top-bar__menu" id="topBarMenu" hidden>
      <a href="configuracoes.php" class="top-bar__menu-item">
        <svg viewBox="0 0 24 24"><path d="M12 15.5A3.5 3.5 0 0 1 8.5 12 3.5 3.5 0 0 1 12 8.5a3.5 3.5 0 0 1 3.5 3.5 3.5 3.5 0 0 1-3.5 3.5m7.43-2.53c.04-.32.07-.65.07-.97s-.03-.66-.07-1l2.03-1.58c.18-.14.23-.41.12-.62l-1.92-3.32c-.12-.22-.37-.3-.59-.22l-2.39.96a7.2 7.2 0 0 0-1.62-.94l-.36-2.54A.49.49 0 0 0 14 2h-4a.49.49 0 0 0-.47.41l-.36 2.54a7.2 7.2 0 0 0-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.47c-.12.22-.07.47.12.62L4.89 10.5c-.04.34-.07.67-.07 1s.03.65.07.97l-2.03 1.58c-.18.14-.23.41-.12.62l1.92 3.32c.12.22.37.3.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.04.24.24.41.48.41h4c.24 0 .44-.17.47-.41l.36-2.54a7.2 7.2 0 0 0 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.11-.21.06-.48-.12-.62l-2.01-1.58z"/></svg>
        Configurações
      </a>
      <a href="../actions/logout_action.php" class="top-bar__menu-item top-bar__menu-item--danger">
        <svg viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
        Sair da conta
      </a>
    </div>
  </div>
</header>
