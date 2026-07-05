<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['producao']);

$tituloPagina = 'Produção';

if (isset($_GET['acao'], $_GET['id'])) {
    $novoStatus = $_GET['acao'] === 'iniciar' ? 'em_producao' : 'pronto';
    $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?")->execute([$novoStatus, $_GET['id']]);
    if ($novoStatus === 'pronto') {
        $pdo->prepare("INSERT INTO entregas (pedido_id, status) VALUES (?, 'aguardando')")->execute([$_GET['id']]);
    }
    header('Location: producao.php');
    exit;
}

$aguardando = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status='recebido'")->fetchColumn();
$emProducao = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status='em_producao'")->fetchColumn();
$prontos    = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status='pronto'")->fetchColumn();

$fila = $pdo->query("
    SELECT p.*, c.nome AS cliente,
           GROUP_CONCAT(CONCAT(ip.quantidade,'x ',pr.nome) SEPARATOR '||') AS itens,
           MAX(pr.tempo_preparo) AS tempo_estimado
    FROM pedidos p
    JOIN clientes c ON c.id = p.cliente_id
    JOIN itens_pedido ip ON ip.pedido_id = p.id
    JOIN produtos pr ON pr.id = ip.produto_id
    WHERE p.status IN ('recebido','em_producao')
    GROUP BY p.id ORDER BY p.criado_em
")->fetchAll();

$prontosEntrega = $pdo->query("
    SELECT p.* FROM pedidos p WHERE p.status = 'pronto' ORDER BY p.criado_em
")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="gf-cards">
    <div class="gf-card gf-card--kpi"><div><div class="label">Aguardando</div><div class="value"><?= $aguardando ?></div></div><div class="icon icon-blue"><i class="bi bi-hourglass-split"></i></div></div>
    <div class="gf-card gf-card--kpi"><div><div class="label">Em Produção</div><div class="value"><?= $emProducao ?></div></div><div class="icon icon-yellow"><i class="bi bi-fire"></i></div></div>
    <div class="gf-card gf-card--kpi"><div><div class="label">Prontos</div><div class="value"><?= $prontos ?></div></div><div class="icon icon-green"><i class="bi bi-check-circle"></i></div></div>
</div>

<div class="gf-panel">
    <h3>Fila de Produção (<?= count($fila) ?> pedidos)</h3>
    <?php foreach ($fila as $i => $p): ?>
        <div class="gf-card" style="margin-bottom:14px;<?= $p['status']==='em_producao' ? 'border-left:4px solid var(--gf-yellow);' : '' ?>">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <span style="background:var(--gf-primary);color:#fff;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;font-size:12px;margin-right:8px;"><?= $i+1 ?></span>
                    <strong>Pedido #<?= 1000 + $p['id'] ?></strong>
                    <span class="badge-status badge-<?= $p['status'] ?>"><?= $p['status']==='em_producao' ? 'Em Produção' : 'Aguardando' ?></span><br>
                    <span style="color:#6b7280;font-size:13.5px;margin-left:32px;"><?= htmlspecialchars($p['cliente']) ?></span>
                </div>
                <?php if ($p['status'] === 'em_producao'): ?>
                    <a href="?acao=finalizar&id=<?= $p['id'] ?>" class="btn btn-green"><i class="bi bi-check-lg"></i> Finalizar</a>
                <?php else: ?>
                    <a href="?acao=iniciar&id=<?= $p['id'] ?>" class="btn btn-amber"><i class="bi bi-play-fill"></i> Iniciar Produção</a>
                <?php endif; ?>
            </div>
            <div style="margin:10px 0 0 32px;font-size:13.5px;color:#444;">
                <?= str_replace('||', '<br>', htmlspecialchars($p['itens'])) ?>
            </div>
            <div style="margin:8px 0 0 32px;color:#9aa0a6;font-size:12.5px;">
                <i class="bi bi-clock"></i> Recebido: <?= date('H:i', strtotime($p['criado_em'])) ?>
                &nbsp; <i class="bi bi-stopwatch"></i> Tempo estimado: <?= $p['tempo_estimado'] ?> min
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (!$fila): ?><p style="color:#999;">Nenhum pedido na fila de produção.</p><?php endif; ?>
</div>

<div class="gf-panel">
    <h3>Pedidos Prontos para Entrega</h3>
    <?php foreach ($prontosEntrega as $p): ?>
        <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f1f1;">
            <span>#<?= 1000 + $p['id'] ?></span>
            <span style="color:var(--gf-green);"><i class="bi bi-check-circle-fill"></i></span>
        </div>
    <?php endforeach; ?>
    <?php if (!$prontosEntrega): ?><p style="color:#999;">Nenhum pedido pronto no momento.</p><?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
