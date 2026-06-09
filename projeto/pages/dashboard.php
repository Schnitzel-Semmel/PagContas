<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/connect.php';

$tituloPagina  = 'Dashboard | PagContas';
$tituloHeader  = 'PagContas';
$cssPagina     = 'dashboard.css';
$nomeUsuario   = $_SESSION['nome_usuario'] ?? 'Usuário';
$idUsuario     = (int) $_SESSION['id_usuario'];
$inicioMes     = date('Y-m-01');
$fimMes        = date('Y-m-t');
$hoje          = date('Y-m-d');

function dinheiro(float $v): string {
    return 'R$ ' . number_format($v, 2, ',', '.');
}
function dataCurta(?string $d): string {
    return $d ? date('d/m', strtotime($d)) : '-';
}

/* Totais */
$stmt = $conn->prepare("SELECT COALESCE(SUM(valor_gastos),0) FROM gasto WHERE id_usuario=:u AND deletado_quando IS NULL AND data_gasto BETWEEN :i AND :f");
$stmt->execute([':u'=>$idUsuario,':i'=>$inicioMes,':f'=>$fimMes]);
$totalMes = (float)$stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM gasto WHERE id_usuario=:u AND deletado_quando IS NULL AND status='pendente'");
$stmt->execute([':u'=>$idUsuario]);
$contasPendentes = (int)$stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM categoria WHERE is_active=1 AND (id_usuario IS NULL OR id_usuario=:u)");
$stmt->execute([':u'=>$idUsuario]);
$categoriasAtivas = (int)$stmt->fetchColumn();

$stmt = $conn->prepare("SELECT tipo_agendamento FROM config_relatorios_usuario WHERE id_usuario=:u LIMIT 1");
$stmt->execute([':u'=>$idUsuario]);
$tipoRelatorio = $stmt->fetchColumn() ?: 'desativado';
$rotulosRelatorio = ['intervalo'=>'Intervalo','personalizado'=>'Personalizado','desativado'=>'Desativado'];

/* Próximos vencimentos */
$stmt = $conn->prepare("SELECT descricao_gasto,vencimento_gasto,valor_gastos FROM gasto WHERE id_usuario=:u AND deletado_quando IS NULL AND status='pendente' AND vencimento_gasto>=:hoje ORDER BY vencimento_gasto ASC LIMIT 3");
$stmt->execute([':u'=>$idUsuario,':hoje'=>$hoje]);
$proximosVencimentos = $stmt->fetchAll();

/* Últimos gastos — inclui todos os campos para o detail sheet */
$stmt = $conn->prepare("
    SELECT g.id_gasto, g.id_categoria, g.descricao_gasto, g.observacoes,
           g.valor_gastos, g.data_gasto, g.vencimento_gasto, g.status,
           COALESCE(c.nome_categoria,'Sem categoria') AS nome_categoria,
           COALESCE(c.cor,'#40916C') AS cor_categoria
    FROM gasto g
    LEFT JOIN categoria c ON c.id_categoria=g.id_categoria
    WHERE g.id_usuario=:u AND g.deletado_quando IS NULL
    ORDER BY g.data_gasto DESC, g.id_gasto DESC
    LIMIT 6
");
$stmt->execute([':u'=>$idUsuario]);
$ultimosGastos = $stmt->fetchAll();

/* Resumo por categoria */
$stmt = $conn->prepare("
    SELECT COALESCE(c.nome_categoria,'Sem categoria') AS nome_categoria,
           COALESCE(c.cor,'#40916C') AS cor,
           COALESCE(SUM(g.valor_gastos),0) AS total_gasto
    FROM gasto g
    LEFT JOIN categoria c ON c.id_categoria=g.id_categoria
    WHERE g.id_usuario=:u AND g.deletado_quando IS NULL AND g.data_gasto BETWEEN :i AND :f
    GROUP BY c.id_categoria, c.nome_categoria, c.cor
    ORDER BY total_gasto DESC LIMIT 5
");
$stmt->execute([':u'=>$idUsuario,':i'=>$inicioMes,':f'=>$fimMes]);
$resumoCategorias = $stmt->fetchAll();
$totalResumo = array_sum(array_map(fn($r)=>(float)$r['total_gasto'], $resumoCategorias));

/* Metas */
$stmt = $conn->prepare("
    SELECT c.nome_categoria, c.cor, c.meta_mensal,
           COALESCE(SUM(g.valor_gastos),0) AS total_gasto
    FROM categoria c
    LEFT JOIN gasto g ON g.id_categoria=c.id_categoria AND g.id_usuario=:ug AND g.deletado_quando IS NULL AND g.data_gasto BETWEEN :i AND :f
    WHERE c.is_active=1 AND (c.id_usuario IS NULL OR c.id_usuario=:uc) AND c.meta_mensal IS NOT NULL
    GROUP BY c.id_categoria
    ORDER BY total_gasto DESC LIMIT 4
");
$stmt->execute([':ug'=>$idUsuario,':uc'=>$idUsuario,':i'=>$inicioMes,':f'=>$fimMes]);
$metasCategorias = $stmt->fetchAll();

/* Categorias para o detail sheet */
$stmt = $conn->prepare("SELECT id_categoria, nome_categoria, cor FROM categoria WHERE is_active=1 AND (id_usuario IS NULL OR id_usuario=:u) ORDER BY nome_categoria ASC");
$stmt->execute([':u'=>$idUsuario]);
$catSheet = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<main class="pagina pagina-dashboard">

  <!-- Hero card -->
  <div class="dash-hero">
    <p class="dash-hero__label">Gastos de <?= date('M/Y') ?></p>
    <h2 class="dash-hero__valor"><?= dinheiro($totalMes) ?></h2>
    <p class="dash-hero__sub">Olá, <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?> 👋</p>
  </div>

  <!-- Chips resumo -->
  <div class="dash-resumo">
    <div class="dash-chip">
      <strong><?= $contasPendentes ?></strong>
      <span>Pendentes</span>
    </div>
    <div class="dash-chip">
      <strong><?= $categoriasAtivas ?></strong>
      <span>Categorias</span>
    </div>
    <div class="dash-chip">
      <strong><?= htmlspecialchars($rotulosRelatorio[$tipoRelatorio] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
      <span>Relatório</span>
    </div>
  </div>

  <div class="dash-grid">

    <!-- Coluna esquerda: gráfico por categoria -->
    <div class="dash-col-left">
      <?php if ($resumoCategorias): ?>
      <div class="sec-header">
        <h3>Por categoria</h3>
        <span style="font-size:.85rem;color:var(--text-2);"><?= date('M/Y') ?></span>
      </div>
      <div class="card" style="margin:0 16px 6px;">
        <div style="display:flex;flex-direction:row;align-items:center;gap:16px;padding:12px 16px;">
          <canvas id="chartCategorias" width="130" height="130" style="flex-shrink:0;"></canvas>
          <div style="flex:1;min-width:0;">
            <?php foreach ($resumoCategorias as $cat):
              $pct = $totalResumo > 0 ? round(((float)$cat['total_gasto'] / $totalResumo) * 100) : 0;
            ?>
            <div class="cat-barra">
              <div class="cat-barra__top">
                <div style="display:flex;align-items:center;gap:8px;">
                  <span class="cat-dot" style="background:<?= htmlspecialchars($cat['cor'], ENT_QUOTES, 'UTF-8') ?>;"></span>
                  <span><?= htmlspecialchars($cat['nome_categoria'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <span style="font-size:.85rem;color:var(--text-2);"><?= $pct ?>%</span>
              </div>
              <div class="progress">
                <div class="progress__fill" style="width:<?= $pct ?>%;background:<?= htmlspecialchars($cat['cor'], ENT_QUOTES, 'UTF-8') ?>;"></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($metasCategorias): ?>
      <div class="sec-header" style="margin-top:8px;">
        <h3>Metas do mês</h3>
        <a href="categorias.php">Gerenciar</a>
      </div>
      <div class="card" style="margin:0 16px 6px;padding:4px 16px 12px;">
        <?php foreach ($metasCategorias as $m):
          $meta  = (float)$m['meta_mensal'];
          $gasto = (float)$m['total_gasto'];
          $pct   = $meta > 0 ? min(100, round(($gasto/$meta)*100)) : 0;
          $cls   = $pct >= 100 ? 'danger' : ($pct >= 75 ? 'warning' : '');
        ?>
        <div class="cat-barra">
          <div class="cat-barra__top">
            <div style="display:flex;align-items:center;gap:8px;">
              <span class="cat-dot" style="background:<?= htmlspecialchars($m['cor'], ENT_QUOTES, 'UTF-8') ?>;"></span>
              <span><?= htmlspecialchars($m['nome_categoria'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <span style="font-size:.82rem;color:var(--text-2);"><?= dinheiro($gasto) ?> / <?= dinheiro($meta) ?></span>
          </div>
          <div class="progress">
            <div class="progress__fill<?= $cls ? ' progress__fill--'.$cls : '' ?>" style="width:<?= $pct ?>%;background:<?= htmlspecialchars($m['cor'], ENT_QUOTES, 'UTF-8') ?>;"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div><!-- /.dash-col-left -->

    <!-- Coluna direita: listas de dados -->
    <div class="dash-col-right">
      <?php if ($proximosVencimentos): ?>
      <div class="sec-header">
        <h3>Próximos vencimentos</h3>
        <a href="contas.php">Ver tudo</a>
      </div>
      <div class="card" style="margin:0 16px 6px;">
        <?php foreach ($proximosVencimentos as $i => $v): ?>
        <div class="venc-item <?= $i < count($proximosVencimentos)-1 ? 'venc-item--sep' : '' ?>">
          <div class="venc-item__info">
            <strong><?= htmlspecialchars($v['descricao_gasto'], ENT_QUOTES, 'UTF-8') ?></strong>
            <span>Vence em <?= dataCurta($v['vencimento_gasto']) ?></span>
          </div>
          <strong class="venc-item__valor"><?= dinheiro((float)$v['valor_gastos']) ?></strong>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="sec-header" style="margin-top:8px;">
        <h3>Últimos lançamentos</h3>
        <a href="gastos.php">Ver tudo</a>
      </div>
      <div class="card" style="margin:0 16px 6px;">
        <?php if ($ultimosGastos): ?>
          <?php foreach ($ultimosGastos as $i => $g): ?>
          <div class="gasto-row <?= $i < count($ultimosGastos)-1 ? 'gasto-row--sep' : '' ?> gasto-clickable"
               data-id="<?= (int)$g['id_gasto'] ?>"
               data-desc="<?= htmlspecialchars($g['descricao_gasto'], ENT_QUOTES, 'UTF-8') ?>"
               data-valor="<?= htmlspecialchars((string)$g['valor_gastos'], ENT_QUOTES, 'UTF-8') ?>"
               data-data="<?= htmlspecialchars($g['data_gasto'], ENT_QUOTES, 'UTF-8') ?>"
               data-venc="<?= htmlspecialchars($g['vencimento_gasto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               data-status="<?= $g['status'] ?>"
               data-id-categoria="<?= (int)($g['id_categoria'] ?? 0) ?>"
               data-nome-categoria="<?= htmlspecialchars($g['nome_categoria'], ENT_QUOTES, 'UTF-8') ?>"
               data-obs="<?= htmlspecialchars($g['observacoes'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="avatar avatar-sm" style="background:<?= htmlspecialchars($g['cor_categoria'], ENT_QUOTES, 'UTF-8') ?>;">
              <?= mb_strtoupper(mb_substr($g['nome_categoria'], 0, 1, 'UTF-8'), 'UTF-8') ?>
            </div>
            <div class="gasto-row__info">
              <strong><?= htmlspecialchars($g['descricao_gasto'], ENT_QUOTES, 'UTF-8') ?></strong>
              <span><?= htmlspecialchars($g['nome_categoria'], ENT_QUOTES, 'UTF-8') ?> · <?= dataCurta($g['data_gasto']) ?></span>
            </div>
            <div class="gasto-row__right">
              <strong style="color:var(--red);">-<?= dinheiro((float)$g['valor_gastos']) ?></strong>
              <span class="badge <?= $g['status']==='pago' ? 'badge-pago' : 'badge-pendente' ?>"><?= $g['status']==='pago' ? 'Pago' : 'Pendente' ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty">
            <svg viewBox="0 0 24 24"><path d="M19.5 3.5 18 2l-1.5 1.5L15 2l-1.5 1.5L12 2l-1.5 1.5L9 2 7.5 3.5 6 2 4.5 3.5 3 2v20l1.5-1.5L6 22l1.5-1.5L9 22l1.5-1.5L12 22l1.5-1.5L15 22l1.5-1.5L18 22l1.5-1.5L21 22V2l-1.5 1.5z"/></svg>
            <p>Nenhum gasto registrado ainda.</p>
          </div>
        <?php endif; ?>
      </div>
    </div><!-- /.dash-col-right -->

  </div><!-- /.dash-grid -->

</main>

<a href="gastos.php#novo" class="fab" aria-label="Adicionar gasto">
  <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
</a>

<!-- Detail Sheet -->
<?php require_once '../includes/gasto_detail_sheet.php'; ?>
<script>window.CATEGORIAS = <?= json_encode($catSheet, JSON_HEX_QUOT | JSON_HEX_TAG) ?>;</script>
<script src="../js/gasto-detail.js"></script>

<?php if ($resumoCategorias): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
  var labels = <?= json_encode(array_column($resumoCategorias, 'nome_categoria'), JSON_HEX_TAG) ?>;
  var dados  = <?= json_encode(array_map('floatval', array_column($resumoCategorias, 'total_gasto'))) ?>;
  var cores  = <?= json_encode(array_column($resumoCategorias, 'cor'), JSON_HEX_TAG) ?>;
  var isDark = document.documentElement.classList.contains('dark');
  var borda  = isDark ? '#1A2E22' : '#FFFFFF';

  new Chart(document.getElementById('chartCategorias'), {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: dados,
        backgroundColor: cores,
        borderColor: borda,
        borderWidth: 3,
        hoverOffset: 8
      }]
    },
    options: {
      responsive: false,
      cutout: '62%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function (ctx) {
              var v = ctx.parsed;
              return ' R$ ' + v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
          }
        }
      }
    }
  });
})();
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
