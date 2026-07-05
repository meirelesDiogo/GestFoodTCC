<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO clientes (nome, telefone, cep, endereco, numero, complemento, bairro, cidade, estado) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $_POST['nome'], $_POST['telefone'], $_POST['cep'] ?: null, $_POST['endereco'] ?: null,
        $_POST['numero'] ?: null, $_POST['complemento'] ?: null, $_POST['bairro'] ?: null,
        $_POST['cidade'] ?: null, $_POST['estado'] ?: null,
    ]);
    $_SESSION['usuario_id']   = $pdo->lastInsertId();
    $_SESSION['usuario_nome'] = $_POST['nome'];
    $_SESSION['usuario_tipo'] = 'cliente';
    header('Location: fazer_pedido.php');
    exit;
}

$telefone = $_GET['telefone'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro · GestFood</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= urlPara('assets/css/style.css') ?>">
</head>
<body>
<div class="login-page">
    <div class="login-card" style="max-width:480px;">
        <div class="login-card__header">
            <div class="login-card__icon"><i class="bi bi-egg-fried"></i></div>
            <h1>GestFood</h1>
            <p>Primeiro pedido? Complete seu cadastro</p>
        </div>
        <div class="login-card__body">
            <form method="post">
                <label class="form-label">Nome completo</label>
                <input class="form-control" name="nome" required placeholder="Seu nome completo">
                <label class="form-label">Telefone</label>
                <input class="form-control" id="telefone" name="telefone" type="tel" inputmode="numeric" data-mascara-telefone="true" required placeholder="(XX) 9XXXX-XXXX" value="<?= htmlspecialchars($telefone) ?>">
                <script>
                const telefone = document.getElementById("telefone");
                if (telefone) {
                    telefone.addEventListener("input", function (e) {
                        let valor = e.target.value.replace(/\D/g, "");
                        if (valor.length > 11) valor = valor.slice(0, 11);
                        if (valor.length > 10) {
                            valor = valor.replace(/^(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
                        } else {
                            valor = valor.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, "($1) $2-$3");
                        }
                        e.target.value = valor;
                    });
                }
                </script>
                <label class="form-label">CEP</label>
                <input class="form-control" id="cep" name="cep" maxlength="9" placeholder="12345-678" onblur="buscarCep('cep',{endereco:'enderecoNovo',bairro:'bairroNovo',cidade:'cidadeNovo',estado:'estadoNovo'})">
                <script>
                const cep = document.getElementById("cep");
                if (cep) {
                    cep.addEventListener("input", function (e) {
                        let valor = e.target.value.replace(/\D/g, "");
                        if (valor.length > 8) valor = valor.slice(0, 8);
                        valor = valor.replace(/^(\d{5})(\d{0,3})$/, "$1-$2");
                        e.target.value = valor;
                    });
                }
                </script>
                <label class="form-label">Endereço</label>
                <input class="form-control" id="enderecoNovo" name="endereco" placeholder="Rua/Avenida...">
                <div style="display:flex;gap:10px;">
                    <div style="flex:1;"><label class="form-label">Número</label><input class="form-control" name="numero" placeholder="123"></div>
                    <div style="flex:2;"><label class="form-label">Complemento</label><input class="form-control" name="complemento" placeholder="Apto, Sala..."></div>
                </div>
                <label class="form-label">Bairro</label>
                <input class="form-control" id="bairroNovo" name="bairro" placeholder="Bairro">
                <div style="display:flex;gap:10px;">
                    <div style="flex:2;"><label class="form-label">Cidade</label><input class="form-control" id="cidadeNovo" name="cidade" placeholder="Cidade"></div>
                    <div style="flex:1;"><label class="form-label">UF</label><input class="form-control" id="estadoNovo" name="estado" placeholder="SP" maxlength="2"></div>
                </div>
                <button type="submit" class="btn-gf">Concluir Cadastro e Pedir</button>
            </form>
        </div>
    </div>
</div>
<script src="<?= urlPara('assets/js/script.js') ?>"></script>
</body>
</html>
