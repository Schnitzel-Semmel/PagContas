/* ── Gasto Detail Sheet ────────────────────────────── */
(function () {
  const bg      = document.getElementById('sheetDetailBg');
  const sheet   = document.getElementById('sheetDetail');
  if (!bg || !sheet) return;

  const closeBtn = document.getElementById('sheetDetailClose');
  const form     = document.getElementById('formEditGasto');
  const btnDel   = document.getElementById('btnApagarDetail');
  const selCat   = document.getElementById('detailCategoria');
  const fDesc    = document.getElementById('detailDesc');
  const fValor   = document.getElementById('detailValor');
  const fData    = document.getElementById('detailData');
  const fVenc    = document.getElementById('detailVenc');
  const fObs     = document.getElementById('detailObs');
  const fStatus  = document.getElementById('detailStatus');
  const fId      = document.getElementById('detailId');

  let currentItem = null;

  /* Popula o select de categorias a partir da variável global */
  function populateCategorias(selectedId) {
    const cats = window.CATEGORIAS || [];
    while (selCat.options.length > 1) selCat.remove(1);
    cats.forEach(c => {
      const opt = new Option(c.nome_categoria, c.id_categoria);
      selCat.add(opt);
    });
    selCat.value = selectedId || '';
  }

  /* Abre o sheet com os dados do item clicado */
  function openDetail(item) {
    currentItem = item;
    const d = item.dataset;

    populateCategorias(d.idCategoria || '');
    fId.value     = d.id    || '';
    fDesc.value   = d.desc  || '';
    fValor.value  = d.valor ? parseFloat(d.valor).toFixed(2).replace('.', ',') : '';
    fData.value   = d.data  || '';
    fVenc.value   = d.venc  || '';
    fObs.value    = d.obs   || '';
    fStatus.value = d.status || 'pendente';

    bg.classList.add('sheet-bg--open');
    sheet.classList.add('bottom-sheet--open');
    document.body.style.overflow = 'hidden';
  }

  function closeDetail() {
    bg.classList.remove('sheet-bg--open');
    sheet.classList.remove('bottom-sheet--open');
    document.body.style.overflow = '';
    currentItem = null;
  }

  bg.addEventListener('click', closeDetail);
  closeBtn.addEventListener('click', closeDetail);

  /* Atualiza o item no DOM após salvar */
  function fmtVal(v) {
    return 'R$ ' + parseFloat(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function fmtDate(ymd) {
    if (!ymd) return '';
    const [y, m, d] = ymd.split('-');
    return d + '/' + m + '/' + y;
  }

  function updateItemDOM(item, g) {
    /* Descrição */
    const elDesc = item.querySelector('.gasto-item__info strong, .gasto-row__info strong, .conta-card__info strong, .cat-gasto-row__info strong');
    if (elDesc) elDesc.textContent = g.descricao_gasto;

    /* Valor */
    const elVal = item.querySelector('.gasto-item__right strong, .gasto-row__right strong, .conta-card__right strong, .cat-gasto-row__right strong');
    const showMinus = item.classList.contains('gasto-row') || item.classList.contains('cat-gasto-row');
    if (elVal) elVal.textContent = (showMinus ? '-' : '') + fmtVal(g.valor_gastos);

    /* Badge/status */
    const badge = item.querySelector('.badge');
    if (badge) {
      badge.textContent = g.status === 'pago' ? 'Pago' : 'Pendente';
      badge.className   = 'badge ' + (g.status === 'pago' ? 'badge-pago' : 'badge-pendente');
    }

    /* Stripe */
    item.classList.remove('stripe-pago', 'stripe-pendente', 'stripe-vencida');
    item.classList.add('stripe-' + g.status);
    item.dataset.status = g.status;

    /* Avatar */
    const av = item.querySelector('.avatar');
    if (av) {
      av.style.background = g.cor_categoria;
      av.textContent = (g.nome_categoria || 'S')[0].toUpperCase();
    }

    /* Subtítulo (categoria + data) */
    const sub = item.querySelector('.gasto-item__info span, .gasto-row__info span, .conta-card__info span, .cat-gasto-row__info span');
    if (sub) sub.textContent = (g.nome_categoria || 'Sem categoria') + ' · ' + fmtDate(g.data_gasto || g.vencimento_gasto);

    /* Atualiza data-* */
    Object.assign(item.dataset, {
      desc:         g.descricao_gasto,
      valor:        g.valor_gastos,
      data:         g.data_gasto,
      venc:         g.vencimento_gasto || '',
      obs:          g.observacoes || '',
      status:       g.status,
      idCategoria:  g.id_categoria || '',
      nomeCategoria: g.nome_categoria,
    });
  }

  /* ── Salvar edição ── */
  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = form.querySelector('[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Salvando…';

    const fd = new FormData(form);
    /* Converte valor para float string (remove pontos de milhar, troca vírgula por ponto) */
    const valStr = fValor.value.replace(/\./g, '').replace(',', '.');
    fd.set('valor_gastos', valStr);

    try {
      const res  = await fetch('../actions/gasto_ajax.php', { method: 'POST', body: fd });
      const data = await res.json();

      if (data.ok) {
        if (currentItem) updateItemDOM(currentItem, data.gasto);
        closeDetail();
        if (typeof showToast === 'function') showToast('Gasto atualizado com sucesso');
      } else {
        if (typeof showToast === 'function') showToast(data.msg || 'Erro ao salvar', 'error');
      }
    } catch (_) {
      if (typeof showToast === 'function') showToast('Erro de conexão', 'error');
    }

    btn.disabled = false;
    btn.textContent = 'Salvar alterações';
  });

  /* ── Excluir gasto ── */
  btnDel.addEventListener('click', async function () {
    if (!currentItem) return;
    if (!confirm('Apagar este gasto? Esta ação não pode ser desfeita.')) return;

    const fd = new FormData();
    fd.append('acao', 'apagar');
    fd.append('id_gasto', currentItem.dataset.id);

    try {
      const res  = await fetch('../actions/gasto_ajax.php', { method: 'POST', body: fd });
      const data = await res.json();

      if (data.ok) {
        /* Remove item do DOM */
        const grupo    = currentItem.closest('.grupo-data');
        const catPanel = currentItem.closest('.cat-gastos');
        currentItem.remove();
        if (grupo && !grupo.querySelector('.gasto-item, .gasto-row, .conta-card')) grupo.remove();
        if (catPanel && !catPanel.querySelector('.cat-gasto-row')) {
          catPanel.innerHTML = '<p class="cat-gastos-loading">Nenhum gasto este mês.</p>';
        }

        /* Remove card vazio na lista (gastos.php / contas.php) */
        closeDetail();
        if (typeof showToast === 'function') showToast('Gasto excluído');
      } else {
        if (typeof showToast === 'function') showToast('Erro ao excluir', 'error');
      }
    } catch (_) {
      if (typeof showToast === 'function') showToast('Erro de conexão', 'error');
    }
  });

  /* ── Listener delegado — funciona para itens estáticos e dinâmicos ── */
  document.addEventListener('click', function (e) {
    var item = e.target.closest('.gasto-clickable');
    if (!item) return;
    if (e.target.closest('button, a, form, input, select')) return;
    openDetail(item);
  });
})();
