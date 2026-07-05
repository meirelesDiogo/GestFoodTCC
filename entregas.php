<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['admin', 'atendente']);

$tituloPagina = 'Clientes';

// --- Ações: criar / editar / excluir ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        $_POST['nome'], $_POST['telefone'], $_POST['cep'] ?: null,
        $_POST['endereco'] ?: null, $_POST['numero'] ?: null, $_POST['complemento'] ?: null,
        $_POST['bairro'] ?: null, $_POST['cidade'] ?: null, $_POST['estado'] ?: null,
        $_POST['observacoes'] ?: null,
    ];
    if (!empty($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE clientes SET nome=?, telefone=?, cep=?, endereco=?, numero=?, complemento=?, bairro=?, cidade=?, estado=?, observacoes=? WHERE id=?");
        $stmt->execute([...$dados, $_POST['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO clientes (nome, telefone, cep, endereco, numero, complemento, bairro, cidade, estado, observacoes) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute($dados);
    }
    header('Location: clientes.php');
    exit;
}

if (isset($_GET['excluir'])) {
    $pdo->prepare("DELETE FROM clientes WHERE id = ?")->execute([$_GET['excluir']]);
    header('Location: clientes.php');
    exit;
}

$clienteEdicao = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $clienteEdicao = $stmt->fetch();
}

$clientes = $pdo->query("SELECT * FROM clientes ORDER BY nome")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="gf-panel" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h3 style="margin-bottom:2px;">Clientes</h3>
        <span style="color:#6b7280;font-size:13.5px;">Gerencie o cadastro de clientes</span>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCliente"><i class="bi bi-plus-lg"></i> Novo Cliente</button>
</div>

<input type="text" class="gf-search" placeholder="Buscar por nome, telefone ou bairro..." data-busca="#tabelaClientes tbody tr">

<div class="gf-panel">
    <table class="gf-table" id="tabelaClientes">
        <thead>
            <tr><th>Cliente</th><th>Contato</th><th>Endereço</th><th>Bairro</th><th>Ações</th></tr>
        </thead>
        <tbody>
        <?php foreach ($clientes as $c): ?>
            <tr>
                <td><strong><?= htmlspecialchars($c['nome']) ?></strong><br>
                    <span style="color:#9aa0a6;font-size:12.5px;"><?= htmlspecialchars($c['observacoes']) ?></span></td>
                <td><i class="bi bi-telephone"></i> <?= htmlspecialchars($c['telefone']) ?></td>
                <td><?= htmlspecialchars($c['endereco']) ?><?= $c['numero'] ? ', '.htmlspecialchars($c['numero']) : '' ?></td>
                <td><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($c['bairro']) ?></td>
                <td>
                    <a href="?editar=<?= $c['id'] ?>" class="btn btn-light-orange" data-bs-toggle="modal" data-bs-target="#modalCliente" title="Editar"><i class="bi bi-pencil-square"></i></a>
                    <a href="?excluir=<?= $c['id'] ?>" class="btn btn-light-red" data-confirm="Excluir este cliente?" title="Excluir"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$clientes): ?><tr><td colspan="5" style="text-align:center;color:#999;">Nenhum cliente cadastrado.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Novo/Editar Cliente -->
<div class="modal fade <?= $clienteEdicao ? 'show' : '' ?>" id="modalCliente" tabindex="-1" style="<?= $clienteEdicao ? 'display:block;background:rgba(0,0,0,.5);' : '' ?>">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px;">
            <form method="post">
                <div class="modal-header"><h5 class="modal-title"><?= $clienteEdicao ? 'Editar' : 'Novo' ?> Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="window.location='clientes.php'"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?= $clienteEdicao['id'] ?? '' ?>">
                    <label class="form-label">Nome</label>
                    <input class="form-control" name="nome" required placeholder="Nome completo" value="<?= htmlspecialchars($clienteEdicao['nome'] ?? '') ?>">
                    <label class="form-label">Telefone</label>
                    <input class="form-control" name="telefone" required placeholder="(XX) 9XXXX-XXXX" value="<?= htmlspecialchars($clienteEdicao['telefone'] ?? '') ?>">
                    <label class="form-label">CEP</label>
                    <input class="form-control" id="cepCliente" name="cep" placeholder="12345-678" onblur="buscarCep('cepCliente',{endereco:'enderecoCliente',bairro:'bairroCliente',cidade:'cidadeCliente',estado:'estadoCliente'})" value="<?= htmlspecialchars($clienteEdicao['cep'] ?? '') ?>">
                    <label class="form-label">Endereço</label>
                    <input class="form-control" id="enderecoCliente" name="endereco" placeholder="Rua/Avenida..." value="<?= htmlspecialchars($clienteEdicao['endereco'] ?? '') ?>">
                    <div style="display:flex;gap:10px;">
                        <div style="flex:1;"><label class="form-label">Número</label>
                        <input class="form-control" name="numero" placeholder="123" value="<?= htmlspecialchars($clienteEdicao['numero'] ?? '') ?>"></div>
                        <div style="flex:2;"><label class="form-label">Complemento</label>
                        <input class="form-control" name="complemento" placeholder="Apto, Sala..." value="<?= htmlspecialchars($clienteEdicao['complemento'] ?? '') ?>"></div>
                    </div>
                    <label class="form-label">Bairro</label>
                    <input class="form-control" id="bairroCliente" name="bairro" placeholder="Bairro" value="<?= htmlspecialchars($clienteEdicao['bairro'] ?? '') ?>">
                    <div style="display:flex;gap:10px;">
                        <div style="flex:2;"><label class="form-label">Cidade</label>
                        <input class="form-control" id="cidadeCliente" name="cidade" placeholder="Cidade" value="<?= htmlspecialchars($clienteEdicao['cidade'] ?? '') ?>"></div>
                        <div style="flex:1;"><label class="form-label">UF</label>
                        <input class="form-control" id="estadoCliente" name="estado" placeholder="SP" maxlength="2" value="<?= htmlspecialchars($clienteEdicao['estado'] ?? '') ?>"></div>
                    </div>
                    <label class="form-label">Observações</label>
                    <input class="form-control" name="observacoes" placeholder="Notas adicionais" value="<?= htmlspecialchars($clienteEdicao['observacoes'] ?? '') ?>">
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Salvar</button></div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
