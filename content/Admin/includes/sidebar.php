<div class="sidebar">
    <h2>Panel de Admin</h2>
    <ul>
        <li><a href="index.php">Dashboard</a></li>
        <li><a href="sn.php">Números de Serie</a></li>
        <li><a href="cpu.php">CPUs</a></li>
        <li><a href="gpu.php">GPUs</a></li>
        <li><a href="pc.php">PCs</a></li>
        <li><a href="models.php">Modelos</a></li>
        <li><a href="stats.php">Estadisticas</a></li>
        <hr>
        <li><a href="users.php">Gestionar usuarios</a></li>
        <li><a href="../EtiquetadorOSL">Volver al etiquetador</a></li>
        <li><a class="footer-btn" id="theme-toggle">Cambiar Tema</a></li>
        <li><a href="logout.php">Cerrar Sesión</a></li>
    </ul>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener la ruta actual completa (ej: "/admin/pc.php" o "/admin/")
            const currentPath = window.location.pathname;
            
            // Seleccionar todos los enlaces del menú
            const menuLinks = document.querySelectorAll('.sidebar ul li a');
            
            menuLinks.forEach(link => {
                const linkHref = link.getAttribute('href');
                const linkPath = linkHref.startsWith('../') 
                    ? linkHref.replace('../', '/') 
                    : '/' + linkHref;
                
                // Comparar rutas (ignorando parámetros de consulta y hash)
                const currentPathClean = currentPath.split('?')[0].split('#')[0];
                const linkPathClean = linkPath.split('?')[0].split('#')[0];
                
                // Caso especial para index.php que puede ser accedido como /
                const isIndexMatch = (currentPathClean === '/' || currentPathClean.endsWith('index.php')) && 
                                    (linkPathClean.endsWith('index.php') || linkPathClean.endsWith('/'));
                
                // Caso general para otras páginas
                const isPathMatch = currentPathClean.endsWith(linkPathClean) || 
                                (linkPathClean.endsWith('/') && currentPathClean + '/' === linkPathClean);
                
                if (isIndexMatch || isPathMatch) {
                    link.parentElement.classList.add('active');
                }
            });
           // Theme Toggle Logic
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;
            
            // Apply theme on load
            const currentTheme = localStorage.getItem('theme');
            if (currentTheme) {
                body.classList.add(currentTheme);
            }

            // Add click listener
            // It's good practice to check if the element exists before adding an event listener
            if (themeToggle) { 
                themeToggle.addEventListener('click', () => {
                    if (body.classList.contains('dark-theme')) {
                        body.classList.remove('dark-theme');
                        localStorage.setItem('theme', ''); // Store empty string for light theme
                    } else {
                        body.classList.add('dark-theme');
                        localStorage.setItem('theme', 'dark-theme');
                    }
                });
            } else {
                console.warn("Theme toggle button with ID 'theme-toggle' not found!");
            }
        });
    </script>
</div>