<?php 
include 'includes/auth.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Panel de Administración</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'?>
    <div class="main-content">
        <h1>Dashboard</h1>
        <!-- Linea 1 -->
        <div class="stats-container">
            <!-- CPUs -->
            <div class="stat-card">
                <h3>CPUs Registradas</h3>
                <p><?php 
                    include 'includes/config.php';
                    $stmt = $pdo->query("SELECT COUNT(*) FROM cpu");
                    echo $stmt->fetchColumn();
                ?></p>
            </div>
            <!-- GPUs -->
            <div class="stat-card">
                <h3>GPUs registradas</h3>
                <p><?php
                $stmt = $pdo->query("SELECT COUNT(*) FROM gpu");
                echo $stmt->fetchColumn();
                ?></p>
            </div>
            
        </div>
        <!-- Linea 2 -->
        <div class="stats-container">
            <!-- PCs -->
            <div class="stat-card">
                    <h3>PCs con wifi</h3>
                    <p><?php 
                        $stmt = $pdo->query("SELECT COUNT(*) AS num_pc_con_wifi FROM pc WHERE wifi = 'true'");
                        echo $stmt->fetchColumn();
                    ?></p>
                </div>
                <!-- Models -->
                <div class="stat-card">
                    <h3>PCs con bluetooth</h3>
                    <p><?php 
                        $stmt = $pdo->query("SELECT COUNT(*) AS num_pc_con_bluetooth FROM pc WHERE bluetooth = 'true'");
                        echo $stmt->fetchColumn();
                    ?></p>
                </div>
            </div>

        <!-- Linea 3 -->
        <div class="stats-container">
            <!-- PCs -->
            <div class="stat-card">
                <h3>PCs Configuradas</h3>
                <p><?php 
                    $stmt = $pdo->query("SELECT COUNT(*) FROM pc");
                    echo $stmt->fetchColumn();
                ?></p>
            </div>
            <!-- Models -->
            <div class="stat-card">
                <h3>Modelos</h3>
                <p><?php 
                    $stmt = $pdo->query("SELECT COUNT(*) FROM models");
                    echo $stmt->fetchColumn();
                ?></p>
            </div>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>