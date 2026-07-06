<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['atendente']);

$tituloPagina = 'Cupons';

function gerarCodigoCupom(): string {
    return 'XSAL-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

// --- Gerar novo cupom para um pedido ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['pedido_id'])) {
    $codigo = gerarCodigoCupom();
    // Garante código único (poucas tentativas, chance de colisão é baixíssima)
    for ($i = 0; $i < 5; $i++) {
        $existe = $pdo->prepare("SELECT COUNT(*) FROM cupons WHERE codigo = ?");
        $existe->execute([$codigo]);
        if (!$existe->fetchColumn()) break;
        $codigo = gerarCodigoCupom();
    }
    $pdo->prepare("INSERT INTO cupons (pedido_id, codigo) VALUES (?, ?)")->execute([$_POST['pedido_id'], $codigo]);
    header('Location: cupons.php');
    exit;
}

if (isset($_GET['excluir'])) {
    $pdo->prepare("DELETE FROM cupons WHERE id = ?")->execute([$_GET['excluir']]);
    header('Location: cupons.php');
    exit;
}

// Pedidos que ainda não possuem cupom emitido
$pedidosSemCupom = $pdo->query("
    SELECT p.id, p.total, c.nome AS cliente
    FROM pedidos p
    JOIN clientes c ON c.id = p.cliente_id
    LEFT JOIN cupons cu ON cu.pedido_id = p.id
    WHERE cu.id IS NULL AND p.status != 'cancelado'
    ORDER BY p.criado_em DESC
")->fetchAll();

$cupons = $pdo->query("
    SELECT cu.*, p.total, c.nome AS cliente
    FROM cupons cu
    JOIN pedidos p ON p.id = cu.pedido_id
    JOIN clientes c ON c.id = p.cliente_id
    ORDER BY cu.gerado_em DESC
")->fetchAll();

$totalCupons = count($cupons);
$cuponsHoje  = $pdo->query("SELECT COUNT(*) FROM cupons WHERE DATE(gerado_em) = CURDATE()")->fetchColumn();

require __DIR__ . '/../includes/header.php';
?>

<div class="gf-cards">
    <div class="gf-card gf-card--kpi"><div><div class="label">Cupons Emitidos</div><div class="value"><?= $totalCupons ?></div></div><div class="icon icon-purple"><i class="bi bi-ticket-perforated"></i></div></div>
    <div class="gf-card gf-card--kpi"><div><div class="label">Emitidos Hoje</div><div class="value"><?= $cuponsHoje ?></div></div><div class="icon icon-green"><i class="bi bi-check-circle"></i></div></div>
    <div class="gf-card gf-card--kpi"><div><div class="label">Pedidos Sem Cupom</div><div class="value"><?= count($pedidosSemCupom) ?></div></div><div class="icon icon-yellow"><i class="bi bi-receipt"></i></div></div>
</div>

<div class="gf-panel">
    <h3>Emitir Novo Cupom</h3>
    <span style="color:#6b7280;font-size:13.5px;">Selecione um pedido para gerar um código de cupom.</span>
    <form method="post" style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:260px;">
            <label class="form-label">Pedido</label>
            <select name="pedido_id" class="form-select" required style="margin-bottom:0;">
                <option value="">Selecione o pedido...</option>
                <?php foreach ($pedidosSemCupom as $p): ?>
                    <option value="<?= $p['id'] ?>">#<?= 1000 + $p['id'] ?> — <?= htmlspecialchars($p['cliente']) ?> — R$ <?= number_format($p['total'],2,',','.') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" <?= !$pedidosSemCupom ? 'disabled' : '' ?>><i class="bi bi-ticket-perforated"></i> Gerar Cupom</button>
    </form>
    <?php if (!$pedidosSemCupom): ?><p style="color:#999;font-size:13px;margin-top:10px;">Todos os pedidos já possuem cupom emitido.</p><?php endif; ?>
</div>

<div class="gf-panel">
    <h3>Histórico de Cupons</h3>
    <table class="gf-table">
        <thead><tr><th>Código</th><th>Pedido</th><th>Cliente</th><th>Valor</th><th>Emitido em</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach ($cupons as $cu): ?>
            <tr>
                <td><strong><i class="bi bi-ticket-perforated"></i> <?= htmlspecialchars($cu['codigo']) ?></strong></td>
                <td>#<?= 1000 + $cu['pedido_id'] ?></td>
                <td><?= htmlspecialchars($cu['cliente']) ?></td>
                <td>R$ <?= number_format($cu['total'],2,',','.') ?></td>
                <td><?= date('d/m/Y H:i', strtotime($cu['gerado_em'])) ?></td>
                <td><a href="?excluir=<?= $cu['id'] ?>" class="btn btn-light-red" data-confirm="Excluir este cupom?" title="Excluir"><i class="bi bi-trash"></i></a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$cupons): ?><tr><td colspan="6" style="text-align:center;color:#999;">Nenhum cupom emitido ainda.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>