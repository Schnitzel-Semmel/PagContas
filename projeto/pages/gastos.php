<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/connect.php';

$tituloPagina  = 'Gastos | PagContas';
$tituloHeader  = 'Gastos';
$cssPagina     = 'gastos.css';
$idUsuario     = (int) $_SESSION['id_usuario'];
$statusMsg     = $_GET['status'] ?? '';

function dinheiroG(float $v): string {
    return 'R$ ' . number_format($v, 2, ',', '.');
}
function dataG(?string $d): string {
    return $d ? date('d/m/Y', strtotime($d)) : '-';
}
function rotuloData(string $d): string {
    $hoje  = date('Y-m-d');
    $ontem = date('Y-m-d', strtotime('-1 day'));
    if ($d === $hoje)  return 'Hoje';
    if ($d === $ontem) return 'Ontem';
    return date('d \d\e M', strtotime($d));
}

/* Categorias para o sheet de adição e para o sheet de detalhes */
$stmt = $conn->prepare("SELECT id_categoria, nome_categoria, cor FROM categoria WHERE is_active=1 AND (id_usuario IS NULL OR id_usuario=:u) ORDER BY is_system DESC, nome_categoria ASC");
$stmt->execute([':u'=>$idUsuario]);
$categorias = $stmt->fetchAll();

/* Gastos */
$stmt = $conn->prepare("
    SELECT g.id_gasto, g.id_categoria, g.descricao_gasto, g.observacoes, g.valor_gastos,
           g.data_gasto, g.vencimento_gasto, g.status,
           COALESCE(c.nome_categoria,'Sem categoria') AS nome_categoria,
           COALESCE(c.cor,'#40916C') AS cor_categoria
    FROM gasto g
    LEFT JOIN categoria c ON c.id_categoria=g.id_categoria
    WHERE g.id_usuario=:u AND g.deletado_quando IS NULL
    ORDER BY g.data_gasto DESC, g.id_gasto DESC
");
$stmt->execute([':u'=>$idUsuario]);
$gastos = $stmt->fetchAll();

/* Agrupa por data */
$gastosPorData = [];
foreach ($gastos as $g) {
    $gastosPorData[$g['data_gasto']][] = $g;
}

$mensagens = ['salvo'=>'Gasto cadastrado.','atualizado'=>'Status atualizado.','apagado'=>'Gasto apagado.','erro'=>'Não foi possível concluir a ação.'];

require_once '../includes/header.php';
?>

<main class="pagina pagina-gastos">

  <?php if ($statusMsg): ?>
    <div class="aviso-gastos aviso-gastos--<?= $statusMsg === 'erro' ? 'erro' : 'sucesso'; ?>">
      <?= htmlspecialchars($mensagens[$statusMsg] ?? '', ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>

  <!-- Chips de filtro -->
  <div class="chips" data-filtro-grupo="status">
    <button class="chip chip--ativo" data-filtro="todos">Todos</button>
    <button class="chip" data-filtro="pendente">Pendentes</button>
    <button class="chip" data-filtro="pago">Pagos</button>
  </div>

  <!-- Lista agrupada por data -->
  <?php if ($gastosPorData): ?>
    <?php foreach ($gastosPorData as $data => $itens): ?>
    <div class="grupo-data">
      <div class="divisor-data"><?= rotuloData($data) ?></div>
      <div class="card" style="margin:0 16px 10px;">
        <?php foreach ($itens as $i => $g): ?>
        <div
          class="gasto-item stripe-item stripe-<?= $g['status'] ?> gasto-clickable"
          data-id="<?= (int)$g['id_gasto'] ?>"
          data-desc="<?= htmlspecialchars($g['descricao_gasto'], ENT_QUOTES, 'UTF-8') ?>"
          data-valor="<?= htmlspecialchars((string)$g['valor_gastos'], ENT_QUOTES, 'UTF-8') ?>"
          data-data="<?= htmlspecialchars($g['data_gasto'], ENT_QUOTES, 'UTF-8') ?>"
          data-venc="<?= htmlspecialchars($g['vencimento_gasto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          data-status="<?= $g['status'] ?>"
          data-id-categoria="<?= (int)($g['id_categoria'] ?? 0) ?>"
          data-nome-categoria="<?= htmlspecialchars($g['nome_categoria'], ENT_QUOTES, 'UTF-8') ?>"
          data-obs="<?= htmlspecialchars($g['observacoes'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        >
          <div class="gasto-item__avatar">
            <div class="avatar avatar-sm" style="background:<?= htmlspecialchars($g['cor_categoria'], ENT_QUOTES, 'UTF-8') ?>;">
              <?= mb_strtoupper(mb_substr($g['nome_categoria'], 0, 1, 'UTF-8'), 'UTF-8') ?>
            </div>
          </div>
          <div class="gasto-item__info">
            <strong><?= htmlspecialchars($g['descricao_gasto'], ENT_QUOTES, 'UTF-8') ?></strong>
            <span><?= htmlspecialchars($g['nome_categoria'], ENT_QUOTES, 'UTF-8') ?><?= $g['vencimento_gasto'] ? ' · Vence '.dataG($g['vencimento_gasto']) : '' ?></span>
          </div>
          <div class="gasto-item__right">
            <strong style="color:var(--red);">-<?= dinheiroG((float)$g['valor_gastos']) ?></strong>
            <span class="badge <?= $g['status']==='pago' ? 'badge-pago' : 'badge-pendente' ?>"><?= $g['status']==='pago' ? 'Pago' : 'Pendente' ?></span>
          </div>
        </div>
        <?php if ($i < count($itens)-1): ?>
        <div style="height:1px;background:var(--border);margin:0 16px;"></div>
        <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="card" style="margin:16px;">
      <div class="empty">
        <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
        <strong>Nenhum gasto ainda</strong>
        <p>Toque no botão + para registrar seu primeiro gasto.</p>
      </div>
    </div>
  <?php endif; ?>

</main>

<!-- FAB -->
<button class="fab" id="fab" aria-label="Novo gasto">
  <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
</button>

<!-- Backdrop (sheet de adição) -->
<div class="sheet-bg" id="sheetBg"></div>

<!-- Bottom Sheet: Novo Gasto -->
<div class="bottom-sheet" id="sheetAdd" role="dialog" aria-label="Novo gasto">
  <div class="sheet-handle"></div>
  <div class="sheet-header">
    <h2>Novo gasto</h2>
    <button class="sheet-close" aria-label="Fechar">✕</button>
  </div>
  <div class="sheet-body">
    <form action="../actions/gasto_action.php" method="post" class="novo-gasto-form">
      <input type="hidden" name="acao" value="criar">

      <!-- Descrição -->
      <input class="ng-desc" type="text" name="descricao_gasto"
             placeholder="Descrição do gasto" autocomplete="off" required>

      <!-- Valor hero -->
      <div class="ng-valor-wrap">
        <span class="ng-valor-prefix">R$</span>
        <input class="ng-valor" type="text" name="valor_gastos"
               placeholder="0,00" inputmode="decimal" required>
      </div>

      <!-- Status + Data -->
      <div class="ng-row">
        <div class="ng-section">
          <span class="ng-label">Status</span>
          <div class="ng-status-toggle">
            <button type="button" class="ng-status-btn ng-status-btn--active" data-val="pendente">Pendente</button>
            <button type="button" class="ng-status-btn" data-val="pago">✓ Pago</button>
          </div>
          <input type="hidden" name="status" value="pendente">
        </div>
        <div class="ng-section">
          <span class="ng-label">Data</span>
          <div class="ng-data-row">
            <input type="date" name="data_gasto" value="<?= date('Y-m-d') ?>" required>
            <button type="button" class="ng-today-btn">Hoje</button>
          </div>
        </div>
      </div>

      <!-- Categoria -->
      <div class="ng-section">
        <span class="ng-label">Categoria</span>
        <div class="ng-cat-chips">
          <button type="button" class="ng-cat-chip ng-cat-chip--active" data-id="">
            Sem categoria
          </button>
          <?php foreach ($categorias as $c): ?>
          <button type="button" class="ng-cat-chip"
                  data-id="<?= (int)$c['id_categoria'] ?>"
                  data-cor="<?= htmlspecialchars($c['cor'], ENT_QUOTES, 'UTF-8') ?>">
            <span class="ng-cat-dot" style="background:<?= htmlspecialchars($c['cor'], ENT_QUOTES, 'UTF-8') ?>;"></span>
            <?= htmlspecialchars($c['nome_categoria'], ENT_QUOTES, 'UTF-8') ?>
          </button>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="id_categoria" value="">
      </div>

      <!-- Mais opções (vencimento + observações) -->
      <button type="button" class="ng-mais-btn">
        Mais opções
        <svg viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
      </button>
      <div class="ng-mais-body">
        <div class="campo">
          <label>Vencimento <span style="font-weight:400;opacity:.6;">(opcional)</span></label>
          <input type="date" name="vencimento_gasto">
        </div>
        <div class="campo">
          <label>Observações</label>
          <textarea name="observacoes" placeholder="Detalhes opcionais" rows="2"></textarea>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block ng-submit">Salvar gasto</button>
    </form>
  </div>
</div>

<!-- Detail Sheet de edição -->
<?php require_once '../includes/gasto_detail_sheet.php'; ?>

<script>window.CATEGORIAS = <?= json_encode($categorias, JSON_HEX_QUOT | JSON_HEX_TAG) ?>;</script>
<script src="../js/gasto-detail.js"></script>

<?php require_once '../includes/footer.php'; ?>
