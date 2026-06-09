/* ── TEMA: aplicado antes do render pelo header.php ─────── */
/* (inline script em header.php lida com o init rápido) */

/* ── TOAST ──────────────────────────────────────────────── */
function showToast(msg, tipo) {
  var el = document.getElementById('appToast');
  if (!el) {
    el = document.createElement('div');
    el.id = 'appToast';
    el.className = 'toast';
    document.body.appendChild(el);
  }
  el.textContent = msg;
  el.className = 'toast' + (tipo === 'error' ? ' toast--error' : '');
  /* force reflow */
  void el.offsetWidth;
  el.classList.add('toast--visible');
  clearTimeout(el._timer);
  el._timer = setTimeout(function () {
    el.classList.remove('toast--visible');
  }, 3000);
}

/* ── FORMATO DE MOEDA / DATA ────────────────────────────── */
function formatCurrency(value) {
  var v = parseFloat(value) || 0;
  var pref = localStorage.getItem('currency') || 'BRL';
  if (pref === 'USD') return '$ ' + v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  if (pref === 'EUR') return '€ ' + v.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  return 'R$ ' + v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function formatDate(dateStr) {
  if (!dateStr) return '';
  var parts = String(dateStr).split('-');
  if (parts.length !== 3) return dateStr;
  var y = parts[0], m = parts[1], d = parts[2];
  var fmt = localStorage.getItem('dateFormat') || 'DD/MM/YYYY';
  if (fmt === 'MM/DD/YYYY') return m + '/' + d + '/' + y;
  if (fmt === 'YYYY-MM-DD') return y + '-' + m + '-' + d;
  return d + '/' + m + '/' + y;
}

/* ── BOTTOM SHEET ───────────────────────────────────────── */
function abrirSheet(id) {
  var bg    = document.getElementById('sheetBg');
  var sheet = document.getElementById(id);
  if (!bg || !sheet) return;
  bg.classList.add('sheet-bg--open');
  sheet.classList.add('bottom-sheet--open');
  document.body.style.overflow = 'hidden';
}
function fecharSheets() {
  document.querySelectorAll('.bottom-sheet:not(#sheetDetail)').forEach(function (s) {
    s.classList.remove('bottom-sheet--open');
  });
  var bg = document.getElementById('sheetBg');
  if (bg) bg.classList.remove('sheet-bg--open');
  document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', function () {

  /* ── Dropdown do avatar (top-bar) ── */
  var avatarBtn = document.getElementById('topBarAvatar');
  var avatarMenu = document.getElementById('topBarMenu');
  if (avatarBtn && avatarMenu) {
    avatarBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      var aberto = !avatarMenu.hidden;
      avatarMenu.hidden = aberto;
      avatarBtn.setAttribute('aria-expanded', String(!aberto));
    });
    document.addEventListener('click', function () {
      if (!avatarMenu.hidden) {
        avatarMenu.hidden = true;
        avatarBtn.setAttribute('aria-expanded', 'false');
      }
    });
    avatarMenu.addEventListener('click', function (e) { e.stopPropagation(); });
  }

  /* Backdrop e botões de fechar (sheet principal) */
  var bg = document.getElementById('sheetBg');
  if (bg) bg.addEventListener('click', fecharSheets);
  document.querySelectorAll('.sheet-close').forEach(function (b) {
    if (b.id !== 'sheetDetailClose') b.addEventListener('click', fecharSheets);
  });

  /* FAB abre sheet principal */
  var fab = document.getElementById('fab');
  if (fab) fab.addEventListener('click', function () { abrirSheet('sheetAdd'); });

  /* Abre sheet automaticamente ao chegar via FAB de outra página (#novo) */
  if (window.location.hash === '#novo' && document.getElementById('sheetAdd')) {
    history.replaceState(null, '', window.location.pathname);
    abrirSheet('sheetAdd');
  }

  /* ── Chips de filtro ── */
  document.querySelectorAll('[data-filtro-grupo]').forEach(function (grupo) {
    var campo = grupo.dataset.filtroGrupo;
    grupo.querySelectorAll('.chip').forEach(function (chip) {
      chip.addEventListener('click', function () {
        grupo.querySelectorAll('.chip').forEach(function (c) { c.classList.remove('chip--ativo'); });
        chip.classList.add('chip--ativo');
        var val = chip.dataset.filtro;

        document.querySelectorAll('[data-' + campo + ']').forEach(function (item) {
          var vis = val === 'todos' || item.dataset[campo] === val;
          item.style.display = vis ? '' : 'none';
        });

        document.querySelectorAll('.grupo-data').forEach(function (g) {
          var temVis = Array.from(g.querySelectorAll('[data-' + campo + ']')).some(function (i) {
            return i.style.display !== 'none';
          });
          g.style.display = temVis ? '' : 'none';
        });
      });
    });
  });

  /* ── Toggle ações das categorias ── */
  document.querySelectorAll('.cat-item__toggle').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var id    = btn.dataset.id;
      var acoes = document.getElementById('acoes-cat-' + id);
      if (acoes) acoes.classList.toggle('cat-acoes--open');
    });
  });

  /* ── Auto-dismiss avisos PHP ── */
  document.querySelectorAll('.aviso-gastos,.aviso-contas,.aviso-categorias,.aviso-configuracoes')
    .forEach(function (el) {
      setTimeout(function () {
        el.style.transition = 'opacity .4s';
        el.style.opacity = '0';
        setTimeout(function () { el.remove(); }, 420);
      }, 4000);
    });

  /* ── Aplica foto de perfil do localStorage no avatar do header ── */
  var prof = {};
  try { prof = JSON.parse(localStorage.getItem('profile') || '{}'); } catch (_) {}
  if (prof.foto) {
    var av = document.querySelector('.top-bar .top-bar__icon div');
    if (av) {
      av.style.backgroundImage = 'url(' + prof.foto + ')';
      av.style.backgroundSize  = 'cover';
      av.style.backgroundPosition = 'center';
      av.textContent = '';
    }
  }
});
