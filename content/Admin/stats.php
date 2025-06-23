<?php
require_once 'includes/auth.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h1>Estadísticas de PCs</h1>
        
        <div class="stats-grid">
            <!-- Gráfico de CPUs -->
            <div class="card">
                <h2>Distribución de CPU</h2>
                <canvas id="cpuChart"></canvas>
            </div>
            
            <!-- Gráfico de GPUs -->
            <div class="card">
                <h2>Distribución de GPU</h2>
                <canvas id="gpuChart"></canvas>
            </div>
        </div>
        <div class="stats-grid">
                        
            <!-- Gráfico de RAM -->
            <div class="card">
                <h2>Distribución de RAM</h2>
                <canvas id="ramChart"></canvas>
            </div>
            
            <!-- Gráfico de Discos -->
            <div class="card">
                <h2>Distribución de Almacenamiento</h2>
                <canvas id="discChart"></canvas>
            </div>
        </div>
    </div>
    
    <script>
    // Colores para los gráficos
    const chartColors = [
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 99, 132, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(255, 159, 64, 0.7)',
        'rgba(153, 102, 255, 0.7)',
        'rgba(255, 206, 86, 0.7)',   // Amarillo
        'rgba(201, 203, 207, 0.7)',  // Gris claro
        'rgba(100, 181, 246, 0.7)',  // Azul cielo
        'rgba(255, 138, 128, 0.7)',  // Coral claro
        'rgba(128, 222, 234, 0.7)',  // Celeste suave
        'rgba(174, 213, 129, 0.7)',  // Verde lima
        'rgba(255, 112, 67, 0.7)',   // Naranja quemado
        'rgba(179, 136, 255, 0.7)',  // Lavanda fuerte
        'rgba(255, 171, 145, 0.7)',  // Salmón claro
        'rgba(121, 134, 203, 0.7)'   // Azul lavanda
    ];
    
    // Función para crear gráficos
    function createChart(elementId, title, url) {
        fetch(url)
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById(elementId).getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: title,
                            data: data.values,
                            backgroundColor: chartColors,
                            borderColor: chartColors.map(c => c.replace('0.7', '1')),
                            borderWidth: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: { beginAtZero: true }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            });
    }
    
    // Crear todos los gráficos al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        createChart('cpuChart', 'PCs de CPU', 'includes/get_pc_stats.php?stat=cpu');
        createChart('ramChart', 'PCs de RAM', 'includes/get_pc_stats.php?stat=ram');
        createChart('discChart', 'PCs de Almacenamiento', 'includes/get_pc_stats.php?stat=disc');
        createChart('gpuChart', 'PCs de GPU', 'includes/get_pc_stats.php?stat=gpu');
    });
    </script>
    
    <style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .stats-grid .card {
        height: 100%;
    }
    
    .stats-grid canvas {
        max-height: 300px;
        width: 100% !important;
    }
    </style>
</body>
</html>