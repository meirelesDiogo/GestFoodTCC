<?php

ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../config/conexao.php';

header('Content-Type: application/json');

$origem = $_GET['origem'] ?? '';
$destino = $_GET['destino'] ?? '';

if (!$origem || !$destino) {
    http_response_code(400);
    echo json_encode(["erro" => "Origem ou destino não informados."]);
    exit;
}

$apiKey = $_ENV['ORS_API_KEY'] ?? null;

if (!$apiKey) {
    http_response_code(500);
    echo json_encode(["erro" => "Chave da API ORS não configurada no servidor (verifique o .env)."]);
    exit;
}

$coordOrigem = explode(',', $origem);
$coordDestino = explode(',', $destino);

if (count($coordOrigem) !== 2 || count($coordDestino) !== 2 ||
    !is_numeric($coordOrigem[0]) || !is_numeric($coordOrigem[1]) ||
    !is_numeric($coordDestino[0]) || !is_numeric($coordDestino[1])) {
    http_response_code(400);
    echo json_encode(["erro" => "Coordenadas inválidas."]);
    exit;
}

$dados = [
    "coordinates" => [
        array_map('floatval', $coordOrigem),
        array_map('floatval', $coordDestino)
    ]
];

$ch = curl_init("https://api.openrouteservice.org/v2/directions/driving-car/geojson");

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: " . $apiKey,
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($dados),
    CURLOPT_TIMEOUT => 15
]);

$resposta = curl_exec($ch);
$erroCurl = curl_error($ch);
$statusHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($erroCurl) {
    http_response_code(502);
    echo json_encode(["erro" => "Falha ao contatar a API de rotas: " . $erroCurl]);
    exit;
}

if ($statusHttp !== 200) {
    http_response_code($statusHttp);
    echo json_encode([
        "erro" => "A API de rotas retornou um erro.",
        "detalhe" => json_decode($resposta, true)
    ]);
    exit;
}

echo $resposta;