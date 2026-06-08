<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>PagContas - Painel Principal</title>
    <link rel="stylesheet" href="style.css">
</head>
<?php 
    // Lógica para detectar se o botão de acessibilidade foi clicado
    $acessibilidade = isset($_GET['acessibilidade']) && $_GET['acessibilidade'] == 'true';
    $classeAcessibilidade = $acessibilidade ? 'fonte-grande' : '';
?>
<body class="<?php echo $classeAcessibilidade; ?>">

<div class="container">
    <header class="flex">
        <h1>💰 PagContas</h1>
        
        <?php if ($acessibilidade): ?>
            <a href="index.php?acessibilidade=false" class="btn btn-acessibilidade">♿ Fonte Normal</a>
        <?php else: ?>
            <a href="index.php?acessibilidade=true" class="btn btn-acessibilidade">♿ Fonte Grande</a>
        <?php endif; ?>
    </header>

    <section class="flex">
        <div class="resumo-card">
            <h3>Total Pago</h3>
            <p style="color: #28a745; font-weight: bold;">R$ 150,00</p>
        </div>
        <div class="resumo-card">
            <h3>Total Pendente</h3>
            <p style="color: #dc3545; font-weight: bold;">R$ 120,00</p>
        </div>
    </section>

    <h2>📋 Suas Contas (WhatsApp e Comando de Voz)</h2>
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Categoria</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Almoço (Texto no Zap)</td>
                <td>R$ 45,00</td>
                <td>🍕 Alimentação</td>
                <td><span class="status pago">Pago</span></td>
            </tr>
            <tr>
                <td>Mercado Central (Áudio/Voz)</td>
                <td>R$ 105,00</td>
                <td>🍕 Alimentação</td>
                <td><span class="status pago">Pago</span></td>
            </tr>
            <tr>
                <td>Internet Banda Larga</td>
                <td>R$ 120,00</td>
                <td>🏠 Moradia</td>
                <td><span class="status pendente">Pendente</span></td>
            </tr>
        </tbody>
    </table>
</div>

</body>
</html>