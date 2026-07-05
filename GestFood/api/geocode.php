<?php

require_once __DIR__ . '/../config/conexao.php';

header('Content-Type: application/json');

$endereco = $_GET['endereco'] ?? '';

if (empty($endereco)) {
    http_response_code(400);
    echo json_encode([
        "erro" => "Endereço não informado."
    ]);
    exit;
}
$url = "https://api.openrouteservice.org/geocode/search?api_key="
    . urlencode($_ENV['ORS_API_KEY'])
    . "&text=" . urlencode($endereco)
    . "&layers=address"
    . "&size=1";

$resposta = file_get_contents($url);

echo $resposta;