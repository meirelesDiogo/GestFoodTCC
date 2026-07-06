<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['cliente']);

$tituloPagina = 'Meus Pedidos';
$clienteId = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("
    SELECT p.*, GROUP_CONCAT(CONCAT(ip.quantidade,'x ',pr.nome) SEPARATOR '||') AS itens
    FROM pedidos p
    LEFT JOIN itens_pedido ip ON ip.pedido_id = p.id
    LEFT JOIN produtos pr ON pr.id = ip.produto_id
    WHERE p.cliente_id = ?
    GROUP BY p.id ORDER BY p.criado_em DESC
");
$stmt->execute([$clienteId]);
$pedidos = $stmt->fetchAll();

$statusLabels = [
    'recebido' => 'Recebido', 'em_producao' => 'Em Produção', 'pronto' => 'Pronto',
    'em_entrega' => 'Em Entrega', 'entregue' => 'Entregue', 'cancelado' => 'Cancelado',
];

require __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['sucesso'])): ?>
    <div class="gf-panel" style="background:#e4f7ec;color:#1e7e42;"><i class="bi bi-check-circle-fill"></i> Pedido enviado com sucesso! Acompanhe o status abaixo.</div>
<?php endif; ?>

<div class="gf-panel">
    <h3>Histórico de Pedidos</h3>
    <?php foreach ($pedidos as $p): ?>
        <div class="gf-card" style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;">
                <strong>Pedido #<?= 1000 + $p['id'] ?></strong>
                <span class="badge-status badge-<?= $p['status'] ?>"><?= $statusLabels[$p['status']] ?></span>
            </div>
            <div style="color:#6b7280;font-size:13px;margin:6px 0;"><?= str_replace('||', ' · ', htmlspecialchars($p['itens'] ?? '')) ?></div>
            <div style="display:flex;justify-content:space-between;font-size:13px;color:#9aa0a6;">
                <span><?= date('d/m/Y H:i', strtotime($p['criado_em'])) ?> · <?= htmlspecialchars($p['forma_pagamento']) ?></span>
                <strong style="color:var(--gf-text);">R$ <?= number_format($p['total'],2,',','.') ?></strong>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (!$pedidos): ?><p style="color:#999;">Você ainda não fez nenhum pedido.</p><?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
