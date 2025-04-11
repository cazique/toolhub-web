<?php
/**
 * Sistema unificado de manejo de errores para ToolHub Web
 * 
 * Este archivo proporciona una interfaz consistente para manejar
 * diferentes tipos de errores en toda la aplicación.
 */

// Nivel de error para producción o desarrollo
$debug_mode = false; // Cambiar a true para modo de desarrollo

// Niveles de error personalizados
define('TOOLHUB_ERROR_INFO', 1);     // Información, no un error real
define('TOOLHUB_ERROR_WARNING', 2);  // Advertencia, pero la operación puede continuar
define('TOOLHUB_ERROR_ERROR', 3);    // Error, la operación no puede continuar
define('TOOLHUB_ERROR_CRITICAL', 4); // Error crítico, afecta la funcionalidad completa

/**
 * Maneja un error en el sistema
 *
 * @param string $message Mensaje de error
 * @param int $level Nivel de error (usar constantes definidas)
 * @param string $context Contexto adicional del error
 * @param Exception|null $exception Excepción relacionada (opcional)
 * @return array Datos del error para uso interno
 */
function handle_error($message, $level = TOOLHUB_ERROR_ERROR, $context = '', $exception = null) {
    global $debug_mode;
    
    // Crear estructura de error
    $error = [
        'message' => $message,
        'level' => $level,
        'context' => $context,
        'time' => date('Y-m-d H:i:s'),
        'trace' => $debug_mode ? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) : [],
    ];
    
    // Agregar detalles de excepción si está disponible
    if ($exception !== null && $debug_mode) {
        $error['exception'] = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];
    }
    
    // Registrar error en sistema de logs
    log_error($error);
    
    return $error;
}

/**
 * Registra un error en el sistema de logs
 *
 * @param array $error Datos del error
 */
function log_error($error) {
    // Obtener configuración de logging
    $logging_enabled = get_config('logging.enabled');
    $log_file = get_config('logging.file');
    
    if ($logging_enabled && $error['level'] >= TOOLHUB_ERROR_ERROR) {
        $log_dir = dirname($log_file);
        
        // Crear directorio de logs si no existe
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        // Formato del mensaje de log
        $log_message = sprintf(
            "[%s] [%s] %s | Context: %s\n",
            $error['time'],
            get_level_name($error['level']),
            $error['message'],
            $error['context']
        );
        
        // Escribir en el archivo de log
        file_put_contents(
            TOOLHUB_BASE_DIR . '/' . $log_file,
            $log_message,
            FILE_APPEND
        );
    }
}

/**
 * Muestra un mensaje de error al usuario
 *
 * @param array $error Datos del error
 * @param bool $return Si es true, devuelve el HTML en lugar de imprimirlo
 * @return string|void HTML del mensaje o nada si $return es false
 */
function display_error($error, $return = false) {
    global $debug_mode;
    
    // Determinar clase CSS basada en el nivel de error
    $classes = [
        TOOLHUB_ERROR_INFO => 'alert-info',
        TOOLHUB_ERROR_WARNING => 'alert-warning',
        TOOLHUB_ERROR_ERROR => 'alert-danger',
        TOOLHUB_ERROR_CRITICAL => 'alert-danger',
    ];
    
    $class = $classes[$error['level']] ?? 'alert-danger';
    
    // Construir HTML
    $html = '<div class="alert ' . $class . '">';
    $html .= '<i class="fas fa-exclamation-triangle me-2"></i>';
    $html .= htmlspecialchars($error['message']);
    
    // Mostrar contexto en modo debug
    if ($debug_mode && !empty($error['context'])) {
        $html .= '<div class="mt-2 small text-muted">';
        $html .= 'Contexto: ' . htmlspecialchars($error['context']);
        $html .= '</div>';
    }
    
    // Mostrar traza en modo debug
    if ($debug_mode && !empty($error['trace'])) {
        $html .= '<div class="mt-2">';
        $html .= '<button class="btn btn-sm btn-outline-secondary" type="button" ';
        $html .= 'data-bs-toggle="collapse" data-bs-target="#errorTrace">';
        $html .= 'Ver detalles técnicos';
        $html .= '</button>';
        $html .= '<div class="collapse mt-2" id="errorTrace">';
        $html .= '<div class="card card-body">';
        $html .= '<pre class="mb-0" style="font-size: 0.8rem;">';
        $html .= htmlspecialchars(json_encode($error['trace'], JSON_PRETTY_PRINT));
        $html .= '</pre>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    if ($return) {
        return $html;
    } else {
        echo $html;
    }
}

/**
 * Obtiene el nombre del nivel de error
 *
 * @param int $level Nivel de error
 * @return string Nombre del nivel
 */
function get_level_name($level) {
    $levels = [
        TOOLHUB_ERROR_INFO => 'INFO',
        TOOLHUB_ERROR_WARNING => 'WARNING',
        TOOLHUB_ERROR_ERROR => 'ERROR',
        TOOLHUB_ERROR_CRITICAL => 'CRITICAL',
    ];
    
    return $levels[$level] ?? 'UNKNOWN';
}

/**
 * Manejador personalizado de excepciones
 */
function toolhub_exception_handler($exception) {
    $error = handle_error(
        'Ha ocurrido un error inesperado: ' . $exception->getMessage(),
        TOOLHUB_ERROR_CRITICAL,
        'Exception',
        $exception
    );
    
    display_error($error);
    
    // En caso de error crítico, terminar la ejecución
    if ($error['level'] >= TOOLHUB_ERROR_CRITICAL) {
        exit;
    }
}

/**
 * Manejador personalizado de errores de PHP
 */
function toolhub_error_handler($errno, $errstr, $errfile, $errline) {
    // No reportar errores si están desactivados en la configuración
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $level = TOOLHUB_ERROR_ERROR;
    
    // Mapear errores de PHP a nuestros niveles
    switch ($errno) {
        case E_WARNING:
        case E_USER_WARNING:
            $level = TOOLHUB_ERROR_WARNING;
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            $level = TOOLHUB_ERROR_INFO;
            break;
    }
    
    $error = handle_error(
        $errstr,
        $level,
        "PHP Error: $errfile:$errline"
    );
    
    if ($error['level'] >= TOOLHUB_ERROR_ERROR) {
        display_error($error);
    }
    
    // No ejecutar el manejador interno de PHP
    return true;
}

// Registrar manejadores personalizados
set_exception_handler('toolhub_exception_handler');
set_error_handler('toolhub_error_handler');
