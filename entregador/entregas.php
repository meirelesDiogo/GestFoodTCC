<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['entregador']);

$tituloPagina = 'Entregas';
$entregadorId = $_SESSION['usuario_id'];

if (isset($_GET['acao'], $_GET['id'])) {
    if ($_GET['acao'] === 'aceitar') {
        $pdo->prepare("UPDATE entregas SET status='em_rota', entregador_id=?, iniciado_em=NOW() WHERE pedido_id=?")->execute([$entregadorId, $_GET['id']]);
        $pdo->prepare("UPDATE pedidos SET status='em_entrega', entregador_id=? WHERE id=?")->execute([$entregadorId, $_GET['id']]);
    } elseif ($_GET['acao'] === 'concluir') {
        $pdo->prepare("UPDATE entregas SET status='entregue', finalizado_em=NOW() WHERE pedido_id=? AND entregador_id=?")->execute([$_GET['id'], $entregadorId]);
        $pdo->prepare("UPDATE pedidos SET status='entregue' WHERE id=?")->execute([$_GET['id']]);
    }
    header('Location: entregas.php');
    exit;
}

$disponiveis = $pdo->query("
    SELECT e.*, p.total, c.nome AS cliente, c.endereco, c.numero, c.bairro
    FROM entregas e JOIN pedidos p ON p.id=e.pedido_id JOIN clientes c ON c.id=p.cliente_id
    WHERE e.status='aguardando' ORDER BY p.criado_em
")->fetchAll();

$stmt = $pdo->prepare("
    SELECT e.*, p.total, c.nome AS cliente, c.endereco, c.numero, c.bairro
    FROM entregas e JOIN pedidos p ON p.id=e.pedido_id JOIN clientes c ON c.id=p.cliente_id
    WHERE e.status='em_rota' AND e.entregador_id=? ORDER BY e.iniciado_em
");
$stmt->execute([$entregadorId]);
$minhasEntregas = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="gf-panel">
    <h3>Minhas Entregas em Rota</h3>
    <?php foreach ($minhasEntregas as $e): ?>
        <div class="gf-card" style="margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;">
                <strong>Pedido #<?= 1000 + $e['pedido_id'] ?></strong>
                <span class="badge-status badge-em_entrega">Em Rota</span>
            </div>
            <div style="color:#6b7280;font-size:13.5px;"><?= htmlspecialchars($e['cliente']) ?></div>
            <div style="font-size:13px;"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($e['endereco']) ?>, <?= htmlspecialchars($e['numero']) ?> — <?= htmlspecialchars($e['bairro']) ?></div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">
                <strong>R$ <?= number_format($e['total'],2,',','.') ?></strong>
                <a href="?acao=concluir&id=<?= $e['pedido_id'] ?>" class="btn btn-green"><i class="bi bi-check-lg"></i> Confirmar Entrega</a>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (!$minhasEntregas): ?><p style="color:#999;">Você não tem entregas em rota.</p><?php endif; ?>
</div>

<div class="gf-panel">
    <h3>Pedidos Disponíveis para Retirada</h3>
    <?php foreach ($disponiveis as $e): ?>
        <div class="gf-card" style="margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;">
                <strong>Pedido #<?= 1000 + $e['pedido_id'] ?></strong>
                <span class="badge-status badge-pronto">Pronto</span>
            </div>
            <div style="color:#6b7280;font-size:13.5px;"><?= htmlspecialchars($e['cliente']) ?></div>
            <div style="font-size:13px;"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($e['endereco']) ?>, <?= htmlspecialchars($e['numero']) ?> — <?= htmlspecialchars($e['bairro']) ?></div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">
                <strong>R$ <?= number_format($e['total'],2,',','.') ?></strong>
                <a href="?acao=aceitar&id=<?= $e['pedido_id'] ?>" class="btn btn-purple"><i class="bi bi-truck"></i> Aceitar Entrega</a>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (!$disponiveis): ?><p style="color:#999;">Nenhum pedido disponível no momento.</p><?php endif; ?>
</div>
<?php if ($minhasEntregas): ?>
<script>
function enviarLocalizacao() {
    if (!navigator.geolocation) return;

    navigator.geolocation.getCurrentPosition(
        function (pos) {
            fetch('../api/atualizar_localizacao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'lat=' + pos.coords.latitude + '&lng=' + pos.coords.longitude
            }).catch(function (err) {
                console.error('Falha ao enviar localização:', err);
            });
        },
        function (err) {
            console.warn('Não foi possível obter localização:', err.message);
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
    );
}

enviarLocalizacao();
setInterval(enviarLocalizacao, 15000); // atualiza a cada 15 segundos
</script>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
