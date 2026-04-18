<?php
session_start();

if (isset($_SESSION['id_usuario'])) {
    header('Location: dashboard.php');
    exit;
}

$erro = $_GET['erro'] ?? '';
$telefone = $_GET['telefone'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PagContas | Login</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <main class="login-page">
        <section class="login-card">
            <div class="login-brand">
                <span class="login-brand__icon">PC</span>
                <div>
                    <p class="login-brand__name">PagContas</p>
                    <p class="login-brand__subtitle">Entre na sua conta para continuar</p>
                </div>
            </div>

            <h1 class="login-title">Entrar</h1>
            <p class="login-description">
                Use seu telefone e sua senha para acessar o sistema.
            </p>

            <?php if ($erro === 'campos'): ?>
                <div class="login-alert login-alert--error">
                    Preencha o telefone e a senha.
                </div>
            <?php elseif ($erro === 'usuario'): ?>
                <div class="login-alert login-alert--error">
                    Telefone ou senha incorretos.
                </div>
            <?php elseif ($erro === 'sistema'): ?>
                <div class="login-alert login-alert--error">
                    Nao foi possivel entrar agora. Tente novamente.
                </div>
            <?php endif; ?>

            <form action="../actions/login_action.php" method="post" class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input
                        type="text"
                        id="telefone"
                        name="telefone"
                        placeholder="(65) 99999-9999"
                        maxlength="15"
                        value="<?= htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8'); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <div class="password-field">
                        <input
                            type="password"
                            id="senha"
                            name="senha"
                            placeholder="Digite sua senha"
                            required
                        >
                        <button type="button" class="password-toggle" id="toggleSenha">
                            Mostrar
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-button">Entrar</button>
            </form>
        </section>
    </main>

    <script src="../js/login.js"></script>
</body>
</html>
