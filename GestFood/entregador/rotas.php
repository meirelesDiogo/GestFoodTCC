<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['entregador']);

$tituloPagina = 'Rotas';
$entregadorId = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("
    SELECT e.pedido_id, c.nome AS cliente, c.endereco, c.numero, c.bairro, c.cidade
    FROM entregas e JOIN pedidos p ON p.id=e.pedido_id JOIN clientes c ON c.id=p.cliente_id
    WHERE e.status='em_rota' AND e.entregador_id=?
");
$stmt->execute([$entregadorId]);
$paradas = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="gf-panel">
    <h3>Rota das Entregas Ativas</h3>
    <span style="color:#6b7280;font-size:13.5px;">Endereços das entregas em andamento, na ordem de retirada.</span>
</div>

<?php if ($paradas): ?>
    <div class="gf-panel">
        <?php foreach ($paradas as $i => $p): ?>
            <div style="display:flex;gap:14px;padding:12px 0;border-bottom:1px solid #f1f1f1;">
                <span style="background:var(--gf-primary);color:#fff;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><?= $i+1 ?></span>
                <div>
                    <strong>Pedido #<?= 1000 + $p['pedido_id'] ?> — <?= htmlspecialchars($p['cliente']) ?></strong><br>
                    <span style="color:#6b7280;font-size:13.5px;"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($p['endereco']) ?>, <?= htmlspecialchars($p['numero']) ?> — <?= htmlspecialchars($p['bairro']) ?>, <?= htmlspecialchars($p['cidade']) ?></span>
                    <div style="margin-top:6px;">
                        <a class="btn btn-outline" target="_blank"
                           href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($p['endereco'].', '.$p['numero'].', '.$p['bairro'].', '.$p['cidade']) ?>">
                           <i class="bi bi-map"></i> Abrir no Google Maps
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="gf-panel" style="text-align:center;color:#999;padding:36px;">Nenhuma rota ativa no momento.</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
