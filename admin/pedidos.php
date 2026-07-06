<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['admin', 'atendente']);

$tituloPagina = 'Pedidos';

// --- Criar novo pedido ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['itens_pedido'])) {
    $itens = json_decode($_POST['itens_pedido'], true) ?: [];
    if ($_POST['cliente_id'] && $itens) {
        $pdo->beginTransaction();
        $total = 0;
        foreach ($itens as $item) $total += $item['qtd'] * $item['preco'];

        $stmt = $pdo->prepare("INSERT INTO pedidos (cliente_id, atendente_id, forma_pagamento, observacoes, total) VALUES (?,?,?,?,?)");
        $stmt->execute([$_POST['cliente_id'], $_SESSION['usuario_id'] ?? null, $_POST['forma_pagamento'], $_POST['observacoes'] ?: null, $total]);
        $pedidoId = $pdo->lastInsertId();

        $stmtItem = $pdo->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?,?,?,?)");
        foreach ($itens as $produtoId => $item) {
            $stmtItem->execute([$pedidoId, $produtoId, $item['qtd'], $item['preco']]);
        }
        $pdo->commit();
    }
    header('Location: pedidos.php');
    exit;
}

// --- Alterar status rapidamente ---
if (isset($_GET['status'], $_GET['id'])) {
    $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?")->execute([$_GET['status'], $_GET['id']]);
    header('Location: pedidos.php');
    exit;
}

$modoNovo = isset($_GET['novo']);

if ($modoNovo) {
    $clientes = $pdo->query("SELECT id, nome, telefone FROM clientes ORDER BY nome")->fetchAll();
    $produtos = $pdo->query("SELECT * FROM produtos WHERE ativo = 1 ORDER BY nome")->fetchAll();
} else {
    $pedidos = $pdo->query("
        SELECT p.*, c.nome AS cliente
        FROM pedidos p JOIN clientes c ON c.id = p.cliente_id
        ORDER BY p.criado_em DESC
    ")->fetchAll();
}

$statusLabels = [
    'recebido' => 'Recebido', 'em_producao' => 'Em Produção', 'pronto' => 'Pronto',
    'em_entrega' => 'Em Entrega', 'entregue' => 'Entregue', 'cancelado' => 'Cancelado',
];

require __DIR__ . '/../includes/header.php';
?>

<?php if ($modoNovo): ?>

    <div class="gf-panel">
        <h3>Novo Pedido</h3>
        <span style="color:#6b7280;font-size:13.5px;">Cadastre um novo pedido de cliente</span>
    </div>

    <form method="post" id="formPedido">
        <input type="hidden" name="itens_pedido" id="itensPedidoInput" value="{}">

        <div class="gf-panel">
            <h3>Informações do Cliente</h3>
            <label class="form-label">Selecionar Cliente *</label>
            <select name="cliente_id" class="form-select" required>
                <option value="">Buscar cliente por nome ou telefone...</option>
                <?php foreach ($clientes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?> — <?= htmlspecialchars($c['telefone']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="gf-panel">
            <h3>Produtos</h3>
            <div class="gf-cards">
                <?php foreach ($produtos as $p): ?>
                    <div class="gf-card" style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <strong><?= htmlspecialchars($p['nome']) ?></strong><br>
                            <span style="color:#6b7280;font-size:13px;">R$ <?= number_format($p['preco'],2,',','.') ?></span>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="adicionarProduto(<?= $p['id'] ?>,'<?= htmlspecialchars($p['nome'], ENT_QUOTES) ?>',<?= $p['preco'] ?>)">+</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="gf-panel">
            <h3>Pagamento e Observações</h3>
            <label class="form-label">Forma de Pagamento *</label>
            <select name="forma_pagamento" class="form-select" required>
                <option value="Dinheiro">Dinheiro</option>
                <option value="Cartão">Cartão</option>
                <option value="Pix">Pix</option>
            </select>
            <label class="form-label">Observações</label>
            <textarea name="observacoes" class="form-control" rows="3" placeholder="Observações sobre o pedido..."></textarea>
        </div>

        <div class="gf-panel" style="background:#fdf1e4;">
            <h3><i class="bi bi-receipt"></i> Resumo do Pedido</h3>
            <div id="resumoItens" style="margin-bottom:10px;">Nenhum item selecionado</div>
            <div style="display:flex;justify-content:space-between;"><span>Total de Itens:</span><strong id="resumoQtd">0</strong></div>
            <div style="display:flex;justify-content:space-between;margin-bottom:14px;"><span>Total:</span><strong id="resumoTotal">R$ 0.00</strong></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Confirmar Pedido</button>
        </div>
    </form>

<?php else: ?>

    <div class="gf-panel" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h3 style="margin-bottom:2px;">Pedidos</h3>
            <span style="color:#6b7280;font-size:13.5px;">Acompanhe e gerencie todos os pedidos</span>
        </div>
        <a href="?novo=1" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo Pedido</a>
    </div>

    <div class="gf-panel">
        <table class="gf-table">
            <thead><tr><th>Pedido</th><th>Cliente</th><th>Total</th><th>Status</th><th>Ações</th></tr></thead>
            <tbody>
            <?php foreach ($pedidos as $p): ?>
                <tr>
                    <td><strong>#<?= 1000 + $p['id'] ?></strong><br>
                        <span style="color:#9aa0a6;font-size:12px;"><?= date('d/m H:i', strtotime($p['criado_em'])) ?></span></td>
                    <td><?= htmlspecialchars($p['cliente']) ?></td>
                    <td>R$ <?= number_format($p['total'],2,',','.') ?></td>
                    <td><span class="badge-status badge-<?= $p['status'] ?>"><?= $statusLabels[$p['status']] ?></span></td>
                    <td>
                        <form method="get" style="display:inline-flex;gap:6px;align-items:center;">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <select name="status" class="form-select" style="margin:0;padding:6px 8px;font-size:12.5px;" onchange="this.form.submit()">
                                <?php foreach ($statusLabels as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= $p['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$pedidos): ?><tr><td colspan="5" style="text-align:center;color:#999;">Nenhum pedido registrado.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
