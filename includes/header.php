<?php
/**
 * Cabeçalho comum das páginas internas.
 * Espera $tituloPagina definido antes do include.
 */
$tituloPagina = $tituloPagina ?? 'GestFood';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?> · GestFood</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= urlPara('assets/css/style.css') ?>">
    <link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
</head>
<body>
<div class="gf-app">
    <?php include __DIR__ . '/menu.php'; ?>
    <main class="gf-main">
        <div class="gf-topbar">
            <div>
                <button id="sidebarToggle" class="btn btn-outline d-md-none" style="margin-bottom:8px;">
                    <i class="bi bi-list"></i> Menu
                </button>
                <h1><?= htmlspecialchars($tituloPagina) ?></h1>
            </div>
            <?php
                $diasSemana = ['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'];
                $diaExtenso = $diasSemana[date('w')] . ', ' . date('d/m/Y');
            ?>
            <div class="data-atual"><i class="bi bi-calendar3"></i> <?= $diaExtenso ?></div>
        </div>
