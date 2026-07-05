<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['admin']);

$tituloPagina = 'Entregas';

if (isset($_GET['acao'], $_GET['id'])) {
    if ($_GET['acao'] === 'iniciar') {
        $pdo->prepare("UPDATE entregas SET status='em_rota', iniciado_em=NOW() WHERE pedido_id=?")->execute([$_GET['id']]);
        $pdo->prepare("UPDATE pedidos SET status='em_entrega' WHERE id=?")->execute([$_GET['id']]);
    } elseif ($_GET['acao'] === 'concluir') {
        $pdo->prepare("UPDATE entregas SET status='entregue', finalizado_em=NOW() WHERE pedido_id=?")->execute([$_GET['id']]);
        $pdo->prepare("UPDATE pedidos SET status='entregue' WHERE id=?")->execute([$_GET['id']]);
    }
    header('Location: entregas.php');
    exit;
}

$bairroFiltro = $_GET['bairro'] ?? '';

$aguardando = $pdo->query("SELECT COUNT(*) FROM entregas WHERE status='aguardando'")->fetchColumn();
$emRota     = $pdo->query("SELECT COUNT(*) FROM entregas WHERE status='em_rota'")->fetchColumn();
$entreguesHoje = $pdo->query("SELECT COUNT(*) FROM entregas WHERE status='entregue' AND DATE(finalizado_em)=CURDATE()")->fetchColumn();

$bairros = $pdo->query("SELECT DISTINCT bairro FROM clientes WHERE bairro IS NOT NULL ORDER BY bairro")->fetchAll(PDO::FETCH_COLUMN);

$sqlBase = "
    SELECT e.*, p.total, c.nome AS cliente, c.endereco, c.bairro,
           GROUP_CONCAT(CONCAT(ip.quantidade,'x ',pr.nome) SEPARATOR '||') AS itens,
           SUM(ip.quantidade) AS total_itens
    FROM entregas e
    JOIN pedidos p ON p.id = e.pedido_id
    JOIN clientes c ON c.id = p.cliente_id
    JOIN itens_pedido ip ON ip.pedido_id = p.id
    JOIN produtos pr ON pr.id = ip.produto_id
    WHERE e.status = ?" . ($bairroFiltro ? " AND c.bairro = ?" : "") . "
    GROUP BY e.id ORDER BY p.criado_em
";

$paramsAguardando = $bairroFiltro ? ['aguardando', $bairroFiltro] : ['aguardando'];
$stmt = $pdo->prepare($sqlBase); $stmt->execute($paramsAguardando);
$prontosParaEntrega = $stmt->fetchAll();

$paramsRota = $bairroFiltro ? ['em_rota', $bairroFiltro] : ['em_rota'];
$stmt = $pdo->prepare($sqlBase); $stmt->execute($paramsRota);
$emRotaLista = $stmt->fetchAll();

$porBairro = $pdo->query("
    SELECT c.bairro, COUNT(*) AS total
    FROM entregas e JOIN pedidos p ON p.id=e.pedido_id JOIN clientes c ON c.id=p.cliente_id
    WHERE e.status IN ('aguardando','em_rota') GROUP BY c.bairro
")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="gf-cards">
    <div class="gf-card gf-card--kpi"><div><div class="label">Aguardando Entrega</div><div class="value"><?= $aguardando ?></div></div><div class="icon icon-green"><i class="bi bi-box-seam"></i></div></div>
    <div class="gf-card gf-card--kpi"><div><div class="label">Em Rota</div><div class="value"><?= $emRota ?></div></div><div class="icon icon-purple"><i class="bi bi-signpost-2"></i></div></div>
    <div class="gf-card gf-card--kpi"><div><div class="label">Entregues Hoje</div><div class="value"><?= $entreguesHoje ?></div></div><div class="icon icon-blue"><i class="bi bi-check-circle"></i></div></div>
</div>

<div class="gf-panel">
    <label class="form-label">Filtrar por Bairro</label>
    <form method="get">
        <select name="bairro" class="form-select" onchange="this.form.submit()" style="margin-bottom:0;">
            <option value="">Todos os Bairros</option>
            <?php foreach ($bairros as $b): ?>
                <option value="<?= htmlspecialchars($b) ?>" <?= $bairroFiltro === $b ? 'selected' : '' ?>><?= htmlspecialchars($b) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div style="display:flex;gap:20px;flex-wrap:wrap;">
    <div class="gf-panel" style="flex:1;min-width:320px;">
        <h3>Prontos para Entrega (<?= count($prontosParaEntrega) ?>)</h3>
        <?php foreach ($prontosParaEntrega as $e): ?>
            <div class="gf-card" style="margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;">
                    <strong>Pedido #<?= 1000 + $e['pedido_id'] ?></strong>
                    <span class="badge-status badge-pronto">Pronto</span>
                </div>
                <div style="color:#6b7280;font-size:13.5px;"><?= htmlspecialchars($e['cliente']) ?></div>
                <div style="font-size:13px;color:#444;"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($e['endereco']) ?> — <?= htmlspecialchars($e['bairro']) ?></div>
                <div style="font-size:13px;color:#444;"><i class="bi bi-basket"></i> <?= $e['total_itens'] ?> itens</div>
                <div style="background:#f7f7f9;border-radius:8px;padding:8px 10px;margin:8px 0;font-size:13px;">
                    <?= str_replace('||', '<br>', htmlspecialchars($e['itens'])) ?>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <strong>R$ <?= number_format($e['total'],2,',','.') ?></strong>
                    <a href="?acao=iniciar&id=<?= $e['pedido_id'] ?>" class="btn btn-purple"><i class="bi bi-truck"></i> Iniciar Entrega</a>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$prontosParaEntrega): ?><p style="color:#999;">Nenhum pedido pronto para entrega.</p><?php endif; ?>
    </div>

    <div class="gf-panel" style="flex:1;min-width:320px;">
        <h3>Em Rota (<?= count($emRotaLista) ?>)</h3>
        <?php foreach ($emRotaLista as $e): ?>
            <div class="gf-card" style="margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;">
                    <strong>Pedido #<?= 1000 + $e['pedido_id'] ?></strong>
                    <span class="badge-status badge-em_entrega">Em Rota</span>
                </div>
                <div style="color:#6b7280;font-size:13.5px;"><?= htmlspecialchars($e['cliente']) ?> — <?= htmlspecialchars($e['bairro']) ?></div>
                <a href="?acao=concluir&id=<?= $e['pedido_id'] ?>" class="btn btn-green" style="margin-top:8px;"><i class="bi bi-check-lg"></i> Confirmar Entrega</a>
            </div>
        <?php endforeach; ?>
        <?php if (!$emRotaLista): ?>
            <div style="text-align:center;color:#999;padding:30px 0;"><i class="bi bi-truck" style="font-size:28px;"></i><br>Nenhuma entrega em andamento</div>
        <?php endif; ?>
    </div>
</div>

<div class="gf-panel">
    <h3>Organização por Bairro</h3>
    <?php foreach ($porBairro as $b): ?>
        <div style="display:flex;justify-content:space-between;background:#fdf1e4;border-radius:8px;padding:10px 14px;margin-bottom:8px;">
            <strong><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($b['bairro']) ?></strong>
            <span style="background:var(--gf-primary);color:#fff;border-radius:50%;width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;font-size:12px;"><?= $b['total'] ?></span>
        </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
