<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['admin']);

$tituloPagina = 'Usuários';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senhaHash = password_hash($_POST['senha'] ?: '123456', PASSWORD_DEFAULT);
    if (!empty($_POST['id'])) {
        if (!empty($_POST['senha'])) {
            $pdo->prepare("UPDATE usuarios SET nome=?, email=?, tipo=?, telefone=?, senha=? WHERE id=?")
                ->execute([$_POST['nome'], $_POST['email'], $_POST['tipo'], $_POST['telefone'], $senhaHash, $_POST['id']]);
        } else {
            $pdo->prepare("UPDATE usuarios SET nome=?, email=?, tipo=?, telefone=? WHERE id=?")
                ->execute([$_POST['nome'], $_POST['email'], $_POST['tipo'], $_POST['telefone'], $_POST['id']]);
        }
    } else {
        $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo, telefone) VALUES (?,?,?,?,?)")
            ->execute([$_POST['nome'], $_POST['email'], $senhaHash, $_POST['tipo'], $_POST['telefone']]);
    }
    header('Location: usuarios.php');
    exit;
}

if (isset($_GET['excluir'])) {
    $pdo->prepare("UPDATE usuarios SET ativo = 0 WHERE id = ?")->execute([$_GET['excluir']]);
    header('Location: usuarios.php');
    exit;
}

$usuarioEdicao = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $usuarioEdicao = $stmt->fetch();
}

$usuarios = $pdo->query("SELECT * FROM usuarios WHERE ativo = 1 ORDER BY tipo, nome")->fetchAll();
$tiposLabel = ['admin'=>'Administrador','atendente'=>'Atendente','producao'=>'Produção','entregador'=>'Entregador'];

require __DIR__ . '/../includes/header.php';
?>

<div class="gf-panel" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h3 style="margin-bottom:2px;">Usuários</h3>
        <span style="color:#6b7280;font-size:13.5px;">Cadastre atendentes, produção, entregadores e administradores</span>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario"><i class="bi bi-plus-lg"></i> Novo Usuário</button>
</div>

<div class="gf-panel">
    <table class="gf-table">
        <thead><tr><th>Nome</th><th>E-mail</th><th>Tipo</th><th>Telefone</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><strong><?= htmlspecialchars($u['nome']) ?></strong></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge-status badge-em_entrega"><?= $tiposLabel[$u['tipo']] ?></span></td>
                <td><?= htmlspecialchars($u['telefone']) ?></td>
                <td>
                    <a href="?editar=<?= $u['id'] ?>" class="btn btn-light-orange" data-bs-toggle="modal" data-bs-target="#modalUsuario" title="Editar"><i class="bi bi-pencil-square"></i></a>
                    <a href="?excluir=<?= $u['id'] ?>" class="btn btn-light-red" data-confirm="Desativar este usuário?" title="Desativar"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade <?= $usuarioEdicao ? 'show' : '' ?>" id="modalUsuario" tabindex="-1" style="<?= $usuarioEdicao ? 'display:block;background:rgba(0,0,0,.5);' : '' ?>">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px;">
            <form method="post">
                <div class="modal-header"><h5 class="modal-title"><?= $usuarioEdicao ? 'Editar' : 'Novo' ?> Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="window.location='usuarios.php'"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?= $usuarioEdicao['id'] ?? '' ?>">
                    <label class="form-label">Nome</label>
                    <input class="form-control" name="nome" required placeholder="Nome completo" value="<?= htmlspecialchars($usuarioEdicao['nome'] ?? '') ?>">
                    <label class="form-label">E-mail</label>
                    <input class="form-control" type="email" name="email" required placeholder="seu@email.com" value="<?= htmlspecialchars($usuarioEdicao['email'] ?? '') ?>">
                    <label class="form-label">Tipo</label>
                    <select class="form-select" name="tipo" required>
                        <?php foreach ($tiposLabel as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($usuarioEdicao['tipo'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="form-label">Telefone</label>
                    <input class="form-control" name="telefone" placeholder="(XX) 9XXXX-XXXX" value="<?= htmlspecialchars($usuarioEdicao['telefone'] ?? '') ?>">
                    <label class="form-label">Senha <?= $usuarioEdicao ? '(deixe em branco para manter)' : '' ?></label>
                    <div class="campo-senha-wrap">
                        <input class="form-control" type="password" name="senha" id="senhaUsuario" placeholder="Padrão: 123456">
                        <button type="button" class="toggle-senha" onclick="alternarSenha('senhaUsuario', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Salvar</button></div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
