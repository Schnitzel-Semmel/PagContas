<!-- ── Detail Sheet Backdrop ── -->
<div class="sheet-bg" id="sheetDetailBg"></div>

<!-- ── Bottom Sheet: Detalhes do Gasto ── -->
<div class="bottom-sheet" id="sheetDetail" role="dialog" aria-label="Detalhes do gasto">
  <div class="sheet-handle"></div>
  <div class="sheet-header">
    <h2>Detalhes do gasto</h2>
    <button class="sheet-close" id="sheetDetailClose" aria-label="Fechar">✕</button>
  </div>
  <div class="sheet-body">
    <form id="formEditGasto" class="detail-form" autocomplete="off">
      <input type="hidden" id="detailId"   name="id_gasto">
      <input type="hidden"                  name="acao"     value="editar">

      <!-- Descrição -->
      <div class="detail-field">
        <label>Descrição</label>
        <div class="detail-input-wrap">
          <input type="text" id="detailDesc" name="descricao_gasto" placeholder="Descrição do gasto" required>
          <svg class="detail-edit-icon" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
        </div>
      </div>

      <div class="detail-sep"></div>

      <!-- Valor + Data -->
      <div class="detail-row">
        <div class="detail-field">
          <label>Valor</label>
          <div class="detail-input-wrap">
            <span class="detail-prefix">R$</span>
            <input type="text" id="detailValor" name="valor_gastos" inputmode="decimal" placeholder="0,00" required>
          </div>
        </div>
        <div class="detail-field">
          <label>Data</label>
          <input type="date" id="detailData" name="data_gasto" required>
        </div>
      </div>

      <div class="detail-sep"></div>

      <!-- Categoria + Status -->
      <div class="detail-row">
        <div class="detail-field">
          <label>Categoria</label>
          <select id="detailCategoria" name="id_categoria">
            <option value="">Sem categoria</option>
          </select>
        </div>
        <div class="detail-field">
          <label>Status</label>
          <select id="detailStatus" name="status">
            <option value="pendente">Pendente</option>
            <option value="pago">Pago</option>
          </select>
        </div>
      </div>

      <div class="detail-sep"></div>

      <!-- Vencimento -->
      <div class="detail-field">
        <label>Vencimento <span style="font-weight:400;opacity:.6;">(opcional)</span></label>
        <input type="date" id="detailVenc" name="vencimento_gasto">
      </div>

      <div class="detail-sep"></div>

      <!-- Observações -->
      <div class="detail-field">
        <label>Observações</label>
        <textarea id="detailObs" name="observacoes" placeholder="Adicione uma anotação..." rows="3"></textarea>
      </div>
    </form>
  </div>

  <!-- Rodapé fixo com ações -->
  <div class="sheet-footer">
    <button type="button" id="btnApagarDetail" class="btn-delete-txt">
      <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
      Excluir gasto
    </button>
    <button type="submit" form="formEditGasto" class="btn btn-primary btn-block">
      Salvar alterações
    </button>
  </div>
</div>
