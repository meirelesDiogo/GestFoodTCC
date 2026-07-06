<?php
// Este arquivo existia duplicado com dashboard.php e com caminhos de include quebrados
// (usava __DIR__.'/includes/...' em vez de __DIR__.'/../includes/...').
// Corrigido para apenas redirecionar para o dashboard real do admin.
header('Location: dashboard.php');
exit;
