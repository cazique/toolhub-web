<?php
/**
 * Sistema de registro de uso para ToolHub Web
 * 
 * Este archivo maneja el registro de consultas realizadas por los usuarios,
 * respetando la privacidad y permitiendo el control del consentimiento.
 */

// Verificar si las cookies de consentimiento ya se han configurado
function has_logging_consent() {
    return isset($_COOKIE['toolhub_logging_consent']) && $_COOKIE['toolhub_logging_consent'] === 'yes';
}

// Establecer el consentimiento de registro
function set_logging_consent($consent = true) {
    $value = $consent ? 'yes' : 'no';
    setcookie('toolhub_logging_consent', $value, time() + 60*60*24*365, '/'); // 1 año
    
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['toolhub_logging_consent'] = $consent;
    }
}

// Registrar el uso de una herramienta
function log_tool_usage($tool_name, $query, $results_status = true) {
    // Verificar si el registro está habilitado y si hay consentimiento
    if (!get_config('logging.enabled') || !has_logging_consent()) {
        return false;
    }
    
    // Obtener la ruta del archivo de log
    $log_file = TOOLHUB_BASE_DIR . '/' . get_config('logging.file');
    $log_dir = dirname($log_file);
    
    // Crear directorio si no existe
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Verificar si el archivo de log supera el tamaño máximo
    if (file_exists($log_file) && filesize($log_file) > get_config('logging.max_size')) {
        // Crear archivo de respaldo con timestamp
        $backup_file = $log_file . '.' . date('YmdHis');
        rename($log_file, $backup_file);
    }
    
    // Obtener datos para el registro
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    // Anonimizar IP para proteger la privacidad (conservar solo los primeros octetos)
    $ip_parts = explode('.', $ip);
    if (count($ip_parts) === 4) {
        $ip = "{$ip_parts[0]}.{$ip_parts[1]}.x.x";
    }
    
    // Preparar la entrada de log en formato CSV
    $log_data = [
        'date' => $timestamp,
        'ip' => $ip,
        'tool' => $tool_name,
        'query' => $query,
        'status' => $results_status ? 'success' : 'error',
        'user_agent' => $user_agent
    ];
    
    // Abrir archivo en modo append
    $fp = fopen($log_file, 'a');
    
    // Si es un archivo nuevo, agregar encabezados
    if (filesize($log_file) === 0) {
        fputcsv($fp, array_keys($log_data));
    }
    
    // Escribir datos
    fputcsv($fp, array_values($log_data));
    fclose($fp);
    
    return true;
}

// Mostrar un banner de consentimiento para el registro de uso
function display_consent_banner() {
    // No mostrar si ya se ha dado consentimiento o si el registro está desactivado
    if (has_logging_consent() || !get_config('logging.enabled')) {
        return;
    }
    
    // HTML para el banner de consentimiento
    echo '<div id="consent-banner" class="alert alert-info alert-dismissible fade show" role="alert">';
    echo '<div class="d-flex align-items-center">';
    echo '<div class="flex-grow-1">';
    echo '<h5><i class="fas fa-clipboard-list me-2"></i>Consentimiento de registro</h5>';
    echo '<p class="mb-0">ToolHub Web puede registrar de forma anónima las consultas realizadas para mejorar el servicio. ';
    echo 'Tus datos personales están seguros, las IPs se anonimizan y puedes revocar este permiso en cualquier momento.</p>';
    echo '</div>';
    echo '<div class="ms-3">';
    echo '<button type="button" class="btn btn-success me-2" onclick="setLoggingConsent(true)">Aceptar</button>';
    echo '<button type="button" class="btn btn-outline-secondary" onclick="setLoggingConsent(false)">Rechazar</button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    // JavaScript para manejar el consentimiento
    echo '<script>
    function setLoggingConsent(consent) {
        const value = consent ? "yes" : "no";
        document.cookie = "toolhub_logging_consent=" + value + ";path=/;max-age=31536000"; // 1 año
        document.getElementById("consent-banner").style.display = "none";
    }
    </script>';
}

// Función para obtener estadísticas de uso
function get_usage_stats() {
    if (!get_config('logging.enabled')) {
        return null;
    }
    
    $log_file = TOOLHUB_BASE_DIR . '/' . get_config('logging.file');
    
    if (!file_exists($log_file)) {
        return [
            'total_queries' => 0,
            'by_tool' => [],
            'last_day' => 0,
            'last_week' => 0,
            'last_month' => 0
        ];
    }
    
    // Estadísticas a recopilar
    $stats = [
        'total_queries' => 0,
        'by_tool' => [],
        'last_day' => 0,
        'last_week' => 0,
        'last_month' => 0
    ];
    
    // Obtener marcas de tiempo para comparaciones
    $now = time();
    $day_ago = $now - (24 * 60 * 60);
    $week_ago = $now - (7 * 24 * 60 * 60);
    $month_ago = $now - (30 * 24 * 60 * 60);
    
    // Abrir archivo para lectura
    $fp = fopen($log_file, 'r');
    
    // Leer encabezados
    $headers = fgetcsv($fp);
    
    // Índices de columnas
    $date_idx = array_search('date', $headers);
    $tool_idx = array_search('tool', $headers);
    
    if ($date_idx === false || $tool_idx === false) {
        fclose($fp);
        return $stats;
    }
    
    // Procesar cada línea
    while (($line = fgetcsv($fp)) !== false) {
        $stats['total_queries']++;
        
        // Contabilizar por herramienta
        $tool = $line[$tool_idx];
        if (!isset($stats['by_tool'][$tool])) {
            $stats['by_tool'][$tool] = 0;
        }
        $stats['by_tool'][$tool]++;
        
        // Verificar fecha para estadísticas temporales
        $entry_time = strtotime($line[$date_idx]);
        
        if ($entry_time >= $day_ago) {
            $stats['last_day']++;
        }
        
        if ($entry_time >= $week_ago) {
            $stats['last_week']++;
        }
        
        if ($entry_time >= $month_ago) {
            $stats['last_month']++;
        }
    }
    
    fclose($fp);
    return $stats;
}

// Interfaz administrativa para ver estadísticas de uso
function render_usage_stats_page() {
    // Verificar si el registro está habilitado
    if (!get_config('logging.enabled')) {
        echo '<div class="alert alert-warning">';
        echo '<i class="fas fa-exclamation-triangle me-2"></i> ';
        echo 'El registro de uso está desactivado en la configuración. Actívalo en config.php para ver estadísticas.';
        echo '</div>';
        return;
    }
    
    // Obtener estadísticas
    $stats = get_usage_stats();
    
    if ($stats['total_queries'] === 0) {
        echo '<div class="alert alert-info">';
        echo '<i class="fas fa-info-circle me-2"></i> ';
        echo 'No hay datos de uso registrados todavía.';
        echo '</div>';
        return;
    }
    
    // Mostrar resumen
    echo '<div class="row g-4">';
    
    // Tarjeta de consultas totales
    echo '<div class="col-md-3">';
    echo '<div class="card bg-primary text-white h-100">';
    echo '<div class="card-body text-center">';
    echo '<h1>' . number_format($stats['total_queries']) . '</h1>';
    echo '<h5>Consultas Totales</h5>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    // Tarjetas de periodo
    echo '<div class="col-md-3">';
    echo '<div class="card bg-success text-white h-100">';
    echo '<div class="card-body text-center">';
    echo '<h1>' . number_format($stats['last_day']) . '</h1>';
    echo '<h5>Últimas 24 horas</h5>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    echo '<div class="col-md-3">';
    echo '<div class="card bg-warning text-dark h-100">';
    echo '<div class="card-body text-center">';
    echo '<h1>' . number_format($stats['last_week']) . '</h1>';
    echo '<h5>Última semana</h5>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    echo '<div class="col-md-3">';
    echo '<div class="card bg-info text-white h-100">';
    echo '<div class="card-body text-center">';
    echo '<h1>' . number_format($stats['last_month']) . '</h1>';
    echo '<h5>Último mes</h5>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>';
    
    // Estadísticas por herramienta
    echo '<div class="card mt-4">';
    echo '<div class="card-header bg-light">';
    echo '<h5 class="mb-0">Uso por herramienta</h5>';
    echo '</div>';
    echo '<div class="card-body">';
    
    // Ordenar por uso
    arsort($stats['by_tool']);
    
    echo '<table class="table table-striped table-hover">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Herramienta</th>';
    echo '<th>Consultas</th>';
    echo '<th>Porcentaje</th>';
    echo '<th>Gráfico</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($stats['by_tool'] as $tool => $count) {
        $percentage = ($count / $stats['total_queries']) * 100;
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($tool) . '</td>';
        echo '<td>' . number_format($count) . '</td>';
        echo '<td>' . number_format($percentage, 1) . '%</td>';
        echo '<td width="40%">';
        echo '<div class="progress">';
        echo '<div class="progress-bar" role="progressbar" style="width: ' . $percentage . '%"></div>';
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    
    echo '</div>';
    echo '</div>';
    
    // Añadir opciones administrativas
    echo '<div class="d-flex justify-content-end mt-4">';
    echo '<a href="?action=download_logs" class="btn btn-outline-primary me-2">';
    echo '<i class="fas fa-download me-2"></i>Descargar logs';
    echo '</a>';
    echo '<button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#resetLogsModal">';
    echo '<i class="fas fa-trash-alt me-2"></i>Reiniciar estadísticas';
    echo '</button>';
    echo '</div>';
    
    // Modal de confirmación para reiniciar logs
    echo '<div class="modal fade" id="resetLogsModal" tabindex="-1" aria-hidden="true">';
    echo '<div class="modal-dialog">';
    echo '<div class="modal-content">';
    echo '<div class="modal-header bg-danger text-white">';
    echo '<h5 class="modal-title">Confirmar eliminación</h5>';
    echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
    echo '</div>';
    echo '<div class="modal-body">';
    echo '<p>¿Estás seguro de que deseas eliminar todos los registros de uso? Esta acción no se puede deshacer.</p>';
    echo '</div>';
    echo '<div class="modal-footer">';
    echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>';
    echo '<a href="?action=reset_logs" class="btn btn-danger">Eliminar registros</a>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
