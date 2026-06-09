<div class="conta-card stripe-item stripe-<?= $conta['status'] === 'pago' ? 'pago' : ($conta['vencimento_gasto'] < date('Y-m-d') ? 'vencida' : 'pendente') ?> gasto-clickable"
     data-id="<?= (int)$conta['id_gasto'] ?>"
     data-desc="<?= htmlspecialchars($conta['descricao_gasto'], ENT_QUOTES, 'UTF-8') ?>"
     data-valor="<?= htmlspecialchars((string)$conta['valor_gastos'], ENT_QUOTES, 'UTF-8') ?>"
     data-data="<?= htmlspecialchars($conta['data_gasto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
     data-venc="<?= htmlspecialchars($conta['vencimento_gasto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
     data-status="<?= $conta['status'] ?>"
     data-id-categoria="<?= (int)($conta['id_categoria'] ?? 0) ?>"
     data-nome-categoria="<?= htmlspecialchars($conta['nome_categoria'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
     data-obs="<?= htmlspecialchars($conta['observacoes'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

  <div class="conta-card__info">
    <strong><?= htmlspecialchars($conta['descricao_gasto'], ENT_QUOTES, 'UTF-8') ?></strong>
    <span><?= htmlspecialchars($conta['nome_categoria'], ENT_QUOTES, 'UTF-8') ?> · Vence <?= dataConta($conta['vencimento_gasto']) ?></span>
  </div>

  <div class="conta-card__right">
    <strong><?= dinheiroConta((float)$conta['valor_gastos']) ?></strong>
    <div class="conta-card__acoes">
      <?php if ($conta['status'] !== 'pago'): ?>
        <form action="../actions/gasto_action.php" method="post">
          <input type="hidden" name="acao"    value="alternar_status">
          <input type="hidden" name="origem"  value="contas">
          <input type="hidden" name="id_gasto" value="<?= (int)$conta['id_gasto'] ?>">
          <button type="submit" class="btn btn-secondary btn-sm">Pagar</button>
        </form>
      <?php else: ?>
        <span class="badge badge-pago">Pago</span>
      <?php endif; ?>
      <form action="../actions/gasto_action.php" method="post" onsubmit="return confirm('Apagar esta conta?');">
        <input type="hidden" name="acao"    value="apagar">
        <input type="hidden" name="origem"  value="contas">
        <input type="hidden" name="id_gasto" value="<?= (int)$conta['id_gasto'] ?>">
        <button type="submit" class="btn btn-icon">
          <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
        </button>
      </form>
    </div>
  </div>
</div>
