<?php
/**
 * Controle de sessão e permissões por tipo de usuário.
 */
session_start();

function usuarioLogado() {
    return isset($_SESSION['usuario_id']);
}

function tipoUsuario() {
    return $_SESSION['usuario_tipo'] ?? null;
}

function nomeUsuario() {
    return $_SESSION['usuario_nome'] ?? '';
}

/**
 * Garante que o usuário está logado e possui um dos tipos permitidos.
 * Caso contrário, redireciona para o login.
 */
function protegerPagina(array $tiposPermitidos) {
    if (!usuarioLogado() || !in_array(tipoUsuario(), $tiposPermitidos, true)) {
        header('Location: ' . urlPara('index.php'));
        exit;
    }
}

function pastaDoTipo($tipo) {
    $mapa = [
        'admin'      => 'admin',
        'atendente'  => 'atendente',
        'producao'   => 'producao',
        'entregador' => 'entregador',
        'cliente'    => 'cliente',
    ];
    return $mapa[$tipo] ?? '';
}

/**
 * Retorna a URL do dashboard correspondente ao tipo de usuário.
 */
function dashboardDoTipo($tipo) {
    $pasta = pastaDoTipo($tipo);
    if ($pasta === 'cliente') {
        return $pasta . '/fazer_pedido.php';
    }
    return $pasta ? $pasta . '/dashboard.php' : 'index.php';
}

function baseUrl() {
    // A pasta deste arquivo é sempre {raiz_do_projeto}/includes, então subimos
    // um nível para achar a raiz real do projeto (independente de qual página
    // /admin, /cliente, /producao ou /entregador chamou esta função).
    $raizProjeto = realpath(__DIR__ . '/..');
    $docRoot     = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');

    if ($raizProjeto && $docRoot && str_starts_with($raizProjeto, $docRoot)) {
        $base = substr($raizProjeto, strlen($docRoot));
        $base = str_replace('\\', '/', $base);
        return rtrim($base, '/');
    }

    // Fallback (ex.: DOCUMENT_ROOT não disponível, CLI, etc.)
    return '';
}

/**
 * Monta uma URL absoluta (a partir da raiz do site) para o caminho informado.
 * Usar sempre esta função em vez de caminhos fixos como "/admin/..." para que
 * o sistema funcione tanto na raiz do domínio quanto em subpastas (ex: /GestFood/).
 */
function urlPara($caminho) {
    $caminho = ltrim($caminho, '/');
    $base = baseUrl();
    return ($base ? $base . '/' : '/') . $caminho;
}
