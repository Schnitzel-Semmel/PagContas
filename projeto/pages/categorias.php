<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/connect.php';

$tituloPagina = 'Categorias | PagContas';
$tituloHeader = 'Categorias';
$cssPagina    = 'categorias.css';
$idUsuario    = (int) $_SESSION['id_usuario'];
$statusMsg    = $_GET['status'] ?? '';
$inicioMes    = date('Y-m-01');
$fimMes       = date('Y-m-t');

$stmt = $conn->prepare("
    SELECT c.id_categoria, c.nome_categoria, c.cor, c.meta_mensal, c.is_system,
           COALESCE(SUM(g.valor_gastos),0) AS total_gasto
    FROM categoria c
    LEFT JOIN gasto g ON g.id_categoria=c.id_categoria AND g.id_usuario=:ug AND g.deletado_quando IS NULL AND g.data_gasto BETWEEN :i AND :f
    WHERE c.is_active=1 AND (c.id_usuario IS NULL OR c.id_usuario=:uc)
    GROUP BY c.id_categoria
    ORDER BY total_gasto DESC, c.nome_categoria ASC
");
$stmt->execute([':ug'=>$idUsuario,':uc'=>$idUsuario,':i'=>$inicioMes,':f'=>$fimMes]);
$categorias = $stmt->fetchAll();

$mensagens = ['salvo'=>'Categoria criada.','apagado'=>'Categoria apagada.','meta'=>'Meta atualizada.','erro'=>'Não foi possível concluir.'];

$catSheet = array_map(fn($c) => [
    'id_categoria'   => $c['id_categoria'],
    'nome_categoria' => $c['nome_categoria'],
    'cor'            => $c['cor'],
], $categorias);

require_once '../includes/header.php';
?>

<main class="pagina pagina-categorias">

  <?php if ($statusMsg): ?>
    <div class="aviso-categorias aviso-categorias--<?= $statusMsg === 'erro' ? 'erro' : 'sucesso'; ?>">
      <?= htmlspecialchars($mensagens[$statusMsg] ?? '', ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>

  <!-- Resumo -->
  <div class="cat-resumo">
    <div class="cat-resumo__info">
      <strong><?= count($categorias) ?></strong>
      <span>categorias ativas</span>
    </div>
    <button class="btn btn-primary btn-sm" id="fab" aria-label="Nova categoria">
      + Nova categoria
    </button>
  </div>

  <!-- Lista de categorias -->
  <?php if ($categorias): ?>
    <?php foreach ($categorias as $i => $cat):
      $meta  = (float)($cat['meta_mensal'] ?? 0);
      $gasto = (float)$cat['total_gasto'];
      $pct   = $meta > 0 ? min(100, round(($gasto/$meta)*100)) : 0;
      $cls   = $pct >= 100 ? 'danger' : ($pct >= 75 ? 'warning' : '');
    ?>
  <div class="card" style="margin:0 16px 8px;">
    <div class="cat-item" id="cat-<?= (int)$cat['id_categoria'] ?>"
         data-cat-id="<?= (int)$cat['id_categoria'] ?>"
         data-cat-nome="<?= htmlspecialchars($cat['nome_categoria'], ENT_QUOTES, 'UTF-8') ?>"
         data-cat-cor="<?= htmlspecialchars($cat['cor'], ENT_QUOTES, 'UTF-8') ?>">
      <div class="cat-item__row">
        <div class="avatar avatar-sm" style="background:<?= htmlspecialchars($cat['cor'], ENT_QUOTES, 'UTF-8') ?>;">
          <?= mb_strtoupper(mb_substr($cat['nome_categoria'], 0, 1, 'UTF-8'), 'UTF-8') ?>
        </div>
        <div class="cat-item__info">
          <strong><?= htmlspecialchars($cat['nome_categoria'], ENT_QUOTES, 'UTF-8') ?></strong>
          <span>
            <?= $cat['is_system'] ? 'Padrão' : 'Personalizada' ?>
            <?php if ($meta > 0): ?>
              · R$ <?= number_format($gasto, 2, ',', '.') ?> / R$ <?= number_format($meta, 2, ',', '.') ?>
            <?php elseif ($gasto > 0): ?>
              · R$ <?= number_format($gasto, 2, ',', '.') ?> este mês
            <?php endif; ?>
          </span>
        </div>
        <button class="btn btn-icon cat-item__toggle" data-id="<?= (int)$cat['id_categoria'] ?>" aria-label="Opções">
          <svg viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
        </button>
      </div>

      <?php if ($meta > 0): ?>
      <div class="cat-item__progresso">
        <div class="progress">
          <div class="progress__fill<?= $cls ? ' progress__fill--'.$cls : '' ?>" style="width:<?= $pct ?>%;background:<?= htmlspecialchars($cat['cor'], ENT_QUOTES, 'UTF-8') ?>;"></div>
        </div>
        <span><?= $pct ?>%</span>
      </div>
      <?php endif; ?>
    </div>

    <!-- Ações expansíveis -->
    <div class="cat-acoes" id="acoes-cat-<?= (int)$cat['id_categoria'] ?>">
      <form action="../actions/categoria_action.php" method="post" class="cat-acoes__meta">
        <input type="hidden" name="acao" value="editar_meta">
        <input type="hidden" name="id_categoria" value="<?= (int)$cat['id_categoria'] ?>">
        <div class="campo" style="flex:1;">
          <label for="meta_<?= (int)$cat['id_categoria'] ?>">Meta mensal (R$)</label>
          <input type="text" id="meta_<?= (int)$cat['id_categoria'] ?>" name="meta_mensal" inputmode="decimal" placeholder="0,00" value="<?= $cat['meta_mensal'] !== null ? number_format((float)$cat['meta_mensal'],2,',','.') : '' ?>">
        </div>
        <button type="submit" class="btn btn-secondary btn-sm" style="align-self:flex-end;">Salvar</button>
      </form>

      <?php if (!$cat['is_system']): ?>
      <form action="../actions/categoria_action.php" method="post" onsubmit="return confirm('Apagar esta categoria?');" style="margin:0;">
        <input type="hidden" name="acao" value="apagar">
        <input type="hidden" name="id_categoria" value="<?= (int)$cat['id_categoria'] ?>">
        <button type="submit" class="btn btn-danger btn-sm">Apagar</button>
      </form>
      <?php endif; ?>
    </div>

    <!-- Lista de gastos inline (expandida ao clicar na linha) -->
    <div class="cat-gastos" id="gastos-cat-<?= (int)$cat['id_categoria'] ?>"></div>
  </div>
    <?php endforeach; ?>
  <?php else: ?>
  <div class="card" style="margin:16px;">
    <div class="empty">
      <svg viewBox="0 0 24 24"><path d="M3 3h8v8H3V3zm0 10h8v8H3v-8zm10-10h8v8h-8V3zm0 10h8v8h-8v-8z"/></svg>
      <p>Nenhuma categoria encontrada.</p>
    </div>
  </div>
  <?php endif; ?>

</main>

<!-- Backdrop -->
<div class="sheet-bg" id="sheetBg"></div>

<!-- Bottom Sheet: Nova Categoria -->
<div class="bottom-sheet" id="sheetAdd" role="dialog" aria-label="Nova categoria">
  <div class="sheet-handle"></div>
  <div class="sheet-header">
    <h2>Nova categoria</h2>
    <button class="sheet-close" aria-label="Fechar">✕</button>
  </div>
  <div class="sheet-body">
    <form action="../actions/categoria_action.php" method="post" class="form-grid">
      <input type="hidden" name="acao" value="criar">

      <div class="campo">
        <label for="nome_categoria">Nome</label>
        <input type="text" id="nome_categoria" name="nome_categoria" placeholder="Ex: Viagem, estudos, pets" required>
      </div>

      <div class="form-row">
        <div class="campo">
          <label for="cor">Cor</label>
          <input type="color" id="cor" name="cor" value="#128C7E">
        </div>
        <div class="campo">
          <label for="meta_mensal">Meta mensal (R$)</label>
          <input type="text" id="meta_mensal" name="meta_mensal" placeholder="0,00" inputmode="decimal">
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block" style="margin-top:4px;">Criar categoria</button>
    </form>
  </div>
</div>

<script>
(function () {

  /* ── Toggle ações (botão ⋮) ── */
  document.querySelectorAll('.cat-item__toggle').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var id    = btn.dataset.id;
      var acoes = document.getElementById('acoes-cat-' + id);
      if (acoes) acoes.classList.toggle('cat-acoes--open');
    });
  });

  /* ── Clique no card → expande/colapsa gastos inline ── */
  document.querySelectorAll('.cat-item[data-cat-id]').forEach(function (item) {
    item.addEventListener('click', function () {
      var id    = item.dataset.catId;
      var panel = document.getElementById('gastos-cat-' + id);
      if (!panel) return;

      var abrindo = !panel.classList.contains('cat-gastos--open');

      /* Fecha todos os outros painéis abertos */
      document.querySelectorAll('.cat-gastos--open').forEach(function (el) {
        if (el === panel) return;
        el.classList.remove('cat-gastos--open');
        var otherId  = el.id.replace('gastos-cat-', '');
        var otherRow = document.querySelector('#cat-' + otherId + ' .cat-item__row');
        if (otherRow) otherRow.classList.remove('cat-item__row--open');
      });

      panel.classList.toggle('cat-gastos--open', abrindo);
      var thisRow = item.querySelector('.cat-item__row');
      if (thisRow) thisRow.classList.toggle('cat-item__row--open', abrindo);

      if (!abrindo || panel.dataset.loaded) return;

      carregarPainel(panel, id);
    });
  });

  function carregarPainel(panel, catId) {
    panel.dataset.loaded = '1';
    panel.innerHTML = '<p class="cat-gastos-loading">Carregando...</p>';

    var fd = new FormData();
    fd.append('acao', 'gastos_categoria');
    fd.append('id_categoria', catId);

    fetch('../actions/categoria_ajax.php', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) { renderCatGastos(panel, data); })
      .catch(function () {
        panel.innerHTML = '<p class="cat-gastos-loading">Erro ao carregar.</p>';
        delete panel.dataset.loaded;
      });
  }

  /* ── Botão "+" dentro do painel (delegação) ── */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.cat-gastos-add-btn');
    if (!btn) return;
    document.getElementById('ag_categoria').value = btn.dataset.catId;
    abrirSheet('sheetAddGasto');
  });

  /* ── Submit do form de novo gasto ── */
  document.getElementById('formAddGasto').addEventListener('submit', function (e) {
    e.preventDefault();
    var submitBtn = this.querySelector('[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Salvando…';

    var catId = document.getElementById('ag_categoria').value;
    var fd = new FormData(this);
    fd.append('acao', 'criar');

    fetch('../actions/gasto_ajax.php', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.ok) {
          fecharSheets();
          e.target.reset();
          document.getElementById('ag_data').value = new Date().toISOString().slice(0, 10);
          if (typeof showToast === 'function') showToast('Gasto adicionado');
          var panel = document.getElementById('gastos-cat-' + catId);
          if (panel && panel.classList.contains('cat-gastos--open')) {
            delete panel.dataset.loaded;
            carregarPainel(panel, catId);
          }
        } else {
          if (typeof showToast === 'function') showToast(data.msg || 'Erro ao salvar', 'error');
        }
      })
      .catch(function () {
        if (typeof showToast === 'function') showToast('Erro de conexão', 'error');
      })
      .finally(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Salvar gasto';
      });
  });

  function renderCatGastos(panel, data) {
    if (!data.ok || !data.gastos) {
      panel.innerHTML = '<p class="cat-gastos-loading">Erro ao carregar.</p>';
      delete panel.dataset.loaded;
      return;
    }
    if (!data.gastos.length) {
      var catId = panel.id.replace('gastos-cat-', '');
      panel.innerHTML = '<div class="cat-gastos-empty">' +
                        '<span>Nenhum gasto este mês.</span>' +
                        '<button class="btn btn-primary btn-sm cat-gastos-add-btn" data-cat-id="' + catId + '">+ Novo</button>' +
                        '</div>';
      return;
    }

    var fmt = function (v) {
      return 'R$ ' + parseFloat(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };
    var fmtData = function (d) {
      if (!d) return '';
      var p = d.split('-');
      return p[2] + '/' + p[1];
    };
    var esc = function (s) {
      return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    };

    var catId = panel.id.replace('gastos-cat-', '');
    var html = '<div class="cat-gastos-total">' +
               '<span>Total este mês</span>' +
               '<strong>' + fmt(data.total) + '</strong>' +
               '<button class="btn btn-primary btn-sm cat-gastos-add-btn" data-cat-id="' + catId + '">+ Novo</button>' +
               '</div>';

    data.gastos.forEach(function (g, i) {
      var sep = i < data.gastos.length - 1 ? ' cat-gasto-row--sep' : '';
      var badgeCls = g.status === 'pago' ? 'badge-pago' : 'badge-pendente';
      var badgeTxt = g.status === 'pago' ? 'Pago' : 'Pendente';
      html += '<div class="cat-gasto-row gasto-clickable' + sep + '"' +
              ' style="cursor:pointer;"' +
              ' data-id="'            + g.id_gasto + '"' +
              ' data-desc="'          + esc(g.descricao_gasto) + '"' +
              ' data-valor="'         + g.valor_gastos + '"' +
              ' data-data="'          + (g.data_gasto || '') + '"' +
              ' data-venc="'          + (g.vencimento_gasto || '') + '"' +
              ' data-status="'        + g.status + '"' +
              ' data-id-categoria="'  + (g.id_categoria || '') + '"' +
              ' data-nome-categoria="'+ esc(data.nome) + '"' +
              ' data-obs="'           + esc(g.observacoes || '') + '">' +
              '<div class="cat-gasto-row__info">' +
              '<strong>' + esc(g.descricao_gasto) + '</strong>' +
              '<span>' + fmtData(g.data_gasto) + '</span>' +
              '</div>' +
              '<div class="cat-gasto-row__right">' +
              '<strong style="color:var(--red);">-' + fmt(g.valor_gastos) + '</strong>' +
              '<span class="badge ' + badgeCls + '">' + badgeTxt + '</span>' +
              '</div>' +
              '</div>';
    });

    panel.innerHTML = html;
  }

})();
</script>

<!-- Bottom Sheet: Novo Gasto (via categoria) -->
<div class="bottom-sheet" id="sheetAddGasto" role="dialog" aria-label="Novo gasto">
  <div class="sheet-handle"></div>
  <div class="sheet-header">
    <h2>Novo gasto</h2>
    <button class="sheet-close" aria-label="Fechar">✕</button>
  </div>
  <div class="sheet-body">
    <form id="formAddGasto" class="form-grid" autocomplete="off">
      <div class="campo">
        <label for="ag_descricao">Descrição</label>
        <input type="text" id="ag_descricao" name="descricao_gasto" placeholder="Ex: Mercado, luz, transporte" required>
      </div>
      <div class="form-row">
        <div class="campo">
          <label for="ag_valor">Valor</label>
          <input type="text" id="ag_valor" name="valor_gastos" placeholder="0,00" inputmode="decimal" required>
        </div>
        <div class="campo">
          <label for="ag_data">Data</label>
          <input type="date" id="ag_data" name="data_gasto" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>
      <div class="form-row">
        <div class="campo">
          <label for="ag_categoria">Categoria</label>
          <select id="ag_categoria" name="id_categoria">
            <option value="">Sem categoria</option>
            <?php foreach ($categorias as $c): ?>
              <option value="<?= (int)$c['id_categoria'] ?>"><?= htmlspecialchars($c['nome_categoria'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="campo">
          <label for="ag_status">Status</label>
          <select id="ag_status" name="status">
            <option value="pendente">Pendente</option>
            <option value="pago">Pago</option>
          </select>
        </div>
      </div>
      <div class="campo">
        <label for="ag_vencimento">Vencimento <span style="font-weight:400;opacity:.6;">(opcional)</span></label>
        <input type="date" id="ag_vencimento" name="vencimento_gasto">
      </div>
      <button type="submit" class="btn btn-primary btn-block" style="margin-top:4px;">Salvar gasto</button>
    </form>
  </div>
</div>

<?php require_once '../includes/gasto_detail_sheet.php'; ?>
<script>window.CATEGORIAS = <?= json_encode($catSheet, JSON_HEX_QUOT | JSON_HEX_TAG) ?>;</script>
<script src="../js/gasto-detail.js"></script>

<?php require_once '../includes/footer.php'; ?>
