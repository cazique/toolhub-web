<?php
/**
 * Archivo de configuración global para ToolHub Web
 * 
 * Este archivo centraliza todas las configuraciones del sistema para
 * facilitar el mantenimiento y la personalización.
 */

// Configuración básica
$config = [
    // Información de la aplicación
    'app' => [
        'name' => 'ToolHub Web',
        'version' => '0.2',
        'author' => 'cazique',
        'github' => 'https://github.com/cazique/toolhub-web',
    ],
    
    // Límites y timeouts
    'limits' => [
        'http_timeout' => 10, // segundos
        'max_results' => 100, // número máximo de resultados a mostrar
        'max_execution_time' => 30, // segundos
    ],
    
    // APIs externas
    'apis' => [
        'geoip' => [
            'url' => 'http://ip-api.com/json/{ip}',
            'fields' => 'status,message,country,regionName,city,zip,lat,lon,timezone,isp,org,as,query',
        ],
        // Añadir otras APIs aquí
    ],
    
    // Configuración de herramientas específicas
    'tools' => [
        'blacklist' => [
            'servers' => [
                "spam.spamrats.com",
                "b.barracudacentral.org",
                "bl.spamcop.net",
                "zen.spamhaus.org",
                "dnsbl.sorbs.net",
                "dnsbl-1.uceprotect.net",
                "bl.emailbasura.org",
                "cbl.abuseat.org"
            ],
            'simulate_results' => true, // Cambiar a false para consultas reales
        ],
        'dns' => [
            'types' => ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SOA', 'CAA'],
            'default_types' => ['A', 'MX', 'NS'],
        ],
        'whois' => [
            'command' => 'whois', // Comando para ejecutar WHOIS
        ],
    ],
    
    // Configuración de registros de uso
    'logging' => [
        'enabled' => false,
        'file' => 'logs/usage.log',
        'fields' => ['date', 'ip', 'tool', 'query', 'user_agent'],
        'max_size' => 10485760, // 10MB
    ],
    
    // Modo offline
    'offline' => [
        'enabled' => false,
        'message' => 'Algunas funcionalidades están limitadas en modo offline',
    ],
];

// Variables globales para la aplicación
define('TOOLHUB_VERSION', $config['app']['version']);
define('TOOLHUB_BASE_DIR', dirname(__FILE__));

// Función para obtener configuración
function get_config($path = null) {
    global $config;
    
    if ($path === null) {
        return $config;
    }
    
    $keys = explode('.', $path);
    $value = $config;
    
    foreach ($keys as $key) {
        if (!isset($value[$key])) {
            return null;
        }
        $value = $value[$key];
    }
    
    return $value;
}

// Función para verificar si estamos en modo offline
function is_offline() {
    global $config;
    return $config['offline']['enabled'];
}
