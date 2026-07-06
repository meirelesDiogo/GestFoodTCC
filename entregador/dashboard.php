<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['entregador']);

$tituloPagina = 'Dashboard';
$entregadorId = $_SESSION['usuario_id'];

$pendentes = $pdo->prepare("SELECT COUNT(*) FROM entregas WHERE status='aguardando'");
$pendentes->execute();
$pendentes = $pendentes->fetchColumn();

$minhasRota = $pdo->prepare("SELECT COUNT(*) FROM entregas WHERE entregador_id=? AND status='em_rota'");
$minhasRota->execute([$entregadorId]);
$minhasRota = $minhasRota->fetchColumn();

$entreguesHoje = $pdo->prepare("SELECT COUNT(*) FROM entregas WHERE entregador_id=? AND status='entregue' AND DATE(finalizado_em)=CURDATE()");
$entreguesHoje->execute([$entregadorId]);
$entreguesHoje = $entreguesHoje->fetchColumn();

require __DIR__ . '/../includes/header.php';
?>

<div class="gf-cards">
    <div class="gf-card gf-card--kpi"><div><div class="label">Disponíveis</div><div class="value"><?= $pendentes ?></div></div><div class="icon icon-blue"><i class="bi bi-box-seam"></i></div></div>
    <div class="gf-card gf-card--kpi"><div><div class="label">Minhas Entregas em Rota</div><div class="value"><?= $minhasRota ?></div></div><div class="icon icon-purple"><i class="bi bi-signpost-2"></i></div></div>
    <div class="gf-card gf-card--kpi"><div><div class="label">Entregues Hoje</div><div class="value"><?= $entreguesHoje ?></div></div><div class="icon icon-green"><i class="bi bi-check-circle"></i></div></div>
</div>

<div class="gf-panel" style="text-align:center;padding:36px;">
    <p style="color:#6b7280;margin-bottom:16px;">Veja os pedidos disponíveis para retirada e suas entregas em andamento.</p>
    <a href="entregas.php" class="btn btn-primary">Ver Minhas Entregas <i class="bi bi-arrow-right"></i></a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
