<?php
// Script preparado para o recebimento de mensagens e áudios da Twilio

$mensagemTexto = $_POST['Body'] ?? '';       // Texto enviado pelo usuário
$linkAudio     = $_POST['MediaUrl0'] ?? '';  // URL do áudio caso ele tenha usado voz
$numeroZap     = $_POST['From'] ?? '';       // Número do WhatsApp de quem enviou

// Formato XML exigido pela Twilio para responder o WhatsApp
header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<Response>\n";

if (!empty($linkAudio)) {
    // Resposta automática caso chegue uma gravação de voz
    echo "    <Message>PagContas: Áudio recebido! Processando o comando de voz para registrar seu gasto...</Message>\n";
} else if (!empty($mensagemTexto)) {
    // Resposta automática para mensagens de texto comuns
    echo "    <Message>PagContas: O gasto '$mensagemTexto' foi computado no seu painel!</Message>\n";
} else {
    echo "    <Message>Olá! Envie uma mensagem de texto ou grave um áudio para adicionar contas ao PagContas.</Message>\n";
}

echo "</Response>";
?>