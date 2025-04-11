<?php
/**
 * Gestión de modo offline para ToolHub Web
 * 
 * Este archivo proporciona funcionalidades para detectar automáticamente
 * cuando el sistema está sin conexión y adaptar las herramientas.
 */

/**
 * Verifica si hay conexión a Internet
 * 
 * @param int $timeout Tiempo de espera en segundos
 * @return bool True si hay conexión, false en caso contrario
 */
function check_internet_connection($timeout = 5) {
    // Lista de dominios confiables para verificar
    $test_servers = [
        'dns.google.com',
        'cloudflare.com',
        '1.1.1.1',
        'google.com'
    ];
    
    // Intentar conectar con cada servidor
    foreach ($test_servers as $server) {
        $conn = @fsockopen($server, 80, $errno, $errstr, $timeout);
        if ($conn) {
            fclose($conn);
            return true;
        }
    }
    
    return false;
}

/**
 * Activa o desactiva el modo offline
 * 
 * @param bool $enable Si es true, activa el modo offline
 */
function set_offline_mode($enable = true) {
    global $config;
    $config['offline']['enabled'] = $enable;
    
    // Guardar el estado en una sesión si está disponible
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['offline_mode'] = $enable;
    }
}

/**
 * Auto-detecta si estamos sin conexión y configura el sistema
 */
function auto_detect_offline_mode() {
    // No verificar si ya está en modo offline en la sesión
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['offline_mode']) && $_SESSION['offline_mode']) {
        set_offline_mode(true);
        return;
    }
    
    // Verificar conexión a Internet
    $has_connection = check_internet_connection();
    set_offline_mode(!$has_connection);
    
    // Si estamos offline, registrar un error informativo
    if (!$has_connection) {
        handle_error(
            "El modo offline está activado. Algunas funcionalidades están limitadas.",
            TOOLHUB_ERROR_INFO,
            "Auto-detection"
        );
    }
}

/**
 * Muestra un banner de modo offline si está activado
 */
function display_offline_banner() {
    if (is_offline()) {
        echo '<div class="alert alert-warning mb-4" role="alert">';
        echo '<i class="fas fa-wifi me-2"></i> ';
        echo '<strong>Modo sin conexión:</strong> ';
        echo htmlspecialchars(get_config('offline.message'));
        echo '</div>';
    }
}

/**
 * Comprueba si una funcionalidad está disponible en modo offline
 * 
 * @param string $tool_name Nombre de la herramienta
 * @return bool True si la herramienta está disponible
 */
function is_tool_available_offline($tool_name) {
    // Lista de herramientas con capacidades parciales o completas sin conexión
    $offline_tools = [
        'whois' => false,      // Requiere comando local
        'dns' => false,        // Requiere conexión para consultas
        'iplookup' => false,   // Requiere APIs externas
        'headers' => false,    // Requiere conexión para consultar sitios
        'ssl' => false,        // Requiere conexión para verificar certificados
        'tech' => false,       // Requiere conexión para analizar sitios
        'blacklist' => true,   // Puede usar datos simulados
    ];
    
    // Verificar capacidad offline para la herramienta
    if (!is_offline()) {
        return true; // Si estamos online, todas las herramientas están disponibles
    }
    
    return isset($offline_tools[$tool_name]) ? $offline_tools[$tool_name] : false;
}

/**
 * Comprueba si hay actualizaciones del repositorio
 * 
 * @return array|null Datos de la última versión o null si hay error
 */
function check_for_updates() {
    if (is_offline()) {
        return null;
    }
    
    // URL de la API de GitHub para verificar la última versión
    $repo_api_url = 'https://api.github.com/repos/cazique/toolhub-web/releases/latest';
    
    // Configurar contexto con tiempo límite y user agent
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'ToolHub-Web-Update-Check'
        ]
    ]);
    
    // Intentar obtener datos
    $response = @file_get_contents($repo_api_url, false, $context);
    
    if ($response === false) {
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }
    
    return [
        'version' => $data['tag_name'] ?? 'desconocida',
        'url' => $data['html_url'] ?? '#',
        'published_at' => $data['published_at'] ?? 'desconocida',
        'body' => $data['body'] ?? 'Sin detalles disponibles',
    ];
}

// Auto-detectar modo offline al cargar
auto_detect_offline_mode();
