<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$tituloPagina = 'Dashboard | PagContas';
$cssPagina = 'dashboard.css';
$nomeUsuario = $_SESSION['nome_usuario'] ?? 'Usuario';

require_once '../includes/header.php';
?>

<main class="pagina-dashboard">
    <section class="painel-destaque">
        <div class="painel-destaque__conteudo">
            <span class="selo-dashboard">Visao geral financeira</span>
            <h1>Bem-vindo de volta, <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8'); ?>.</h1>
            <p>
                Aqui voce acompanha seus gastos, confere contas proximas do vencimento
                e enxerga onde o seu dinheiro esta sendo usado neste mes.
            </p>
        </div>

        <div class="painel-destaque__resumo">
            <p class="painel-destaque__rotulo">Saldo previsto do mês</p>
            <strong>R$ 2.480,00</strong>
            <span>Atualizado com base nas suas movimentações recentes.</span>
        </div>
    </section>

    <section class="grade-resumo">
        <article class="cartao-resumo cartao-resumo--destaque">
            <span class="cartao-resumo__rotulo">Gastos do mês</span>
            <strong class="cartao-resumo__valor">R$ 0000,00</strong>
            <p class="cartao-resumo__meta">Add porcentagem em relacao ao mes passado</p>
        </article>

        <article class="cartao-resumo">
            <span class="cartao-resumo__rotulo">Contas pendentes</span>
            <strong class="cartao-resumo__valor">0</strong>
            <p class="cartao-resumo__meta">Colocar a conta mais proxima que vai vencer</p>
        </article>

        <article class="cartao-resumo">
            <span class="cartao-resumo__rotulo">Categorias ativas</span>
            <strong class="cartao-resumo__valor">8</strong>
            <p class="cartao-resumo__meta">3 acima de 70% da meta</p>
        </article>

        <article class="cartao-resumo">
            <span class="cartao-resumo__rotulo">Relatório configurado</span>
            <strong class="cartao-resumo__valor">Quinzenal</strong>
            <p class="cartao-resumo__meta">Proximo envio em 30/04</p>
        </article>
    </section>

    <section class="grade-dashboard">
        <article class="painel-dashboard painel-dashboard--amplo">
            <div class="cabecalho-painel">
                <div>
                    <p class="cabecalho-painel__rotulo">Categorias</p>
                    <h2>Meta mensal por categoria</h2>
                </div>
                <a href="categorias.php" class="link-painel">Gerenciar</a>
            </div>

            <div class="lista-progresso">
                <div class="item-progresso">
                    <div class="item-progresso__topo">
                        <span>Mercado</span>
                        <span>R$ 420,00 / R$ 600,00</span>
                    </div>
                    <div class="barra-progresso">
                        <span style="width: 70%"></span>
                    </div>
                </div>

                <div class="item-progresso">
                    <div class="item-progresso__topo">
                        <span>Transporte</span>
                        <span>R$ 180,00 / R$ 250,00</span>
                    </div>
                    <div class="barra-progresso barra-progresso--azul">
                        <span style="width: 72%"></span>
                    </div>
                </div>

                <div class="item-progresso">
                    <div class="item-progresso__topo">
                        <span>Lazer</span>
                        <span>R$ 230,00 / R$ 200,00</span>
                    </div>
                    <div class="barra-progresso barra-progresso--perigo">
                        <span style="width: 100%"></span>
                    </div>
                </div>

                <div class="item-progresso">
                    <div class="item-progresso__topo">
                        <span>Saude</span>
                        <span>R$ 95,00 / R$ 180,00</span>
                    </div>
                    <div class="barra-progresso barra-progresso--dourada">
                        <span style="width: 53%"></span>
                    </div>
                </div>
            </div>
        </article>

        <article class="painel-dashboard">
            <div class="cabecalho-painel">
                <div>
                    <p class="cabecalho-painel__rotulo">Contas</p>
                    <h2>Próximos vencimentos</h2>
                </div>
                <a href="contas.php" class="link-painel">Ver tudo</a>
            </div>

            <div class="lista-contas">

                <div class="item-conta">
                    <div>
                        <strong>Energia eletrica</strong>
                        <span>Vence em 30/04</span>
                    </div>
                    <em>R$ 214,70</em>
                </div>

                <div class="item-conta">
                    <div>
                        <strong>Plano de celular</strong>
                        <span>Vence em 02/05</span>
                    </div>
                    <em>R$ 59,90</em>
                </div>
            </div>
        </article>

        <article class="painel-dashboard">
            <div class="cabecalho-painel">
                <div>
                    <p class="cabecalho-painel__rotulo">Resumo</p>
                    <h2>Seu ritmo financeiro</h2>
                </div>
            </div>

            <div class="pilha-insights">
                <div class="cartao-insight cartao-insight--sucesso">
                    <span>Maior controle</span>
                    <strong>72% das categorias estao dentro da meta.</strong>
                </div>

                <div class="cartao-insight cartao-insight--alerta">
                    <span>Ponto de atencao</span>
                    <strong>Lazer passou da meta mensal planejada.</strong>
                </div>

                <div class="cartao-insight cartao-insight--neutro">
                    <span>Proximo passo</span>
                    <strong>Revise gastos pendentes antes do fechamento do mes.</strong>
                </div>
            </div>
        </article>

        <article class="painel-dashboard painel-dashboard--amplo">
            <div class="cabecalho-painel">
                <div>
                    <p class="cabecalho-painel__rotulo">Movimentações</p>
                    <h2>Ultimos gastos registrados</h2>
                </div>
                <a href="gastos.php" class="link-painel">Abrir gastos</a>
            </div>

            <div class="tabela-atividades">
                <div class="tabela-atividades__cabecalho">
                    <span>Descricao</span>
                    <span>Categoria</span>
                    <span>Data</span>
                    <span>Status</span>
                    <span>Valor</span>
                </div>

                <div class="linha-atividade">
                    <span>Compra no mercado</span>
                    <span>Mercado</span>
                    <span>24/04</span>
                    <span class="selo-status selo-status--pago">Pago</span>
                    <strong>R$ 186,40</strong>
                </div>

                <div class="linha-atividade">
                    <span>Uber para faculdade</span>
                    <span>Transporte</span>
                    <span>23/04</span>
                    <span class="selo-status selo-status--pago">Pago</span>
                    <strong>R$ 24,90</strong>
                </div>

                <div class="linha-atividade">
                    <span>Assinatura streaming</span>
                    <span>Lazer</span>
                    <span>23/04</span>
                    <span class="selo-status selo-status--pendente">Pendente</span>
                    <strong>R$ 34,90</strong>
                </div>

                <div class="linha-atividade">
                    <span>Farmacia</span>
                    <span>Saude</span>
                    <span>22/04</span>
                    <span class="selo-status selo-status--pago">Pago</span>
                    <strong>R$ 58,30</strong>
                </div>
            </div>
        </article>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>
