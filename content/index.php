<?php include("configuration.php"); ?>

<!DOCTYPE html>
<html lang="es-ES">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css" type="text/css">
    <link rel="icon" type="image/jpg" href="public/favicon.ico"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="script.js"></script>

    <title>Etiquetador OSL</title>
</head>
<body>
    <section class="layout">
        <div class="header">
            <img src="public/logo_UGR_horizontal.png" width=350>
            <h1>Generador de etiquetas</h1>
            <img src="public/osl_logo.png" width=300>

        </div>

        <div class="options">
            <!-- Nombre de la etiqueta -->
            <div class="form-group">
                <label for="ticket_name">Nombre del ticket:</label>
                <input type="text" placeholder="Escribir nombre del ticket en caso de guardado" id="ticket_name" name="ticket_name">
            </div>
            <button class="options-btn-save" onclick="save()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="8" y="8" width="48" height="48" rx="2" ry="2"/>
            <rect x="24" y="8" width="16" height="12"/>
            <rect x="20" y="40" width="24" height="16"/>
            </svg>
            Guardar
            </button>
            <button class="options-btn" onclick="load()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M8 20C8 18.8954 8.89543 18 10 18H26L30 22H54C55.1046 22 56 22.8954 56 24V46C56 47.1046 55.1046 48 54 48H10C8.89543 48 8 47.1046 8 46V20Z"/>
            </svg>
            Cargar
            </button>
            <button class="options-btn" onclick="loadModel()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M8 20C8 18.8954 8.89543 18 10 18H26L30 22H54C55.1046 22 56 22.8954 56 24V46C56 47.1046 55.1046 48 54 48H10C8.89543 48 8 47.1046 8 46V20Z"/>
            </svg>
            Gestionar Modelos
            </button>

        </div>
        <div class="form">
            <form action="generate_pdf.php" method="post">

                <!-- Tipo de Placa -->
                <div class="form-group">
                    <label for="board_type">Tipo de placa:</label>
                    <select name="board_type" id="board_type" required>
                        <option value="bios">BIOS</option>
                        <option value="uefi">UEFI</option>
                    </select>
                </div>
                <hr>
                <!-- Nombre CPU -->
                <div class="form-group">
                    <label for="cpu_name">Nombre CPU:</label>
                    <div class="line">
                    <select name="cpu_name" id="cpu_name" style="width: 25%;">
                        <option>Indefinido</option>
                        <?php
                        $stmt = $conn->prepare("SELECT DISTINCT name FROM cpu ORDER BY name ASC");
                        $stmt->execute();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="' . $row['name'] . '">' . htmlspecialchars($row['name']) . '</option>';
                        }
                        ?>
                    </select>
                    <input type="text" placeholder="Escribir nombre del cpu en caso de otro" id="cpu_other_name" name="cpu_other_name">
                    </div>
                </div>
                <hr>

                <!-- Memoria -->
                <div class="form-group">
                    <label for="ram_capacity">Memoria:</label>
                    <div class="line">
                        <select name="ram_capacity" id="ram_capacity" style="width: 25%;">
                            <option>Indefinido</option>
                            <?php
                            $stmt = $conn->prepare("SELECT DISTINCT capacity FROM ram ORDER BY capacity");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['capacity'] . '">' . htmlspecialchars($row['capacity']) . '</option>';
                            }
                            ?>
                        </select>
                        <input type="number" id="ram_other_capacity" name="ram_other_capacity" placeholder="0" max="99999">
                        GB |
                        <select name="ram_type" id="ram_type" style="width: 19.5%;">
                            <option value="ddr2">DDR2</option>
                            <option value="ddr3">DDR3</option>
                            <option value="ddr4">DDR4</option>
                            <option value="ddr5">DDR5</option>
                        </select>
                    </div>
                </div>
                <hr>
                <!-- Disco duro -->
                <div class="form-group">
                    <label for="disc_capacity">Disco duro:</label>
                    <div class="line">
                        <select name="disc_capacity" id="disc_capacity" style="width: 25%;">
                            <option>Indefinido</option>
                            <?php
                            $stmt = $conn->prepare("SELECT DISTINCT capacity FROM disc ORDER BY capacity");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['capacity'] . '">' . htmlspecialchars($row['capacity']) . '</option>';
                            }
                            ?>
                        </select>
                        <input type="number" id="disc_other_capacity" name="disc_other_capacity" placeholder="0" max="99999">
                        GB |
                        <select name="disc_type" id="disc_type" style="width: 19.5%;">
                            <option value="hdd">HDD</option>
                            <option value="ssd">SSD</option>
                            <option value="nvme">NVMe</option>
                        </select>
                    </div>
                </div>
                <hr>

                <!-- Grafica -->
                <div class="form-group">
                    <label for="gpu_name">Gráfica:</label>
                    <div class="line">
                        <select name="gpu_name" id="gpu_name" style="width: 25%;">
                            <option>Indefinido</option>
                            <?php
                            $stmt = $conn->prepare("SELECT DISTINCT name FROM gpu ORDER BY name ASC");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['name'] . '">' . htmlspecialchars($row['name']) . '</option>';
                            }
                            ?>
                        </select>
                        <input type="text" placeholder="Escribir nombre del gpu en caso de otro" id="gpu_other_name" name="gpu_other_name" maxlength="40">
                        <select name="gpu_type" id="gpu_type" style="width: 20%">
                            <option value="integrada">Integrada</option>
                            <option value="externa">Externa</option>
                        </select>
                    </div>
                </div>
                <hr>

                <!-- WiFi y Bluetooth -->
                <div class="line">
                    <div class="radio-section">
                        <label>WiFi:</label>
                        <div class="radio-input">
                            <label>
                                <input type="radio" id="wifi_si" name="wifi" value="true">
                                <span>Sí</span>
                            </label>
                            <label>
                                <input type="radio" id="wifi_no" name="wifi" value="false">
                                <span>No</span>
                            </label>
                            <span class="selection"></span>
                        </div>

                        <label>Bluetooth:</label>
                        <div class="radio-input">
                            <label>
                                <input type="radio" id="bluetooth_si" name="bluetooth" value="true">
                                <span>Sí</span>
                            </label>
                            <label>
                                <input type="radio" id="bluetooth_no" name="bluetooth" value="false">
                                <span>No</span>
                            </label>
                            <span class="selection"></span>
                        </div>
                    </div>
                    <div class="vertical-line"></div>

                    <!--Numero de serie-->
                    <div class="twodivinline">
                        <label for="SN">Número de serie:</label>
                        <div class="line">
                            <select name="sn_prefix" id="sn_prefix">
                            <?php
                            $stmt = $conn->prepare("SELECT DISTINCT prefix FROM sn ORDER BY prefix ASC");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['prefix'] . '">' . htmlspecialchars($row['prefix']) . '</option>';
                            }
                            ?>
                            </select>
                            <input type="text" placeholder="Prefijo (ej: ABC)" id="sn_prefix_other" name="sn_prefix_other" maxlength="3">
                        </div>
                        
                        <!--Cantidad de etiquetas-->
                        <label for="num_pag" style="margin-top: 6px">Cantidad de etiquetas</label>
                        <div class="line">
                            <input type="number" placeholder="1" id="num_pag" name="num_pag" max="50" min="1" value="1" required>
                        </div>
                    </div>
                </div>
                <hr>

                <!-- Observaciones -->
                <div class="form-group">
                    <label for="observaciones">Observaciones:</label>
                    <textarea maxlength="567" id="observaciones" name="observaciones" rows="4" cols="50"></textarea>
                </div>

                <input type="submit" value="Generar Preview" onclick="reload_iframe()">
            </form>
        </div>

        <!-- Preview -->
        <div class="preview">

            <?php
            $pdfToShow = 'pdf/etiqueta_preview.pdf';
            
            if (isset($_GET['pdf']) && file_exists($_GET['pdf'])) {
                $pdfToShow = $_GET['pdf'];
            } elseif (file_exists('pdf/generado.pdf')) {
                $pdfToShow = 'pdf/generado.pdf';
            }
            
            echo "<iframe id='iframe_preview'src='$pdfToShow#toolbar=0#view=Fit' frameborder='0' width='100%' height='100%' title='Preview' style='border:none'></iframe>";
            ?>

        </div>

        <footer class="footer">

        <div class="footer-content">
           
            <div class="footer-buttons">
            <a href="https://github.com/Adriansolier322/EtiquetadorOSL" class="footer-btn" target="_blank">
            <!-- Logo de GitHub -->
                <svg height="22" viewBox="0 0 16 16" width="22" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                <path d="M8 0C3.58 0 0 3.58 0 8a8 8 0 005.47 7.59c.4.07.55-.17.55-.38 
                0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52
                -.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78
                -.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 
                .67-.21 2.2.82a7.54 7.54 0 012-.27c.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 
                2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 
                3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 
                .21.15.46.55.38A8.01 8.01 0 0016 8c0-4.42-3.58-8-8-8z"/>
                </svg>
                GitHub
            </a>

            <a href="https://osl.ugr.es/" class="footer-btn" target="_blank">
            <svg height="22" width="22" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 490 490" stroke="currentColor" fill="currentColor">
            <path d="M245,0C109.69,0,0,109.69,0,245s109.69,245,245,245s245-109.69,245-245S380.31,0,245,0z M31.401,260.313h52.542
                c1.169,25.423,5.011,48.683,10.978,69.572H48.232C38.883,308.299,33.148,284.858,31.401,260.313z M320.58,229.688
                c-1.152-24.613-4.07-47.927-8.02-69.572h50.192c6.681,20.544,11.267,43.71,12.65,69.572H320.58z M206.38,329.885
                c-4.322-23.863-6.443-47.156-6.836-69.572h90.913c-0.392,22.416-2.514,45.709-6.837,69.572H206.38z M276.948,360.51
                c-7.18,27.563-17.573,55.66-31.951,83.818c-14.376-28.158-24.767-56.255-31.946-83.818H276.948z M199.961,229.688
                c1.213-24.754,4.343-48.08,8.499-69.572h73.08c4.157,21.492,7.286,44.818,8.5,69.572H199.961z M215.342,129.492
                c9.57-37.359,21.394-66.835,29.656-84.983c8.263,18.148,20.088,47.624,29.66,84.983H215.342z M306.07,129.492
                c-9.77-40.487-22.315-73.01-31.627-94.03c11.573,8.235,50.022,38.673,76.25,94.03H306.07z M215.553,35.46
                c-9.312,21.02-21.855,53.544-31.624,94.032h-44.628C165.532,74.13,203.984,43.692,215.553,35.46z M177.44,160.117
                c-3.95,21.645-6.867,44.959-8.019,69.572h-54.828c1.383-25.861,5.968-49.028,12.65-69.572H177.44z M83.976,229.688H31.401
                c1.747-24.545,7.481-47.984,16.83-69.572h46.902C89.122,181.002,85.204,204.246,83.976,229.688z M114.577,260.313h54.424
                c0.348,22.454,2.237,45.716,6.241,69.572h-47.983C120.521,309.288,115.92,286.115,114.577,260.313z M181.584,360.51
                c7.512,31.183,18.67,63.054,34.744,95.053c-10.847-7.766-50.278-38.782-77.013-95.053H181.584z M273.635,455.632
                c16.094-32.022,27.262-63.916,34.781-95.122h42.575C324.336,417.068,284.736,447.827,273.635,455.632z M314.759,329.885
                c4.005-23.856,5.894-47.118,6.241-69.572h54.434c-1.317,25.849-5.844,49.016-12.483,69.572H314.759z M406.051,260.313h52.548
                c-1.748,24.545-7.482,47.985-16.831,69.572h-46.694C401.041,308.996,404.882,285.736,406.051,260.313z M406.019,229.688
                c-1.228-25.443-5.146-48.686-11.157-69.572h46.908c9.35,21.587,15.083,45.026,16.83,69.572H406.019z M425.309,129.492h-41.242
                c-13.689-32.974-31.535-59.058-48.329-78.436C372.475,68.316,403.518,95.596,425.309,129.492z M154.252,51.06
                c-16.792,19.378-34.636,45.461-48.324,78.432H64.691C86.48,95.598,117.52,68.321,154.252,51.06z M64.692,360.51h40.987
                c13.482,32.637,31.076,58.634,47.752,78.034C117.059,421.262,86.318,394.148,64.692,360.51z M336.576,438.54
                c16.672-19.398,34.263-45.395,47.742-78.03h40.99C403.684,394.146,372.945,421.258,336.576,438.54z"/>
            </svg>

                Sitio Web
            </a>

            <button class="footer-btn" id="theme-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2">
            <!-- Círculo central del sol -->
            <circle cx="32" cy="32" r="12" fill="currentColor" stroke="none"/>

            <!-- Rayos del sol -->
            <line x1="32" y1="4" x2="32" y2="14"/>
            <line x1="32" y1="50" x2="32" y2="60"/>
            <line x1="4" y1="32" x2="14" y2="32"/>
            <line x1="50" y1="32" x2="60" y2="32"/>
            <line x1="14" y1="14" x2="20" y2="20"/>
            <line x1="50" y1="50" x2="44" y2="44"/>
            <line x1="14" y1="50" x2="20" y2="44"/>
            <line x1="50" y1="14" x2="44" y2="20"/>
            </svg>

                Cambiar Tema
            </button>
            </div>
            
        </div>
        <span class="footer-text">2025 - Oficina de Software Libre</span>
        </footer>


    </section>


</body>
</html>
