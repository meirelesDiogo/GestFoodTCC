<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['cliente']);

$tituloPagina = 'Meu Perfil';
$clienteId = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE clientes SET nome=?, telefone=?, cep=?, endereco=?, numero=?, complemento=?, bairro=?, cidade=?, estado=? WHERE id=?");
    $stmt->execute([
        $_POST['nome'], $_POST['telefone'], $_POST['cep'] ?: null, $_POST['endereco'] ?: null,
        $_POST['numero'] ?: null, $_POST['complemento'] ?: null, $_POST['bairro'] ?: null,
        $_POST['cidade'] ?: null, $_POST['estado'] ?: null, $clienteId,
    ]);
    $_SESSION['usuario_nome'] = $_POST['nome'];
}

$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$clienteId]);
$cliente = $stmt->fetch();

require __DIR__ . '/../includes/header.php';
?>

<div class="gf-panel" style="max-width:560px;">
    <h3>Meus Dados</h3>
    <form method="post">
        <label class="form-label">Nome</label>
        <input class="form-control" name="nome" required placeholder="Seu nome completo" value="<?= htmlspecialchars($cliente['nome']) ?>">
        <label class="form-label">Telefone</label>
        <input class="form-control" name="telefone" required placeholder="(XX) 9XXXX-XXXX" value="<?= htmlspecialchars($cliente['telefone']) ?>">
        <label class="form-label">CEP</label>
        <input class="form-control" id="cepPerfil" name="cep" placeholder="12345-678" onblur="buscarCep('cepPerfil',{endereco:'enderecoPerfil',bairro:'bairroPerfil',cidade:'cidadePerfil',estado:'estadoPerfil'})" value="<?= htmlspecialchars($cliente['cep'] ?? '') ?>">
        <label class="form-label">Endereço</label>
        <input class="form-control" id="enderecoPerfil" name="endereco" placeholder="Rua/Avenida..." value="<?= htmlspecialchars($cliente['endereco'] ?? '') ?>">
        <div style="display:flex;gap:10px;">
            <div style="flex:1;"><label class="form-label">Número</label><input class="form-control" name="numero" placeholder="123" value="<?= htmlspecialchars($cliente['numero'] ?? '') ?>"></div>
            <div style="flex:2;"><label class="form-label">Complemento</label><input class="form-control" name="complemento" placeholder="Apto, Sala..." value="<?= htmlspecialchars($cliente['complemento'] ?? '') ?>"></div>
        </div>
        <label class="form-label">Bairro</label>
        <input class="form-control" id="bairroPerfil" name="bairro" placeholder="Bairro" value="<?= htmlspecialchars($cliente['bairro'] ?? '') ?>">
        <div style="display:flex;gap:10px;">
            <div style="flex:2;"><label class="form-label">Cidade</label><input class="form-control" id="cidadePerfil" name="cidade" placeholder="Cidade" value="<?= htmlspecialchars($cliente['cidade'] ?? '') ?>"></div>
            <div style="flex:1;"><label class="form-label">UF</label><input class="form-control" id="estadoPerfil" name="estado" placeholder="SP" maxlength="2" value="<?= htmlspecialchars($cliente['estado'] ?? '') ?>"></div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar Alterações</button>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
