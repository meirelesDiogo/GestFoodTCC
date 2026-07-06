# GestFood — Sistema de Automação de Pedidos (X Salgados)

Sistema completo de gestão de pedidos, produção e entregas, desenvolvido em **PHP + MySQL** com front-end em **HTML5, CSS3, Bootstrap 5, Bootstrap Icons e JavaScript**, seguindo a paleta de cores (laranja e branco) do protótipo original.

## Paleta de cores

| Cor | Uso | Hex |
|---|---|---|
| Laranja escuro | Gradiente do login / topo da sidebar | `#9a3a25` |
| Laranja principal | Sidebar, botões primários | `#e2601f` |
| Marrom-avermelhado | Botão "Entrar no Sistema" | `#8b3220` |
| Branco | Cards e áreas de conteúdo | `#ffffff` |
| Cinza claro | Fundo das telas internas | `#f4f5f7` |

Todas as variáveis estão centralizadas em `assets/css/style.css` (`:root`).

## Como rodar (XAMPP / WAMP / Laragon)

1. Copie a pasta `GestFood/` para `htdocs` (XAMPP) ou `www` (WAMP/Laragon) — pode ser direto na raiz ou em uma subpasta, os caminhos do sistema já se ajustam sozinhos (veja "Correções aplicadas" abaixo).
2. Crie o banco de dados importando o arquivo `banco/gestfood.sql` (via phpMyAdmin ou linha de comando):
   ```
   mysql -u root -p < banco/gestfood.sql
   ```
3. Ajuste as credenciais em `config/conexao.php` se necessário (usuário/senha do MySQL).
4. Acesse `http://localhost/GestFood/` (ou a URL correspondente) no navegador.

## Login de demonstração

O login aceita **qualquer senha** — o campo existe na interface, com botão de mostrar/ocultar (ícone de olho), mas em modo demonstração o sistema autentica apenas pelo e-mail/telefone e tipo de usuário selecionado.

| Tipo | E-mail |
|---|---|
| Administrador | admin@gestfood.com |
| Atendente | atendente@gestfood.com |
| Produção | producao@gestfood.com |
| Entregador | entregador@gestfood.com |
| Cliente | Faça login informando um telefone novo — o cadastro é criado automaticamente. |

## Estrutura de pastas

```
GestFood/
├── index.php                → Tela de login (adicionada — não estava nos arquivos originais)
├── logout.php
├── config/conexao.php        → Conexão PDO com o MySQL
├── assets/{css,js}            → Estilos e scripts
├── includes/                  → header, footer, menu e autenticação (auth.php)
├── admin/                      → Dashboard, Clientes, Produtos, Pedidos, Produção, Entregas, Relatórios, Usuários
├── atendente/                   → (ver observação abaixo)
├── producao/                     → Dashboard, Fila de Produção
├── entregador/                    → Dashboard, Entregas, Rotas (com link para Google Maps)
├── cliente/                        → Fazer Pedido, Meus Pedidos, Perfil, Cadastro
└── banco/gestfood.sql               → Schema completo + dados de exemplo
```

## Correções aplicadas nesta revisão

- **Caminhos quebrados corrigidos:**
  - `logout.php` e `menu.php` usavam `header('Location: /index.php')` e `href="/logout.php"` (caminho absoluto a partir da raiz do domínio). Isso quebra o sistema quando instalado em uma subpasta (ex.: `htdocs/GestFood/`). Agora usam caminho relativo / a função `urlPara()` já existente em `auth.php`.
  - `cliente/cadastro.php` usava `/assets/css/style.css`, `/assets/js/script.js` e `header('Location: /cliente/fazer_pedido.php')` (absolutos). Corrigido para usar `urlPara()` e caminho relativo, no mesmo padrão das demais páginas.
  - O arquivo `index.php` enviado era, na prática, uma cópia de `admin/dashboard.php`, mas usava `require __DIR__.'/includes/...'` em vez de `require __DIR__.'/../includes/...'` — ou seja, tinha os requires errados e duplicava a lógica do dashboard. Foi transformado em `admin/index.php`, que agora apenas redireciona para `admin/dashboard.php` (arquivo com os caminhos corretos).
  - Adicionada a tela de login (`index.php` da raiz), referenciada por `logout.php`, `auth.php` e pelo próprio README, mas que não estava entre os arquivos enviados.
- **Emojis removidos:** todos os emojis (relógio, cronômetro, localização, telefone, caminhão, seta de download etc.) foram substituídos por ícones do **Bootstrap Icons** (`bi bi-*`), carregado via CDN em `includes/header.php` e nas páginas de login/cadastro.
- **Ícones no lugar dos textos entre colchetes:** os placeholders de texto como `[Edit]`, `[Delete]`, `[Info]`, `[Dashboard]` etc. também foram trocados por ícones reais (menu lateral, botões de ação, KPIs).
- **Placeholders adicionados** em todos os campos que ainda não tinham (ex.: `cliente/perfil.php` estava sem placeholder em quase todos os campos de endereço).
- **Senha mascarada com botão de mostrar/ocultar** (ícone de olho, `bi-eye` / `bi-eye-slash`) na tela de login e no cadastro/edição de usuários (`admin/usuarios.php`), via a função `alternarSenha()` em `assets/js/script.js`.

## Observação sobre o perfil "Atendente"

O menu lateral (`includes/menu.php`) referencia `atendente/pedidos.php` e `atendente/cupons.php`, mas esses arquivos não foram enviados nesta leva — apenas os arquivos de `admin/`, `producao/`, `entregador/` e `cliente/`. As páginas `admin/clientes.php` e `admin/pedidos.php` já liberam acesso ao perfil `atendente` (`protegerPagina(['admin', 'atendente'])`), então, por ora, o atendente pode usar essas mesmas páginas. Se você tiver os arquivos originais de `atendente/`, me envie que eu aplico as mesmas correções.

## Funcionalidades implementadas

- **Login por perfil** (Administrador, Atendente, Produção, Entregador, Cliente), com sessão PHP.
- **Clientes**: CRUD completo, com consulta automática de CEP (API ViaCEP) no formulário.
- **Produtos**: CRUD completo com preço e tempo de preparo.
- **Pedidos**: criação com carrinho dinâmico (JS), cálculo automático de total, alteração de status.
- **Produção**: fila de pedidos aguardando/em produção, com botões "Iniciar Produção" e "Finalizar".
- **Entregas**: pedidos prontos, aceite/início/conclusão de entrega, organização por bairro, rota com link direto ao Google Maps.
- **Relatórios**: KPIs (faturamento, ticket médio, taxa de entrega), gráficos (Chart.js) e ranking de produtos.
- **Usuários**: cadastro de funcionários com definição de tipo/papel, senha mascarada com opção de mostrar/ocultar.

## Requisitos

- PHP 8.0+ (usa `match()`)
- MySQL 5.7+ / MariaDB
- Extensão PDO MySQL habilitada

## Próximos passos sugeridos

- Trocar a autenticação demo por verificação real de senha (`password_verify`), já que o hash de senha já é gerado corretamente em `admin/usuarios.php`.
- Enviar os arquivos de `atendente/` (dashboard, pedidos, cupons) para aplicar as mesmas correções de caminho/ícones/placeholder.
- Adicionar upload de imagem dos produtos.
- Integrar API de mapas para rota otimizada com múltiplas paradas (o link atual abre uma parada por vez no Google Maps).
