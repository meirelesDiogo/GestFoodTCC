// =====================================================
// GestFood - Script geral
// =====================================================

// Abrir/fechar sidebar no mobile
function aplicarMascaraTelefone(input) {
    if (!input) return;

    const formatar = () => {
        const numeros = input.value.replace(/\D/g, '').slice(0, 11);
        let valorFormatado = '';

        if (!numeros) {
            input.value = '';
            return;
        }

        valorFormatado = `(${numeros.slice(0, 2)}) `;

        if (numeros.length <= 10) {
            valorFormatado += numeros.slice(2, 6);
            if (numeros.length > 6) {
                valorFormatado += `-${numeros.slice(6, 10)}`;
            }
        } else {
            valorFormatado += numeros.slice(2, 7);
            if (numeros.length > 7) {
                valorFormatado += `-${numeros.slice(7, 11)}`;
            }
        }

        input.value = valorFormatado;
    };

    if (!input.dataset.mascaraTelefoneInicializada) {
        input.addEventListener('input', formatar);
        input.addEventListener('blur', formatar);
        input.dataset.mascaraTelefoneInicializada = 'true';
    }

    formatar();
}

document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('gfSidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
    }

    document.querySelectorAll('input[name="telefone"], input[data-mascara-telefone]').forEach(aplicarMascaraTelefone);

    // Filtro de busca simples (clientes / produtos)
    const buscaInput = document.querySelector('[data-busca]');
    if (buscaInput) {
        buscaInput.addEventListener('input', function () {
            const termo = this.value.toLowerCase();
            const alvo = document.querySelectorAll(this.dataset.busca);
            alvo.forEach(el => {
                const texto = el.textContent.toLowerCase();
                el.style.display = texto.includes(termo) ? '' : 'none';
            });
        });
    }

    // Confirmação antes de excluir
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Tem certeza?')) {
                e.preventDefault();
            }
        });
    });
});

// -----------------------------------------------------
// Mostrar/ocultar senha (usado no login e cadastro de usuários)
// -----------------------------------------------------
function alternarSenha(inputId, botao) {
    const campo = document.getElementById(inputId);
    const icone = botao.querySelector('i');
    if (!campo) return;

    if (campo.type === 'password') {
        campo.type = 'text';
        icone.classList.remove('bi-eye');
        icone.classList.add('bi-eye-slash');
    } else {
        campo.type = 'password';
        icone.classList.remove('bi-eye-slash');
        icone.classList.add('bi-eye');
    }
}

// -----------------------------------------------------
// Consulta de CEP (ViaCEP) - usado em cliente/fazer_pedido.php
// -----------------------------------------------------
function buscarCep(cepInputId, alvo) {
    const cep = document.getElementById(cepInputId).value.replace(/\D/g, '');
    if (cep.length !== 8) return;

    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(res => res.json())
        .then(data => {
            if (data.erro) return;
            if (alvo.endereco) document.getElementById(alvo.endereco).value = data.logradouro || '';
            if (alvo.bairro) document.getElementById(alvo.bairro).value = data.bairro || '';
            if (alvo.cidade) document.getElementById(alvo.cidade).value = data.localidade || '';
            if (alvo.estado) document.getElementById(alvo.estado).value = data.uf || '';
        })
        .catch(() => console.warn('Não foi possível consultar o CEP.'));
}

// -----------------------------------------------------
// Montagem do carrinho no formulário de novo pedido
// -----------------------------------------------------
const carrinho = {};

function adicionarProduto(id, nome, preco) {
    if (!carrinho[id]) carrinho[id] = { nome, preco, qtd: 0 };
    carrinho[id].qtd += 1;
    atualizarResumoPedido();
}

function removerProduto(id) {
    if (carrinho[id] && carrinho[id].qtd > 0) {
        carrinho[id].qtd -= 1;
        if (carrinho[id].qtd === 0) delete carrinho[id];
    }
    atualizarResumoPedido();
}

function atualizarResumoPedido() {
    const listaEl = document.getElementById('resumoItens');
    const totalEl = document.getElementById('resumoTotal');
    const qtdEl = document.getElementById('resumoQtd');
    const inputEl = document.getElementById('itensPedidoInput');
    if (!listaEl) return;

    let total = 0, qtd = 0, html = '';
    Object.entries(carrinho).forEach(([id, item]) => {
        const subtotal = item.qtd * item.preco;
        total += subtotal;
        qtd += item.qtd;
        html += `<div class="d-flex justify-content-between" style="padding:4px 0;font-size:13.5px;">
                    <span>${item.qtd}x ${item.nome}</span><span>R$ ${subtotal.toFixed(2)}</span>
                  </div>`;
    });

    listaEl.innerHTML = html || '<span style="color:#999">Nenhum item selecionado</span>';
    totalEl.textContent = 'R$ ' + total.toFixed(2);
    qtdEl.textContent = qtd;
    if (inputEl) inputEl.value = JSON.stringify(carrinho);
}
