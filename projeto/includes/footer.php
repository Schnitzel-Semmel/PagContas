<?php
$paginaAtual = $paginaAtual ?? basename($_SERVER['PHP_SELF'] ?? '');

$navItens = [
  'dashboard.php'     => ['Início',     '<path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>'],
  'gastos.php'        => ['Gastos',     '<path d="M19.5 3.5 18 2l-1.5 1.5L15 2l-1.5 1.5L12 2l-1.5 1.5L9 2 7.5 3.5 6 2 4.5 3.5 3 2v20l1.5-1.5L6 22l1.5-1.5L9 22l1.5-1.5L12 22l1.5-1.5L15 22l1.5-1.5L18 22l1.5-1.5L21 22V2l-1.5 1.5zM19 19H5V5h14v14zM7 17h10v-2H7v2zm0-4h10v-2H7v2zm0-4h10V7H7v2z"/>'],
  'categorias.php'    => ['Categorias', '<path d="M3 3h8v8H3V3zm0 10h8v8H3v-8zm10-10h8v8h-8V3zm0 10h8v8h-8v-8z"/>'],
  'contas.php'        => ['Contas',     '<path d="M21 18v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v1h-9c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h9zm-9-2h10V8H12v8zm4-2.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>'],
  'configuracoes.php' => ['Config',     '<path d="M12 15.5A3.5 3.5 0 0 1 8.5 12 3.5 3.5 0 0 1 12 8.5a3.5 3.5 0 0 1 3.5 3.5 3.5 3.5 0 0 1-3.5 3.5m7.43-2.53c.04-.32.07-.65.07-.97s-.03-.66-.07-1l2.03-1.58c.18-.14.23-.41.12-.62l-1.92-3.32c-.12-.22-.37-.3-.59-.22l-2.39.96a7.2 7.2 0 0 0-1.62-.94l-.36-2.54A.49.49 0 0 0 14 2h-4a.49.49 0 0 0-.47.41l-.36 2.54a7.2 7.2 0 0 0-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.47c-.12.22-.07.47.12.62L4.89 10.5c-.04.34-.07.67-.07 1s.03.65.07.97l-2.03 1.58c-.18.14-.23.41-.12.62l1.92 3.32c.12.22.37.3.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.04.24.24.41.48.41h4c.24 0 .44-.17.47-.41l.36-2.54a7.2 7.2 0 0 0 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.11-.21.06-.48-.12-.62l-2.01-1.58z"/>'],
];
?>
<nav class="bottom-nav" aria-label="Navegação">
<?php foreach ($navItens as $href => [$label, $path]): ?>
  <a href="../pages/<?= $href ?>" class="bottom-nav__item<?= $paginaAtual === $href ? ' bottom-nav__item--ativo' : ''; ?>">
    <svg viewBox="0 0 24 24" aria-hidden="true"><?= $path ?></svg>
    <span><?= $label ?></span>
  </a>
<?php endforeach; ?>
</nav>
<script src="../js/global.js"></script>
</body>
</html>
