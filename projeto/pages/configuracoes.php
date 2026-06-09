<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/connect.php';

$tituloPagina = 'Configurações | PagContas';
$tituloHeader = 'Configurações';
$cssPagina    = 'configuracoes.css';
$idUsuario    = (int) $_SESSION['id_usuario'];
$statusMsg    = $_GET['status'] ?? '';

$stmt = $conn->prepare("SELECT nome, telefone, alto_contraste FROM usuario WHERE id_usuario=:u LIMIT 1");
$stmt->execute([':u'=>$idUsuario]);
$usuario = $stmt->fetch();

$stmt = $conn->prepare("SELECT tipo_agendamento, intervalo_dias, horario_envio FROM config_relatorios_usuario WHERE id_usuario=:u LIMIT 1");
$stmt->execute([':u'=>$idUsuario]);
$config = $stmt->fetch() ?: ['tipo_agendamento'=>'desativado','intervalo_dias'=>'','horario_envio'=>'09:00:00'];

$nomeUsuario = $usuario['nome'] ?? 'Usuário';

require_once '../includes/header.php';
?>

<main class="pagina pagina-config">

  <?php if ($statusMsg): ?>
    <div class="aviso-configuracoes aviso-configuracoes--<?= $statusMsg === 'erro' ? 'erro' : 'sucesso'; ?>">
      <?= $statusMsg === 'erro' ? 'Não foi possível salvar.' : 'Configurações salvas.'; ?>
    </div>
  <?php endif; ?>

  <!-- ══════════ PERFIL ══════════ -->
  <div class="config-perfil">
    <div class="perfil-avatar-wrap" title="Trocar foto">
      <div class="avatar" id="avatarEl" style="background:var(--green-x);width:56px;height:56px;font-size:1.4rem;">
        <?= mb_strtoupper(mb_substr($nomeUsuario, 0, 1, 'UTF-8'), 'UTF-8') ?>
      </div>
      <div class="perfil-avatar-overlay">
        <svg viewBox="0 0 24 24"><path d="M12 15.2A3.2 3.2 0 1 1 15.2 12 3.2 3.2 0 0 1 12 15.2zm5.33-10.4h-2.12l-1.5-1.6H10.3L8.8 4.8H6.67A2.13 2.13 0 0 0 4.53 6.93v10.67a2.13 2.13 0 0 0 2.14 2.13h10.66a2.13 2.13 0 0 0 2.14-2.13V6.93a2.13 2.13 0 0 0-2.14-2.13z"/></svg>
      </div>
      <input type="file" id="fotoInput" accept="image/*" style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
    </div>
    <div>
      <strong id="perfilNomeDisplay"><?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?></strong>
      <span><?= htmlspecialchars($usuario['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>

  <!-- ══════════ APARÊNCIA ══════════ -->
  <div class="config-sec-title">Aparência</div>
  <div class="card" style="margin:0 16px 16px;padding:0 16px;">
    <div class="toggle-row">
      <div class="toggle-info">
        <strong>Modo escuro</strong>
        <small>Interface em tons escuros</small>
      </div>
      <label class="toggle-switch">
        <input type="checkbox" id="toggleDark">
        <span class="slider"></span>
      </label>
    </div>
    <div class="toggle-row">
      <div class="toggle-info">
        <strong>Alto contraste</strong>
        <small>Melhora a visibilidade dos elementos</small>
      </div>
      <label class="toggle-switch">
        <input type="checkbox" id="chkContraste" name="alto_contraste" form="formConfig"
          <?= !empty($usuario['alto_contraste']) ? 'checked' : '' ?>>
        <span class="slider"></span>
      </label>
    </div>
  </div>

  <!-- ══════════ PERFIL / DADOS PESSOAIS ══════════ -->
  <div class="config-sec-title">Dados pessoais</div>
  <div class="card" style="margin:0 16px 16px;">
    <div class="config-field">
      <div class="campo">
        <label for="inputNome">Nome</label>
        <input type="text" id="inputNome" placeholder="Seu nome"
               value="<?= htmlspecialchars($usuario['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
    </div>
    <div style="height:1px;background:var(--border);margin:0 16px;"></div>
    <div class="config-field">
      <div class="campo">
        <label>WhatsApp vinculado <span style="font-size:.75rem;color:var(--green-dark);font-weight:700;">● Conectado</span></label>
        <input type="text" value="<?= htmlspecialchars($usuario['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly>
      </div>
    </div>
    <div class="config-field" style="padding-top:0;">
      <button type="button" id="btnSalvarPerfil" class="btn btn-primary btn-block">Salvar perfil</button>
    </div>
  </div>

  <!-- ══════════ RELATÓRIO WHATSAPP ══════════ -->
  <form action="../actions/configuracoes_action.php" method="post" id="formConfig">
    <input type="hidden" name="nome" id="hiddenNome" value="<?= htmlspecialchars($usuario['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <div class="config-sec-title">
      <span>Relatório via WhatsApp</span>
      <span class="config-status <?= $config['tipo_agendamento'] !== 'desativado' ? 'config-status--on' : 'config-status--off' ?>">
        <?= $config['tipo_agendamento'] !== 'desativado' ? 'Ativo' : 'Desativado' ?>
      </span>
    </div>
    <div class="card" style="margin:0 16px 16px;">
      <div class="config-field">
        <div class="campo">
          <label for="tipo_agendamento">Tipo de envio</label>
          <select id="tipo_agendamento" name="tipo_agendamento">
            <option value="desativado"    <?= $config['tipo_agendamento']==='desativado'    ? 'selected' : '' ?>>Desativado</option>
            <option value="intervalo"     <?= $config['tipo_agendamento']==='intervalo'     ? 'selected' : '' ?>>Por intervalo</option>
            <option value="personalizado" <?= $config['tipo_agendamento']==='personalizado' ? 'selected' : '' ?>>Personalizado</option>
          </select>
        </div>
      </div>
      <div style="height:1px;background:var(--border);margin:0 16px;"></div>
      <div class="config-field config-row">
        <div class="campo">
          <label for="intervalo_dias">Intervalo (dias)</label>
          <input type="number" id="intervalo_dias" name="intervalo_dias" min="1"
                 value="<?= htmlspecialchars((string)($config['intervalo_dias'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="campo">
          <label for="horario_envio">Horário</label>
          <input type="time" id="horario_envio" name="horario_envio"
                 value="<?= htmlspecialchars(substr((string)$config['horario_envio'], 0, 5), ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>
    </div>

    <div style="padding:0 16px 12px;">
      <button type="submit" class="btn btn-primary btn-block">Salvar configurações</button>
    </div>
  </form>

  <!-- Sair -->
  <div style="padding:0 16px 8px;">
    <a href="../actions/logout_action.php" class="btn btn-danger btn-block">Sair da conta</a>
  </div>

</main>

<script>
(function () {

  /* ── Tema escuro ── */
  var toggleDark = document.getElementById('toggleDark');
  toggleDark.checked = localStorage.getItem('theme') === 'dark';
  toggleDark.addEventListener('change', function () {
    if (toggleDark.checked) {
      document.documentElement.classList.add('dark');
      localStorage.setItem('theme', 'dark');
    } else {
      document.documentElement.classList.remove('dark');
      localStorage.setItem('theme', 'light');
    }
  });

  /* ── Alto contraste ── */
  var chkContr = document.getElementById('chkContraste');
  if (localStorage.getItem('contrast') !== null) {
    chkContr.checked = localStorage.getItem('contrast') === '1';
  }
  document.documentElement.classList.toggle('high-contrast', chkContr.checked);
  if (chkContr.checked) localStorage.setItem('contrast', '1');
  else                  localStorage.removeItem('contrast');

  chkContr.addEventListener('change', function () {
    document.documentElement.classList.toggle('high-contrast', chkContr.checked);
    if (chkContr.checked) localStorage.setItem('contrast', '1');
    else                  localStorage.removeItem('contrast');
  });

  /* ── Perfil ── */
  var inputNome  = document.getElementById('inputNome');
  var hiddenNome = document.getElementById('hiddenNome');
  var fotoInput  = document.getElementById('fotoInput');
  var avatarEl   = document.getElementById('avatarEl');
  var nomeDisp   = document.getElementById('perfilNomeDisplay');

  /* Carrega perfil do localStorage */
  var prof = {};
  try { prof = JSON.parse(localStorage.getItem('profile') || '{}'); } catch(_){}
  if (prof.foto) {
    avatarEl.style.backgroundImage   = 'url(' + prof.foto + ')';
    avatarEl.style.backgroundSize    = 'cover';
    avatarEl.style.backgroundPosition= 'center';
    avatarEl.textContent = '';
  }

  /* Preview da foto antes de salvar */
  fotoInput.addEventListener('change', function () {
    var file = fotoInput.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
      avatarEl.style.backgroundImage   = 'url(' + e.target.result + ')';
      avatarEl.style.backgroundSize    = 'cover';
      avatarEl.style.backgroundPosition= 'center';
      avatarEl.textContent = '';
    };
    reader.readAsDataURL(file);
  });

  document.getElementById('btnSalvarPerfil').addEventListener('click', function () {
    var novoNome = inputNome.value.trim();
    if (!novoNome) { if (typeof showToast==='function') showToast('Nome obrigatório','error'); return; }

    /* Pega foto atual (base64) */
    var fotoBase64 = '';
    if (avatarEl.style.backgroundImage && avatarEl.style.backgroundImage !== 'none') {
      var m = avatarEl.style.backgroundImage.match(/url\(["']?(.+?)["']?\)/);
      if (m) fotoBase64 = m[1];
    }
    var fotoFile = fotoInput.files[0];
    function salvarPerfil(fotoUrl) {
      prof.nome  = novoNome;
      if (fotoUrl) prof.foto = fotoUrl;
      localStorage.setItem('profile', JSON.stringify(prof));
      nomeDisp.textContent = novoNome;
      hiddenNome.value     = novoNome;
      /* Salva nome no servidor também (via submit do formConfig) */
      hiddenNome.value = novoNome;
      /* Atualiza avatar no header */
      var topAv = document.querySelector('.top-bar .top-bar__icon div');
      if (topAv && fotoUrl) {
        topAv.style.backgroundImage   = 'url(' + fotoUrl + ')';
        topAv.style.backgroundSize    = 'cover';
        topAv.style.backgroundPosition= 'center';
        topAv.textContent = '';
      } else if (topAv && novoNome) {
        topAv.textContent = novoNome[0].toUpperCase();
      }
      if (typeof showToast === 'function') showToast('Perfil atualizado');
    }
    if (fotoFile) {
      var reader = new FileReader();
      reader.onload = function (e) { salvarPerfil(e.target.result); };
      reader.readAsDataURL(fotoFile);
    } else {
      salvarPerfil(fotoBase64 || null);
    }
    /* Salva nome no DB via fetch */
    var fd = new FormData();
    fd.append('nome', novoNome);
    if (document.getElementById('chkContraste').checked) fd.append('alto_contraste', '1');
    fd.append('tipo_agendamento', document.getElementById('tipo_agendamento').value);
    fd.append('intervalo_dias',   document.getElementById('intervalo_dias').value);
    fd.append('horario_envio',    document.getElementById('horario_envio').value);
    fetch('../actions/configuracoes_action.php', { method: 'POST', body: fd })
      .catch(function(){});
  });

})();
</script>

<?php require_once '../includes/footer.php'; ?>
