<?php

require_once __DIR__ . '/../config/conexao.php';

header('Content-Type: application/json');

$origem = $_GET['origem'] ?? '';
$destino = $_GET['destino'] ?? '';

if (!$origem || !$destino) {
    http_response_code(400);
    echo json_encode([
        'erro' => 'Origem ou destino não informados.'
    ]);
    exit;
}

$url = "https://api.openrouteservice.org/v2/directions/driving-car";

$dados = [
    "coordinates" => [
        array_map('floatval', explode(',', $origem)),
        array_map('floatval', explode(',', $destino))
    ]
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: ' . $_ENV['ORS_API_KEY'],
        'Content-Type: application/json'
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($dados)
]);

$resposta = curl_exec($ch);

curl_close($ch);

echo $resposta;