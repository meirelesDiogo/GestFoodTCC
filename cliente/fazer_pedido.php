<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['cliente']);

$tituloPagina = 'Fazer Pedido';
$clienteId = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['itens_pedido'])) {
    $itens = json_decode($_POST['itens_pedido'], true) ?: [];
    if ($itens) {
        $pdo->beginTransaction();
        $total = 0;
        foreach ($itens as $item) $total += $item['qtd'] * $item['preco'];

        $stmt = $pdo->prepare("INSERT INTO pedidos (cliente_id, forma_pagamento, observacoes, total) VALUES (?,?,?,?)");
        $stmt->execute([$clienteId, $_POST['forma_pagamento'], $_POST['observacoes'] ?: null, $total]);
        $pedidoId = $pdo->lastInsertId();

        $stmtItem = $pdo->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?,?,?,?)");
        foreach ($itens as $produtoId => $item) {
            $stmtItem->execute([$pedidoId, $produtoId, $item['qtd'], $item['preco']]);
        }
        $pdo->commit();
        header('Location: meus_pedidos.php?sucesso=1');
        exit;
    }
}

$produtos = $pdo->query("SELECT * FROM produtos WHERE ativo = 1 ORDER BY categoria, nome")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<form method="post">
    <input type="hidden" name="itens_pedido" id="itensPedidoInput" value="{}">

    <div class="gf-panel">
        <h3>Escolha seus salgados</h3>
        <div class="gf-cards">
            <?php foreach ($produtos as $p): ?>
                <div class="gf-card" style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <strong><?= htmlspecialchars($p['nome']) ?></strong><br>
                        <span style="color:#6b7280;font-size:13px;"><?= htmlspecialchars($p['categoria']) ?> · R$ <?= number_format($p['preco'],2,',','.') ?></span>
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
        <textarea name="observacoes" class="form-control" rows="3" placeholder="Ex: sem cebola, troco para R$ 50..."></textarea>
    </div>

    <div class="gf-panel" style="background:#fdf1e4;">
        <h3><i class="bi bi-receipt"></i> Resumo do Pedido</h3>
        <div id="resumoItens" style="margin-bottom:10px;">Nenhum item selecionado</div>
        <div style="display:flex;justify-content:space-between;"><span>Total de Itens:</span><strong id="resumoQtd">0</strong></div>
        <div style="display:flex;justify-content:space-between;margin-bottom:14px;"><span>Total:</span><strong id="resumoTotal">R$ 0.00</strong></div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Confirmar Pedido</button>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
