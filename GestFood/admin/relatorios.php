<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['admin']);

$tituloPagina = 'Relatórios';

$periodo = $_GET['periodo'] ?? 'hoje';
$filtroData = match ($periodo) {
    'semana' => "p.criado_em >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
    'mes'    => "p.criado_em >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
    default  => "DATE(p.criado_em) = CURDATE()",
};

$totalPedidos = $pdo->query("SELECT COUNT(*) FROM pedidos p WHERE $filtroData")->fetchColumn();
$faturamento  = $pdo->query("SELECT COALESCE(SUM(total),0) FROM pedidos p WHERE $filtroData AND status != 'cancelado'")->fetchColumn();
$ticketMedio  = $totalPedidos > 0 ? $faturamento / $totalPedidos : 0;
$entregues    = $pdo->query("SELECT COUNT(*) FROM pedidos p WHERE $filtroData AND status='entregue'")->fetchColumn();
$taxaEntrega  = $totalPedidos > 0 ? round(($entregues / $totalPedidos) * 100) : 0;

$maisVendidos = $pdo->query("
    SELECT pr.nome, COALESCE(SUM(ip.quantidade),0) AS qtd, COALESCE(SUM(ip.quantidade*ip.preco_unitario),0) AS total
    FROM produtos pr
    LEFT JOIN itens_pedido ip ON ip.produto_id = pr.id
    LEFT JOIN pedidos p ON p.id = ip.pedido_id AND $filtroData
    GROUP BY pr.id ORDER BY qtd DESC
")->fetchAll();

$statusResumo = $pdo->query("SELECT status, COUNT(*) AS total FROM pedidos p WHERE $filtroData GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$pagamentos = $pdo->query("SELECT forma_pagamento, COUNT(*) AS total FROM pedidos p WHERE $filtroData GROUP BY forma_pagamento")->fetchAll(PDO::FETCH_KEY_PAIR);

require __DIR__ . '/../includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

<div class="gf-panel" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h3 style="margin-bottom:2px;">Relatórios</h3>
        <span style="color:#6b7280;font-size:13.5px;">Análise de vendas e desempenho</span>
    </div>
    <div style="display:flex;gap:10px;">
        <form method="get">
            <select name="periodo" class="form-select" style="margin:0;" onchange="this.form.submit()">
                <option value="hoje" <?= $periodo==='hoje'?'selected':'' ?>>Hoje</option>
                <option value="semana" <?= $periodo==='semana'?'selected':'' ?>>Últimos 7 dias</option>
                <option value="mes" <?= $periodo==='mes'?'selected':'' ?>>Últimos 30 dias</option>
            </select>
        </form>
        <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-download"></i> Exportar</button>
    </div>
</div>

<div class="gf-cards">
    <div class="gf-card gf-card--kpi"><div><div class="label">Total de Pedidos</div><div class="value"><?= $totalPedidos ?></div></div><div class="icon icon-blue"><i class="bi bi-receipt"></i></div></div>
    <div class="gf-card gf-card--kpi"><div><div class="label">Faturamento</div><div class="value">R$ <?= number_format($faturamento,2,',','.') ?></div></div><div class="icon icon-green"><i class="bi bi-currency-dollar"></i></div></div>
    <div class="gf-card gf-card--kpi"><div><div class="label">Ticket Médio</div><div class="value">R$ <?= number_format($ticketMedio,2,',','.') ?></div></div><div class="icon icon-yellow"><i class="bi bi-graph-up"></i></div></div>
    <div class="gf-card gf-card--kpi"><div><div class="label">Taxa de Entrega</div><div class="value"><?= $taxaEntrega ?>%</div></div><div class="icon icon-purple"><i class="bi bi-file-earmark-bar-graph"></i></div></div>
</div>

<div style="display:flex;gap:20px;flex-wrap:wrap;">
    <div class="gf-panel" style="flex:1;min-width:320px;">
        <h3>Produtos Mais Vendidos</h3>
        <canvas id="graficoProdutos" height="180"></canvas>
    </div>
    <div class="gf-panel" style="flex:1;min-width:320px;">
        <h3>Status dos Pedidos</h3>
        <canvas id="graficoStatus" height="180"></canvas>
    </div>
</div>

<div style="display:flex;gap:20px;flex-wrap:wrap;">
    <div class="gf-panel" style="flex:1;min-width:320px;">
        <h3>Ranking de Produtos</h3>
        <?php foreach ($maisVendidos as $i => $p): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f1f1f1;">
                <div><span style="background:var(--gf-primary);color:#fff;border-radius:50%;width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;font-size:12px;margin-right:8px;"><?= $i+1 ?></span>
                    <?= htmlspecialchars($p['nome']) ?><br><span style="margin-left:32px;color:#9aa0a6;font-size:12px;"><?= $p['qtd'] ?> unidades</span></div>
                <strong style="color:var(--gf-green);">R$ <?= number_format($p['total'],2,',','.') ?></strong>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="gf-panel" style="flex:1;min-width:320px;">
        <h3>Formas de Pagamento</h3>
        <?php foreach ($pagamentos as $forma => $qtd): ?>
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f1f1;">
                <span><?= htmlspecialchars($forma) ?></span><strong><?= $qtd ?></strong>
            </div>
        <?php endforeach; ?>
        <?php if (!$pagamentos): ?><p style="color:#999;">Sem dados no período.</p><?php endif; ?>
    </div>
</div>

<script>
new Chart(document.getElementById('graficoProdutos'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($maisVendidos, 'nome')) ?>,
        datasets: [{ label: 'Vendidos', data: <?= json_encode(array_map('intval', array_column($maisVendidos, 'qtd'))) ?>, backgroundColor: '#e2601f' }]
    },
    options: { plugins: { legend: { display: false } } }
});
new Chart(document.getElementById('graficoStatus'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($statusResumo)) ?>,
        datasets: [{ data: <?= json_encode(array_values($statusResumo)) ?>, backgroundColor: ['#2f80ed','#f2a93b','#27ae60','#8e44ad','#9aa0a6','#e74c3c'] }]
    }
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>