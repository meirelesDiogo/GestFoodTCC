<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['admin']);

$tituloPagina = 'Produtos';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [$_POST['nome'], $_POST['categoria'], $_POST['preco'], $_POST['tempo_preparo']];
    if (!empty($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE produtos SET nome=?, categoria=?, preco=?, tempo_preparo=? WHERE id=?");
        $stmt->execute([...$dados, $_POST['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO produtos (nome, categoria, preco, tempo_preparo) VALUES (?,?,?,?)");
        $stmt->execute($dados);
    }
    header('Location: produtos.php');
    exit;
}

if (isset($_GET['excluir'])) {
    $pdo->prepare("UPDATE produtos SET ativo = 0 WHERE id = ?")->execute([$_GET['excluir']]);
    header('Location: produtos.php');
    exit;
}

$produtoEdicao = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $produtoEdicao = $stmt->fetch();
}

$produtos = $pdo->query("SELECT * FROM produtos WHERE ativo = 1 ORDER BY categoria, nome")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="gf-panel" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h3 style="margin-bottom:2px;">Produtos</h3>
        <span style="color:#6b7280;font-size:13.5px;">Gerencie o catálogo de produtos</span>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProduto"><i class="bi bi-plus-lg"></i> Novo Produto</button>
</div>

<input type="text" class="gf-search" placeholder="Buscar por nome ou categoria..." data-busca="#gridProdutos .product-card">

<div class="gf-cards" id="gridProdutos">
    <?php foreach ($produtos as $p): ?>
        <div class="product-card">
            <div class="product-tile">
                <strong><?= htmlspecialchars($p['nome']) ?></strong><br>
                <span style="font-size:12.5px;opacity:.9;"><?= htmlspecialchars($p['categoria']) ?></span>
            </div>
            <div class="product-tile-body">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="color:#6b7280;font-size:13.5px;"><i class="bi bi-currency-dollar"></i> Preço</span>
                    <strong style="color:var(--gf-green);">R$ <?= number_format($p['preco'],2,',','.') ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                    <span style="color:#6b7280;font-size:13.5px;"><i class="bi bi-stopwatch"></i> Preparo</span>
                    <strong><?= $p['tempo_preparo'] ?> min</strong>
                </div>
                <div style="display:flex;gap:8px;">
                    <a href="?editar=<?= $p['id'] ?>" class="btn btn-light-orange" style="flex:1;text-align:center;"><i class="bi bi-pencil-square"></i> Editar</a>
                    <a href="?excluir=<?= $p['id'] ?>" class="btn btn-light-red" style="flex:1;text-align:center;" data-confirm="Excluir este produto?"><i class="bi bi-trash"></i> Excluir</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="modal fade <?= $produtoEdicao ? 'show' : '' ?>" id="modalProduto" tabindex="-1" style="<?= $produtoEdicao ? 'display:block;background:rgba(0,0,0,.5);' : '' ?>">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px;">
            <form method="post">
                <div class="modal-header"><h5 class="modal-title"><?= $produtoEdicao ? 'Editar' : 'Novo' ?> Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="window.location='produtos.php'"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?= $produtoEdicao['id'] ?? '' ?>">
                    <label class="form-label">Nome</label>
                    <input class="form-control" name="nome" required placeholder="Nome do produto" value="<?= htmlspecialchars($produtoEdicao['nome'] ?? '') ?>">
                    <label class="form-label">Categoria</label>
                    <input class="form-control" name="categoria" required list="categorias" placeholder="Ex: Salgados Fritos" value="<?= htmlspecialchars($produtoEdicao['categoria'] ?? '') ?>">
                    <datalist id="categorias"><option value="Salgados Fritos"><option value="Salgados Assados"><option value="Doces"><option value="Bebidas"></datalist>
                    <div style="display:flex;gap:10px;">
                        <div style="flex:1;"><label class="form-label">Preço (R$)</label>
                        <input class="form-control" type="number" step="0.01" name="preco" required placeholder="0.00" value="<?= $produtoEdicao['preco'] ?? '' ?>"></div>
                        <div style="flex:1;"><label class="form-label">Tempo de preparo (min)</label>
                        <input class="form-control" type="number" name="tempo_preparo" required placeholder="Ex: 15" value="<?= $produtoEdicao['tempo_preparo'] ?? '' ?>"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Salvar</button></div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>