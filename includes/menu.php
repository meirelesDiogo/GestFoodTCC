<?php
/**
 * Menu lateral, varia conforme o tipo de usuário logado.
 * Espera que $tipo e $paginaAtual estejam definidos antes do include.
 */
$tipo = $tipo ?? tipoUsuario();
$paginaAtual = $paginaAtual ?? basename($_SERVER['PHP_SELF'], '.php');

$menus = [
    'admin' => [
        ['dashboard',  'bi-speedometer2', 'Dashboard'],
        ['clientes',   'bi-people', 'Clientes'],
        ['produtos',   'bi-box-seam', 'Produtos'],
        ['pedidos',    'bi-receipt', 'Pedidos'],
        ['producao',   'bi-fire', 'Produção'],
        ['entregas',   'bi-truck', 'Entregas'],
        ['relatorios', 'bi-bar-chart-line', 'Relatórios'],
        ['usuarios',   'bi-person-badge', 'Usuários'],
    ],
    'atendente' => [
        ['dashboard', 'bi-speedometer2', 'Dashboard'],
        ['pedidos',   'bi-receipt', 'Pedidos'],
        ['cupons',    'bi-ticket-perforated', 'Cupons'],
    ],
    'producao' => [
        ['dashboard', 'bi-speedometer2', 'Dashboard'],
        ['producao',  'bi-fire', 'Produção'],
    ],
    'entregador' => [
        ['dashboard', 'bi-speedometer2', 'Dashboard'],
        ['entregas',  'bi-truck', 'Entregas'],
        ['rotas',     'bi-signpost-2', 'Rotas'],
    ],
    'cliente' => [
        ['fazer_pedido', 'bi-cart-plus', 'Fazer Pedido'],
        ['meus_pedidos', 'bi-list-check', 'Meus Pedidos'],
        ['perfil',       'bi-person-circle', 'Meu Perfil'],
    ],
];

$itens = $menus[$tipo] ?? [];
$rotulos = [
    'admin' => 'Administrador', 'atendente' => 'Atendente',
    'producao' => 'Produção', 'entregador' => 'Entregador', 'cliente' => 'Cliente',
];
?>
<aside class="gf-sidebar" id="gfSidebar">
    <div class="gf-sidebar__brand">
        <h2>X Salgados</h2>
        <span>Sistema de Gestão</span>
    </div>
    <nav class="gf-nav">
        <?php foreach ($itens as [$slug, $icone, $label]): ?>
            <a href="<?= $slug ?>.php" class="<?= $paginaAtual === $slug ? 'active' : '' ?>">
                <i class="bi <?= $icone ?>"></i> <?= $label ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="gf-sidebar__footer">
        <div>Usuário<br><strong><?= htmlspecialchars($rotulos[$tipo] ?? '') ?></strong></div>
        <a href="<?= urlPara('logout.php') ?>" class="logout"><i class="bi bi-box-arrow-right"></i> Sair</a>
    </div>
</aside>
