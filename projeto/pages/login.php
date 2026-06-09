<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

if (isset($_SESSION['id_usuario'])) {
    header('Location: dashboard.php');
    exit;
}

$erro     = $_GET['erro']     ?? '';
$telefone = $_GET['telefone'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>PagContas — Entrar</title>
  <link rel="stylesheet" href="../css/global.css">
  <link rel="stylesheet" href="../css/login.css">
</head>
<body class="login-body">

<div class="login-wrap">
  <div class="login-hero">
    <div class="login-logo">PC</div>
    <h1>PagContas</h1>
    <p>Gerencie suas contas com praticidade</p>
  </div>

  <div class="login-card">
    <?php if ($erro === 'sistema'): ?>
      <div class="login-alert login-alert--erro">
        Não foi possível entrar agora. Tente novamente.
      </div>
    <?php endif; ?>

    <form action="../actions/login_action.php" method="post" id="loginForm" class="form-grid">
      <div class="campo">
        <label for="telefone">Telefone</label>
        <input
          type="text"
          id="telefone"
          name="telefone"
          placeholder="(99) 99999-9999"
          maxlength="15"
          inputmode="tel"
          value="<?= htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8'); ?>"
          autocomplete="tel"
        >
        <span id="errTelefone" class="campo-erro">Telefone inválido.</span>
      </div>

      <div class="campo">
        <label for="senha">Senha</label>
        <div class="campo-senha">
          <input
            type="password"
            id="senha"
            name="senha"
            placeholder="Sua senha"
            autocomplete="current-password"
          >
          <button type="button" id="toggleSenha" class="campo-senha__toggle">Mostrar</button>
        </div>
        <span id="errSenha" class="campo-erro">Senha obrigatória.</span>
        <?php if ($erro === 'usuario'): ?>
          <span id="errLogin" class="campo-erro campo-erro--vis">Telefone ou senha incorretos.</span>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary btn-block" style="margin-top:4px;">
        Entrar
      </button>
    </form>
  </div>
</div>

<script src="../js/login.js"></script>
</body>
</html>
