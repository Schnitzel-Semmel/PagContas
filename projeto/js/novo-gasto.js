/* ── Novo Gasto Form ─────────────────────────────────── */
(function () {

  function ativarChip(form, chip) {
    form.querySelectorAll('.ng-cat-chip').forEach(function (c) {
      c.classList.remove('ng-cat-chip--active');
      c.style.removeProperty('border-color');
      c.style.removeProperty('background');
    });
    chip.classList.add('ng-cat-chip--active');
    var cor = chip.dataset.cor;
    if (cor) {
      chip.style.borderColor = cor;
      chip.style.background  = cor + '22';
    }
  }

  function initForm(form) {
    /* Status toggle */
    var statusInput = form.querySelector('[name="status"]');
    form.querySelectorAll('.ng-status-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        form.querySelectorAll('.ng-status-btn').forEach(function (b) {
          b.classList.remove('ng-status-btn--active');
        });
        btn.classList.add('ng-status-btn--active');
        if (statusInput) statusInput.value = btn.dataset.val;
      });
    });

    /* Category chips */
    var catInput = form.querySelector('[name="id_categoria"]');
    form.querySelectorAll('.ng-cat-chip').forEach(function (chip) {
      chip.addEventListener('click', function () {
        ativarChip(form, chip);
        if (catInput) catInput.value = chip.dataset.id;
      });
    });

    /* Hoje button */
    var todayBtn = form.querySelector('.ng-today-btn');
    if (todayBtn) {
      todayBtn.addEventListener('click', function () {
        var dataInput = form.querySelector('[name="data_gasto"]');
        if (dataInput) dataInput.value = new Date().toISOString().slice(0, 10);
      });
    }

    /* Mais opções */
    var maisBtn  = form.querySelector('.ng-mais-btn');
    var maisBody = form.querySelector('.ng-mais-body');
    if (maisBtn && maisBody) {
      maisBtn.addEventListener('click', function () {
        var aberto = maisBody.classList.toggle('ng-mais-body--open');
        maisBtn.classList.toggle('ng-mais-btn--open', aberto);
      });
    }
  }

  /* Pre-selects a category chip; falls back to "Sem categoria" */
  window.ngSelectCategory = function (form, catId) {
    var chip = form.querySelector('.ng-cat-chip[data-id="' + catId + '"]');
    if (!chip) chip = form.querySelector('.ng-cat-chip[data-id=""]');
    if (!chip) return;
    ativarChip(form, chip);
    var catInput = form.querySelector('[name="id_categoria"]');
    if (catInput) catInput.value = chip.dataset.id || '';
    try { chip.scrollIntoView({ inline: 'nearest', block: 'nearest' }); } catch (_) {}
  };

  /* Resets form to initial state */
  window.ngResetForm = function (form) {
    form.reset();
    /* Status → pendente */
    form.querySelectorAll('.ng-status-btn').forEach(function (b) {
      b.classList.remove('ng-status-btn--active');
    });
    var pBtn = form.querySelector('.ng-status-btn[data-val="pendente"]');
    if (pBtn) pBtn.classList.add('ng-status-btn--active');
    var statusInput = form.querySelector('[name="status"]');
    if (statusInput) statusInput.value = 'pendente';
    /* Category → sem categoria */
    form.querySelectorAll('.ng-cat-chip').forEach(function (c) {
      c.classList.remove('ng-cat-chip--active');
      c.style.removeProperty('border-color');
      c.style.removeProperty('background');
    });
    var semCat = form.querySelector('.ng-cat-chip[data-id=""]');
    if (semCat) semCat.classList.add('ng-cat-chip--active');
    var catInput = form.querySelector('[name="id_categoria"]');
    if (catInput) catInput.value = '';
    /* Date → today */
    var dataInput = form.querySelector('[name="data_gasto"]');
    if (dataInput) dataInput.value = new Date().toISOString().slice(0, 10);
    /* Close mais opções */
    var maisBody = form.querySelector('.ng-mais-body');
    var maisBtn  = form.querySelector('.ng-mais-btn');
    if (maisBody) maisBody.classList.remove('ng-mais-body--open');
    if (maisBtn)  maisBtn.classList.remove('ng-mais-btn--open');
  };

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.novo-gasto-form').forEach(initForm);
  });

})();
