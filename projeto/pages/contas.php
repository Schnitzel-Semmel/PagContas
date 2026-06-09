<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/connect.php';

$tituloPagina = 'Contas | PagContas';
$tituloHeader = 'Contas';
$cssPagina    = 'contas.css';
$idUsuario    = (int) $_SESSION['id_usuario'];
$statusMsg    = $_GET['status'] ?? '';
$hoje         = date('Y-m-d');

function dinheiroConta(float $v): string {
    return 'R$ ' . number_format($v, 2, ',', '.');
}
function dataConta(?string $d): string {
    return $d ? date('d/m/Y', strtotime($d)) : '-';
}

$stmt = $conn->prepare("SELECT g.*, COALESCE(c.nome_categoria,'Sem categoria') AS nome_categoria, COALESCE(c.cor,'#40916C') AS cor_categoria FROM gasto g LEFT JOIN categoria c ON c.id_categoria=g.id_categoria WHERE g.id_usuario=:u AND g.deletado_quando IS NULL AND g.vencimento_gasto IS NOT NULL AND g.status='pendente' AND g.vencimento_gasto < :h ORDER BY g.vencimento_gasto ASC");
$stmt->execute([':u'=>$idUsuario,':h'=>$hoje]);
$vencidas = $stmt->fetchAll();

$stmt = $conn->prepare("SELECT g.*, COALESCE(c.nome_categoria,'Sem categoria') AS nome_categoria, COALESCE(c.cor,'#40916C') AS cor_categoria FROM gasto g LEFT JOIN categoria c ON c.id_categoria=g.id_categoria WHERE g.id_usuario=:u AND g.deletado_quando IS NULL AND g.vencimento_gasto IS NOT NULL AND g.status='pendente' AND g.vencimento_gasto >= :h ORDER BY g.vencimento_gasto ASC");
$stmt->execute([':u'=>$idUsuario,':h'=>$hoje]);
$pendentes = $stmt->fetchAll();

$stmt = $conn->prepare("SELECT g.*, COALESCE(c.nome_categoria,'Sem categoria') AS nome_categoria, COALESCE(c.cor,'#40916C') AS cor_categoria FROM gasto g LEFT JOIN categoria c ON c.id_categoria=g.id_categoria WHERE g.id_usuario=:u AND g.deletado_quando IS NULL AND g.vencimento_gasto IS NOT NULL AND g.status='pago' ORDER BY g.pago_quando DESC, g.vencimento_gasto DESC LIMIT 8");
$stmt->execute([':u'=>$idUsuario]);
$pagas = $stmt->fetchAll();

/* Categorias para o detail sheet */
$stmt = $conn->prepare("SELECT id_categoria, nome_categoria, cor FROM categoria WHERE is_active=1 AND (id_usuario IS NULL OR id_usuario=:u) ORDER BY nome_categoria ASC");
$stmt->execute([':u'=>$idUsuario]);
$catSheet = $stmt->fetchAll();

$totalVencido  = array_sum(array_map(fn($c) => (float)$c['valor_gastos'], $vencidas));
$totalPendente = array_sum(array_map(fn($c) => (float)$c['valor_gastos'], $pendentes));

$mensagens = ['atualizado'=>'Status atualizado.','apagado'=>'Conta apagada.','erro'=>'Não foi possível concluir.'];

require_once '../includes/header.php';
?>

<main class="pagina pagina-contas">

  <?php if ($statusMsg): ?>
    <div class="aviso-contas aviso-contas--<?= $statusMsg === 'erro' ? 'erro' : 'sucesso'; ?>">
      <?= htmlspecialchars($mensagens[$statusMsg] ?? '', ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>

  <!-- Resumo -->
  <div class="contas-resumo">
    <div class="contas-chip contas-chip--vencida">
      <span class="contas-chip__label">Em atraso</span>
      <strong><?= dinheiroConta($totalVencido) ?></strong>
      <span class="contas-chip__count"><?= count($vencidas) ?> conta<?= count($vencidas) !== 1 ? 's' : '' ?></span>
    </div>
    <div class="contas-chip contas-chip--pendente">
      <span class="contas-chip__label">A vencer</span>
      <strong><?= dinheiroConta($totalPendente) ?></strong>
      <span class="contas-chip__count"><?= count($pendentes) ?> conta<?= count($pendentes) !== 1 ? 's' : '' ?></span>
    </div>
  </div>

  <!-- Vencidas -->
  <?php if ($vencidas): ?>
  <div class="sec-header">
    <h3 style="color:var(--red);">Vencidas</h3>
    <form action="../actions/gasto_action.php" method="post" onsubmit="return confirm('Apagar todas as vencidas?');" style="margin:0;">
      <input type="hidden" name="acao" value="apagar_vencidas">
      <input type="hidden" name="origem" value="contas">
      <button type="submit" class="btn btn-danger btn-sm">Apagar todas</button>
    </form>
  </div>
  <div class="card" style="margin:0 16px 10px;">
    <?php foreach ($vencidas as $i => $conta): ?>
      <?php require '../includes/item_conta.php'; ?>
      <?php if ($i < count($vencidas)-1): ?><div style="height:1px;background:var(--border);margin:0 16px;"></div><?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Próximas -->
  <div class="sec-header" style="margin-top:4px;">
    <h3>Próximas</h3>
  </div>
  <div class="card" style="margin:0 16px 10px;">
    <?php if ($pendentes): ?>
      <?php foreach ($pendentes as $i => $conta): ?>
        <?php require '../includes/item_conta.php'; ?>
        <?php if ($i < count($pendentes)-1): ?><div style="height:1px;background:var(--border);margin:0 16px;"></div><?php endif; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty">
        <svg viewBox="0 0 24 24"><path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/></svg>
        <p>Nenhuma conta pendente com vencimento.</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Pagas recentes -->
  <?php if ($pagas): ?>
  <div class="sec-header" style="margin-top:4px;">
    <h3>Pagas recentemente</h3>
  </div>
  <div class="card" style="margin:0 16px 10px;">
    <?php foreach ($pagas as $i => $conta): ?>
      <?php require '../includes/item_conta.php'; ?>
      <?php if ($i < count($pagas)-1): ?><div style="height:1px;background:var(--border);margin:0 16px;"></div><?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</main>

<!-- Detail Sheet -->
<?php require_once '../includes/gasto_detail_sheet.php'; ?>
<script>window.CATEGORIAS = <?= json_encode($catSheet, JSON_HEX_QUOT | JSON_HEX_TAG) ?>;</script>
<script src="../js/gasto-detail.js"></script>

<?php require_once '../includes/footer.php'; ?>
