<?php
// Views/UserInsD/header.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Instituciones - GameOn Network</title>
    
    <!-- CSS separados para InstDepor -->
    <link rel="stylesheet" href="../../Public/cssInsDepor/header_insd.css">
    <link rel="stylesheet" href="../../Public/cssInsDepor/dashboard_insd.css">
    <link rel="stylesheet" href="../../Public/cssInsDepor/footer_insd.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="main-header-inst">
        <div class="logo-inst">
            <img src="../../Resources/logo_gameon.png" alt="GameOn Network">
            <span class="logo-text">Instituciones Deportivas</span>
        </div>
        <nav class="main-nav-inst">
            <ul>
                <li><a href="dashboard.php">DASHBOARD</a></li>
                <li><a href="instalaciones_deportivas.php">INSTALACIONES DEPORTIVAS</a></li>
                <li><a href="areas_deportivas.php">ÁREAS DEPORTIVAS</a></li>
                <li><a href="reservas.php">RESERVAS</a></li>
                <li><a href="torneos.php">TORNEOS</a></li>
            </ul>
        </nav>
        <div class="header-user-inst">
            <div class="user-info-inst">
                <span class="user-welcome-inst">¡Hola, <?php echo $_SESSION['username']; ?>!</span>
                <span class="user-type-inst">Institución Deportiva</span>
            </div>
            <div class="logout-container-inst">
                <a href="../Auth/logout.php" class="logout-btn-inst" onclick="return confirm('¿Estás seguro que deseas cerrar sesión?')">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </div>
        </div>
    </header>
    <main class="main-content-inst">