<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/connect.php';

$tituloPagina = 'Categorias | PagContas';
$cssPagina = 'categorias.css';
$nomeUsuario = $_SESSION['nome_usuario'] ?? 'Usuario';
$idUsuario = (int) $_SESSION['id_usuario'];

$sql = '
    SELECT id_categoria, nome_categoria, cor, meta_mensal, is_system
    FROM categoria
    WHERE is_active = 1
      AND (id_usuario IS NULL OR id_usuario = :id_usuario)
    ORDER BY is_system DESC, nome_categoria ASC
';

$stmt = $conn->prepare($sql);
$stmt->execute([':id_usuario' => $idUsuario]);
$categorias = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<main class="pagina-categorias">
    <section class="hero-categorias">
        <div class="hero-categorias__texto">
            <span class="hero-categorias__selo">Organização por categoria</span>
            <h1>Categorias disponíveis no PagContas</h1>
            <p>
                Estas categorias padrão ficam disponíveis para todas as contas.
                Você pode usá-las para organizar gastos, metas e relatórios.
            </p>
        </div>

        <div class="hero-categorias__caixa">
            <span>Total ativo</span>
            <strong><?= count($categorias); ?></strong>
            <p>Categorias prontas para uso no sistema.</p>
        </div>
    </section>

    <section class="lista-categorias">
        <?php foreach ($categorias as $categoria): ?>
            <article class="cartao-categoria">
                <div class="cartao-categoria__topo">
                    <span class="cartao-categoria__cor" style="background: <?= htmlspecialchars($categoria['cor'], ENT_QUOTES, 'UTF-8'); ?>;"></span>
                    <div>
                        <h2><?= htmlspecialchars($categoria['nome_categoria'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p><?= $categoria['is_system'] ? 'Categoria padrão do sistema' : 'Categoria criada pelo usuário'; ?></p>
                    </div>
                </div>

                <div class="cartao-categoria__rodape">
                    <span class="selo-categoria <?= $categoria['is_system'] ? 'selo-categoria--sistema' : 'selo-categoria--usuario'; ?>">
                        <?= $categoria['is_system'] ? 'Padrão' : 'Personalizada'; ?>
                    </span>

                    <span class="meta-categoria">
                        <?= $categoria['meta_mensal'] !== null ? 'Meta: R$ ' . number_format((float) $categoria['meta_mensal'], 2, ',', '.') : 'Sem meta definida'; ?>
                    </span>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>
