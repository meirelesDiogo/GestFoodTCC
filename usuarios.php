<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['admin']);

$tituloPagina = 'Dashboard';
$hoje = date('Y-m-d');

$pedidosHoje   = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(criado_em) = CURDATE()")->fetchColumn();
$emProducao    = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status = 'em_producao'")->fetchColumn();
$entregasPend  = $pdo->query("SELECT COUNT(*) FROM entregas WHERE status IN ('aguardando','em_rota')")->fetchColumn();
$faturamento   = $pdo->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(criado_em) = CURDATE() AND status != 'cancelado'")->fetchColumn();

$recentes = $pdo->query("
    SELECT p.id, p.status, p.total, p.criado_em, c.nome AS cliente,
           (SELECT COALESCE(SUM(quantidade),0) FROM itens_pedido WHERE pedido_id = p.id) AS itens
    FROM pedidos p JOIN clientes c ON c.id = p.cliente_id
    ORDER BY p.criado_em DESC LIMIT 5
")->fetchAll();

$statusResumo = $pdo->query("
    SELECT status, COUNT(*) AS total FROM pedidos GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

$statusLabels = [
    'recebido' => ['Recebido', 'blue'], 'em_producao' => ['Em Produção', 'yellow'],
    'pronto' => ['Pronto', 'green'], 'em_entrega' => ['Em Entrega', 'purple'],
    'entregue' => ['Entregue', 'gray'], 'cancelado' => ['Cancelado', 'red'],
];

$maisVendidos = $pdo->query("
    SELECT pr.nome, pr.categoria, COALESCE(SUM(ip.quantidade),0) AS vendidos
    FROM produtos pr
    LEFT JOIN itens_pedido ip ON ip.produto_id = pr.id
    GROUP BY pr.id ORDER BY vendidos DESC LIMIT 6
")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="gf-cards">
    <div class="gf-card gf-card--kpi">
        <div><div class="label">Pedidos Hoje</div><div class="value"><?= $pedidosHoje ?></div></div>
        <div class="icon icon-blue"><i class="bi bi-receipt"></i></div>
    </div>
    <div class="gf-card gf-card--kpi">
        <div><div class="label">Em Produção</div><div class="value"><?= $emProducao ?></div></div>
        <div class="icon icon-yellow"><i class="bi bi-fire"></i></div>
    </div>
    <div class="gf-card gf-card--kpi">
        <div><div class="label">Entregas Pendentes</div><div class="value"><?= $entregasPend ?></div></div>
        <div class="icon icon-purple"><i class="bi bi-truck"></i></div>
    </div>
    <div class="gf-card gf-card--kpi">
        <div><div class="label">Faturamento Hoje</div><div class="value">R$ <?= number_format($faturamento, 2, ',', '.') ?></div></div>
        <div class="icon icon-green"><i class="bi bi-currency-dollar"></i></div>
    </div>
</div>

<div class="row g-4" style="display:flex;gap:20px;flex-wrap:wrap;">
    <div class="gf-panel" style="flex:2;min-width:320px;">
        <h3>Pedidos Recentes</h3>
        <?php foreach ($recentes as $p): [$label,] = $statusLabels[$p['status']]; ?>
            <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f1f1f1;">
                <div>
                    <strong>#<?= 1000 + $p['id'] ?></strong>
                    <span class="badge-status badge-<?= $p['status'] ?>"><?= $label ?></span><br>
                    <span style="color:#6b7280;font-size:13.5px;"><?= htmlspecialchars($p['cliente']) ?></span><br>
                    <span style="color:#9aa0a6;font-size:12px;"><?= date('H:i', strtotime($p['criado_em'])) ?></span>
                </div>
                <div style="text-align:right;">
                    <strong>R$ <?= number_format($p['total'],2,',','.') ?></strong><br>
                    <span style="color:#9aa0a6;font-size:12px;"><?= $p['itens'] ?> itens</span>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$recentes): ?><p style="color:#999;">Nenhum pedido registrado ainda.</p><?php endif; ?>
    </div>

    <div class="gf-panel" style="flex:1;min-width:260px;">
        <h3>Status dos Pedidos</h3>
        <?php foreach ($statusLabels as $key => [$label, $cor]): ?>
            <div style="display:flex;justify-content:space-between;padding:8px 0;">
                <span><span class="dot dot-<?= $cor ?>"></span><?= $label ?></span>
                <strong><?= $statusResumo[$key] ?? 0 ?></strong>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="gf-panel">
    <h3>Produtos Mais Vendidos</h3>
    <div class="gf-cards">
        <?php foreach ($maisVendidos as $pr): ?>
            <div class="gf-card">
                <strong><?= htmlspecialchars($pr['nome']) ?></strong><br>
                <span style="color:#9aa0a6;font-size:13px;"><?= htmlspecialchars($pr['categoria']) ?></span>
                <div style="display:flex;justify-content:space-between;margin-top:8px;">
                    <span style="font-size:13px;color:#6b7280;">Vendidos:</span>
                    <strong style="color:var(--gf-primary);"><?= $pr['vendidos'] ?></strong>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
