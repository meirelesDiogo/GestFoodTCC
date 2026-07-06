<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/conexao.php';
protegerPagina(['atendente']);

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
    SELECT p.*, c.nome AS cliente, c.endereco, c.numero, c.bairro, c.cidade
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
            <?php if (!$clientes): ?>
                <p style="color:#999;font-size:13px;">Nenhum cliente cadastrado ainda. Peça para o cliente se cadastrar pelo próprio link de pedido, ou peça ao administrador para cadastrá-lo.</p>
            <?php endif; ?>
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
    <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
        <form method="get" style="display:inline-flex;gap:6px;align-items:center;margin:0;">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <select name="status" class="form-select" style="margin:0;padding:6px 8px;font-size:12.5px;" onchange="this.form.submit()">
                <?php foreach ($statusLabels as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $p['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </form>

       <?php if ($p['status'] === 'em_entrega' && $p['entregador_id']): ?>
    <button type="button" class="btn btn-outline btn-rastrear"
        data-pedido="<?= $p['id'] ?>"
        data-endereco="<?= htmlspecialchars($p['endereco'] . ', ' . $p['numero'] . ', ' . $p['bairro'] . ', ' . $p['cidade'] . ', MG') ?>"
        style="padding:6px 10px;font-size:12.5px;">
        <i class="bi bi-geo-alt-fill"></i> Rastrear
    </button>
<?php endif; ?>
    </div>
</td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$pedidos): ?><tr><td colspan="5" style="text-align:center;color:#999;">Nenhum pedido registrado.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?><style>
.gf-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.gf-modal {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    width: 90%;
    max-width: 600px;
}
</style>

<div id="modalRastreio" class="gf-modal-overlay">
    <div class="gf-modal">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <h3 style="margin:0;"><i class="bi bi-geo-alt-fill"></i> Rastreando entrega</h3>
            <button type="button" id="fecharModalRastreio" class="btn btn-outline">&times;</button>
        </div>
        <div id="statusRastreio" style="margin-bottom:10px;color:#6b7280;font-size:13.5px;"></div>
        <div id="mapaRastreio" style="height:400px;border-radius:10px;"></div>
    </div>
</div>
<style>
.gf-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.gf-modal {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    width: 90%;
    max-width: 600px;
}
</style>

<div id="modalRastreio" class="gf-modal-overlay">
    <div class="gf-modal">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <h3 style="margin:0;"><i class="bi bi-geo-alt-fill"></i> Rastreando entrega</h3>
            <button type="button" id="fecharModalRastreio" class="btn btn-outline">&times;</button>
        </div>
        <div id="statusRastreio" style="margin-bottom:10px;color:#6b7280;font-size:13.5px;"></div>
        <div id="mapaRastreio" style="height:400px;border-radius:10px;"></div>
    </div>
</div>

<script>
let mapaRastreio = null;
let intervaloMotoboy = null;
let marcadorMotoboy = null;

async function geocodeRastreio(endereco) {
    const resposta = await fetch('../api/geocode.php?endereco=' + encodeURIComponent(endereco));
    const dados = await resposta.json();
    if (!dados.features.length) return null;
    return dados.features[0].geometry.coordinates; // [lng, lat]
}

async function abrirRastreio(pedidoId, enderecoCliente) {
    document.getElementById('modalRastreio').style.display = 'flex';
    document.getElementById('statusRastreio').textContent = 'Carregando rota...';

    if (mapaRastreio) {
        mapaRastreio.remove();
        mapaRastreio = null;
    }

    if (intervaloMotoboy) {
        clearInterval(intervaloMotoboy);
        intervaloMotoboy = null;
    }

    mapaRastreio = L.map('mapaRastreio');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(mapaRastreio);

    const loja = await geocodeRastreio('Rua Rio de Janeiro, 471, Centro, Belo Horizonte, MG');
    const cliente = await geocodeRastreio(enderecoCliente);

    if (!loja || !cliente) {
        document.getElementById('statusRastreio').textContent = 'Não foi possível localizar o endereço.';
        return;
    }

    const respostaRota = await fetch('../api/rota.php?origem=' + loja.join(',') + '&destino=' + cliente.join(','));
    const rota = await respostaRota.json();

    if (!rota.features || !rota.features.length) {
        document.getElementById('statusRastreio').textContent = 'Não foi possível calcular a rota.';
        return;
    }

    const coordenadas = rota.features[0].geometry.coordinates;
    const pontos = coordenadas.map(c => [c[1], c[0]]);

    const linha = L.polyline(pontos, { color: '#2563eb', weight: 5 }).addTo(mapaRastreio);

    const iconeLoja = L.divIcon({
        className: 'marcador-loja',
        html: '<div style="background:#f97316;width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.35);border:2px solid #fff;"><span style="font-size:15px;">🏪</span></div>',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });

    const iconeCliente = L.divIcon({
        className: 'marcador-cliente',
        html: '<div style="background:#2563eb;width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.35);border:2px solid #fff;"><span style="font-size:15px;">📍</span></div>',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });

    L.marker([loja[1], loja[0]], { icon: iconeLoja }).addTo(mapaRastreio).bindPopup('🏪 Lanchonete');
    L.marker([cliente[1], cliente[0]], { icon: iconeCliente }).addTo(mapaRastreio).bindPopup('📍 Cliente');

    // Ícone do motoboy: começa perto da lanchonete e vai avançando pela rota
    const iconeMotoboy = L.divIcon({
        className: 'marcador-motoboy',
        html: '<div style="background:#16a34a;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.35);border:2px solid #fff;"><span style="font-size:17px;">🛵</span></div>',
        iconSize: [34, 34],
        iconAnchor: [17, 17]
    });

    let indiceMotoboy = Math.floor(pontos.length * 0.15);
    marcadorMotoboy = L.marker(pontos[indiceMotoboy], { icon: iconeMotoboy }).addTo(mapaRastreio).bindPopup('🛵 Entregador');

    intervaloMotoboy = setInterval(() => {
        indiceMotoboy++;

        // Ao chegar perto do destino, volta pro início pra continuar a demonstração
        if (indiceMotoboy >= pontos.length - 2) {
            indiceMotoboy = Math.floor(pontos.length * 0.15);
        }

        marcadorMotoboy.setLatLng(pontos[indiceMotoboy]);
    }, 1200); // avança um ponto a cada 1.2s

    mapaRastreio.fitBounds(linha.getBounds(), { padding: [30, 30] });

    const distancia = (rota.features[0].properties.summary.distance / 1000).toFixed(2);
    const tempo = Math.round(rota.features[0].properties.summary.duration / 60);

    document.getElementById('statusRastreio').textContent =
        `Distância total: ${distancia} km · Tempo estimado: ${tempo} min`;
}

function fecharRastreio() {
    document.getElementById('modalRastreio').style.display = 'none';
    if (intervaloMotoboy) {
        clearInterval(intervaloMotoboy);
        intervaloMotoboy = null;
    }
}

document.getElementById('fecharModalRastreio').addEventListener('click', fecharRastreio);

document.querySelectorAll('.btn-rastrear').forEach(botao => {
    botao.addEventListener('click', () => {
        abrirRastreio(botao.dataset.pedido, botao.dataset.endereco);
    });
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>