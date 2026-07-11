<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error 404 - Bet-El Creativa</title>
    <link rel="icon" href="<?php echo APP_URL; ?>Public/images/BetEl.png">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/404Style.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
</head>
<body>
    <div class="app-container">
        <main class="main-content" style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
            <div class="card" style="max-width: 500px; margin: 0 auto; text-align: center; padding: 40px;">
                <div style="font-size: 6rem; color: var(--primary); margin-bottom: 20px;">404</div>
                <h1 style="color: var(--primary); margin-bottom: 15px;">Página no encontrada</h1>
                <p style="color: var(--gray); margin-bottom: 30px;">La página que buscas no existe o ha sido movida.</p>
                <a href="<?php echo APP_URL; ?>dashboard" class="btn btn-primary">
                    <i class="fas fa-home"></i> Volver al Inicio
                </a>
            </div>
        </main>
    </div>
</body>
</html>
