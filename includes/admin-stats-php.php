<?php
/**
 * Página de estadísticas administrativas para ToolHub Web
 */

// Incluir archivos necesarios
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/error_handler.php';
require_once dirname(__DIR__) . '/includes/offline_mode.php';
require_once dirname(__DIR__) . '/includes/usage_logger.php';

// Procesamiento de acciones administrativas
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'download_logs':
            // Descargar archivo de logs
            $log_file = TOOLHUB_BASE_DIR . '/' . get_config('logging.file');
            
            if (file_exists($log_file)) {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="toolhub_usage_logs.csv"');
                header('Content-Length: ' . filesize($log_file));
                
                readfile($log_file);
                exit;
            }
            break;
            
        case 'reset_logs':
            // Eliminar logs
            $log_file = TOOLHUB_BASE_DIR . '/' . get_config('logging.file');
            
            if (file_exists($log_file)) {
                // Crear copia de respaldo antes de eliminar
                $backup_file = $log_file . '.' . date('YmdHis') . '.bak';
                copy($log_file, $backup_file);
                
                // Truncar archivo
                file_put_contents($log_file, '');
                
                // Mensaje de éxito
                $success_message = "Los registros han sido eliminados y se ha creado una copia de respaldo.";
            }
            break;
    }
}

// Autenticación básica (esto debería mejorarse en un entorno de producción)
$auth_required = true;

if ($auth_required) {
    $authorized = false;
    
    // Verificar si las credenciales están configuradas
    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        // Valores de prueba - en producción usar configuración real
        $admin_user = 'admin';
        $admin_pass = 'toolhub';
        
        if ($_SERVER['PHP_AUTH_USER'] === $admin_user && $_SERVER['PHP_AUTH_PW'] === $admin_pass) {
            $authorized = true;
        }
    }
    
    // Si no está autorizado, solicitar credenciales
    if (!$authorized) {
        header('WWW-Authenticate: Basic realm="ToolHub Web Admin"');
        header('HTTP/1.0 401 Unauthorized');
        echo '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Acceso denegado - ToolHub Web</title>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container py-5 text-center">
                <div class="alert alert-danger">
                    <h4>Acceso denegado</h4>
                    <p>Se requieren credenciales de administrador para acceder a esta página.</p>
                </div>
                <a href="../index.php" class="btn btn-primary">Volver al inicio</a>
            </div>
        </body>
        </html>';
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas de Uso - ToolHub Web</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-chart-line me-2 text-primary"></i>Estadísticas de Uso</h2>
            <a href="../index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
            </a>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Panel de Estadísticas</h5>
            </div>
            <div class="card-body">
                <?php render_usage_stats_page(); ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Configuración de Registros</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Estado del Sistema de Registro</h6>
                        <p>
                            <?php if (get_config('logging.enabled')): ?>
                                <span class="badge bg-success">ACTIVADO</span>
                            <?php else: ?>
                                <span class="badge bg-danger">DESACTIVADO</span>
                            <?php endif; ?>
                        </p>
                        
                        <h6>Archivo de Registros</h6>
                        <p>
                            <?php 
                            $log_file = TOOLHUB_BASE_DIR . '/' . get_config('logging.file');
                            if (file_exists($log_file)) {
                                echo '<span class="badge bg-success">DISPONIBLE</span>';
                                echo '<br><small class="text-muted">' . get_config('logging.file') . '</small>';
                                echo '<br><small class="text-muted">Tamaño: ' . number_format(filesize($log_file) / 1024, 2) . ' KB</small>';
                            } else {
                                echo '<span class="badge bg-warning">NO CREADO</span>';
                                echo '<br><small class="text-muted">' . get_config('logging.file') . '</small>';
                            }
                            ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6>Campos Registrados</h6>
                        <ul>
                            <?php foreach (get_config('logging.fields') as $field): ?>
                                <li><?php echo htmlspecialchars($field); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <h6>Tamaño Máximo de Archivo</h6>
                        <p><?php echo number_format(get_config('logging.max_size') / (1024*1024), 2); ?> MB</p>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Nota:</strong> La configuración del sistema de registro se puede modificar en el archivo <code>config.php</code>.
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
