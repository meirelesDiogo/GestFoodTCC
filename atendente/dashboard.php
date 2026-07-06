<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['atendente']);

$tituloPagina = 'Dashboard';

$pedidosHoje  = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(criado_em) = CURDATE()")->fetchColumn();
$aguardando   = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status = 'recebido'")->fetchColumn();
$cuponsHoje   = $pdo->query("SELECT COUNT(*) FROM cupons WHERE DATE(gerado_em) = CURDATE()")->fetchColumn();

$recentes = $pdo->query("
    SELECT p.id, p.status, p.total, p.criado_em, c.nome AS cliente
    FROM pedidos p JOIN clientes c ON c.id = p.cliente_id
    ORDER BY p.criado_em DESC LIMIT 6
")->fetchAll();

$statusLabels = [
    'recebido' => 'Recebido', 'em_producao' => 'Em Produção', 'pronto' => 'Pronto',
    'em_entrega' => 'Em Entrega', 'entregue' => 'Entregue', 'cancelado' => 'Cancelado',
];

require __DIR__ . '/../includes/header.php';
?>

<div class="gf-cards">
    <div class="gf-card gf-card--kpi"><div><div class="label">Pedidos Hoje</div><div class="value"><?= $pedidosHoje ?></div></div><div class="icon icon-blue"><i class="bi bi-receipt"></i></div></div>
    <div class="gf-card gf-card--kpi"><div><div class="label">Aguardando Produção</div><div class="value"><?= $aguardando ?></div></div><div class="icon icon-yellow"><i class="bi bi-hourglass-split"></i></div></div>
    <div class="gf-card gf-card--kpi"><div><div class="label">Cupons Emitidos Hoje</div><div class="value"><?= $cuponsHoje ?></div></div><div class="icon icon-purple"><i class="bi bi-ticket-perforated"></i></div></div>
</div>

<div class="gf-panel">
    <h3>Pedidos Recentes</h3>
    <?php foreach ($recentes as $p): ?>
        <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f1f1f1;">
            <div>
                <strong>#<?= 1000 + $p['id'] ?></strong>
                <span class="badge-status badge-<?= $p['status'] ?>"><?= $statusLabels[$p['status']] ?></span><br>
                <span style="color:#6b7280;font-size:13.5px;"><?= htmlspecialchars($p['cliente']) ?></span>
            </div>
            <div style="text-align:right;">
                <strong>R$ <?= number_format($p['total'],2,',','.') ?></strong><br>
                <span style="color:#9aa0a6;font-size:12px;"><?= date('d/m H:i', strtotime($p['criado_em'])) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (!$recentes): ?><p style="color:#999;">Nenhum pedido registrado ainda.</p><?php endif; ?>
</div>

<div class="gf-panel" style="text-align:center;padding:36px;">
    <p style="color:#6b7280;margin-bottom:16px;">Cadastre um novo pedido para um cliente.</p>
    <a href="pedidos.php?novo=1" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo Pedido</a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
