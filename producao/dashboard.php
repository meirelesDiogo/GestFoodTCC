<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['producao']);

$tituloPagina = 'Dashboard';

$aguardando = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status='recebido'")->fetchColumn();
$emProducao = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status='em_producao'")->fetchColumn();
$concluidosHoje = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status IN ('pronto','em_entrega','entregue') AND DATE(atualizado_em)=CURDATE()")->fetchColumn();

require __DIR__ . '/../includes/header.php';
?>

<div class="gf-cards">
    <div class="gf-card gf-card--kpi"><div><div class="label">Aguardando Produção</div><div class="value"><?= $aguardando ?></div></div><div class="icon icon-blue"><i class="bi bi-hourglass-split"></i></div></div>
    <div class="gf-card gf-card--kpi"><div><div class="label">Em Produção</div><div class="value"><?= $emProducao ?></div></div><div class="icon icon-yellow"><i class="bi bi-fire"></i></div></div>
    <div class="gf-card gf-card--kpi"><div><div class="label">Concluídos Hoje</div><div class="value"><?= $concluidosHoje ?></div></div><div class="icon icon-green"><i class="bi bi-check-circle"></i></div></div>
</div>

<div class="gf-panel" style="text-align:center;padding:36px;">
    <p style="color:#6b7280;margin-bottom:16px;">Acompanhe e atualize a fila de produção em tempo real.</p>
    <a href="producao.php" class="btn btn-primary">Ir para a Fila de Produção <i class="bi bi-arrow-right"></i></a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
