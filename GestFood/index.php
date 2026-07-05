<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/config/conexao.php';

if (usuarioLogado()) {
    header('Location: ' . urlPara(dashboardDoTipo(tipoUsuario())));
    exit;
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'] ?? '';
    $identificador = trim($_POST['identificador'] ?? '');

    if ($tipo === 'cliente') {
        // Cliente: login pelo telefone. Se não existir, envia para o cadastro automático.
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE telefone = ?");
        $stmt->execute([$identificador]);
        $cliente = $stmt->fetch();

        if ($cliente) {
            $_SESSION['usuario_id']   = $cliente['id'];
            $_SESSION['usuario_nome'] = $cliente['nome'];
            $_SESSION['usuario_tipo'] = 'cliente';
            header('Location: ' . urlPara('cliente/fazer_pedido.php'));
            exit;
        }
        header('Location: ' . urlPara('cliente/cadastro.php?telefone=' . urlencode($identificador)));
        exit;
    }

    if ($tipo !== '') {
        // Demonstração: autentica apenas por e-mail + tipo (qualquer senha é aceita)
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND tipo = ? AND ativo = 1");
        $stmt->execute([$identificador, $tipo]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];
            header('Location: ' . urlPara(dashboardDoTipo($usuario['tipo'])));
            exit;
        }
        $erro = 'Usuário não encontrado para o tipo selecionado.';
    } else {
        $erro = 'Selecione o tipo de usuário.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar · GestFood</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= urlPara('assets/css/style.css') ?>">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="login-card__header">
            <div class="login-card__icon"><i class="bi bi-egg-fried"></i></div>
            <h1>GestFood</h1>
            <p>X Salgados · Sistema de Gestão</p>
        </div>
        <div class="login-card__body">
            <?php if ($erro): ?>
                <div style="background:#fde8e6;color:#c0392b;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13.5px;">
                    <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <label class="form-label">Tipo de Usuário</label>
                <select name="tipo" id="tipoUsuario" class="form-select" required onchange="atualizarCampoLogin()">
                    <option value="">Selecione o seu perfil de acesso...</option>
                    <option value="admin">Administrador</option>
                    <option value="atendente">Atendente</option>
                    <option value="producao">Produção</option>
                    <option value="entregador">Entregador</option>
                    <option value="cliente">Cliente</option>
                </select>

                <label class="form-label" id="labelIdentificador">E-mail</label>
                <input class="form-control" type="text" name="identificador" id="campoIdentificador"
                       required placeholder="seu@email.com">

                <label class="form-label">Senha</label>
                <div style="position:relative;">
                    <input class="form-control" type="password" name="senha" id="campoSenha"
                           placeholder="Digite sua senha" style="padding-right:44px;">
                    <button type="button" onclick="alternarSenha('campoSenha', this)"
                            style="position:absolute;right:10px;top:11px;background:none;border:none;color:#9aa0a6;cursor:pointer;font-size:16px;">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <button type="submit" class="btn-gf"><i class="bi bi-box-arrow-in-right"></i> Entrar no Sistema</button>
            </form>
            <div class="login-demo-note">
                <i class="bi bi-info-circle"></i>
                Modo demonstração: qualquer senha é aceita. Clientes fazem login informando o telefone;
                se for a primeira vez, o cadastro é criado automaticamente.
            </div>
        </div>
    </div>
</div>

<script src="<?= urlPara('assets/js/script.js') ?>"></script>
<script>
function atualizarCampoLogin() {
    const tipo = document.getElementById('tipoUsuario').value;
    const label = document.getElementById('labelIdentificador');
    const campo = document.getElementById('campoIdentificador');
    if (tipo === 'cliente') {
        label.textContent = 'Telefone';
        campo.placeholder = '(XX) 9XXXX-XXXX';
        campo.type = 'tel';
    } else {
        label.textContent = 'E-mail';
        campo.placeholder = 'seu@email.com';
        campo.type = 'text';
    }
}
</script>
</body>
</html>
