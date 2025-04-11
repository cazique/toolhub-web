<?php
// whois.php - Herramienta WHOIS Lookup

// Incluir archivos necesarios
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/error_handler.php';
require_once dirname(__DIR__) . '/includes/offline_mode.php';
require_once dirname(__DIR__) . '/includes/usage_logger.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sanitize_domain($domain) {
    $domain = trim(strtolower($domain));
    // Eliminar URL http(s):// y www.
    $domain = preg_replace('(^https?://)', '', $domain);
    $domain = preg_replace('/^www\./', '', $domain);
    $domain = explode('/', $domain)[0];
    return $domain;
}

$domain = '';
$result = '';
$error = '';

// Verificar si la herramienta está disponible en modo offline
if (is_offline() && !is_tool_available_offline('whois')) {
    $error = handle_error(
        "Esta herramienta no está disponible en modo offline. Por favor, activa tu conexión a Internet.",
        TOOLHUB_ERROR_WARNING,
        "Offline Mode"
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['domain'])) {
    $domain = sanitize_domain($_POST['domain']);
    
    try {
        // Evitar inyección de comandos
        $safe_domain = escapeshellarg($domain);
        
        // Obtener comando whois de configuración
        $whois_command = get_config('tools.whois.command');
        
        // Ejecutar comando whois
        $output = shell_exec("{$whois_command} {$safe_domain}");
        
        if ($output === null) {
            $error = handle_error(
                "No se pudo ejecutar el comando WHOIS. Verifica que esté instalado en el servidor.",
                TOOLHUB_ERROR_ERROR,
                "Command Execution"
            );
        } else {
            $result = nl2br(htmlspecialchars($output));
            
            // Registrar uso exitoso
            log_tool_usage('whois', $domain, true);
        }
    } catch (Exception $e) {
        $error = handle_error(
            "Ocurrió un error al consultar el WHOIS para '$domain'.",
            TOOLHUB_ERROR_ERROR,
            "WHOIS Lookup",
            $e
        );
        
        // Registrar uso fallido
        log_tool_usage('whois', $domain, false);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>WHOIS Lookup - ToolHub Web</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
  <div class="container py-5">
    <!-- Banner de modo offline -->
    <?php display_offline_banner(); ?>
    
    <h2 class="mb-4"><i class="fas fa-search-location me-2 text-primary"></i>WHOIS Lookup</h2>
    <p class="text-muted mb-4">Consulta la información de registro de cualquier dominio.</p>
    
    <form method="post" class="mb-4">
      <div class="mb-3">
        <label for="domain" class="form-label">Dominio:</label>
        <input type="text" class="form-control" name="domain" id="domain" 
               placeholder="ejemplo.com" required value="<?php echo htmlspecialchars($domain); ?>"
               <?php echo (is_offline() && !is_tool_available_offline('whois')) ? 'disabled' : ''; ?>>
      </div>
      <button type="submit" class="btn btn-primary" <?php echo (is_offline() && !is_tool_available_offline('whois')) ? 'disabled' : ''; ?>>
        Consultar
      </button>
      <a href="../index.php" class="btn btn-outline-secondary">Volver al Dashboard</a>
    </form>
    
    <?php 
    // Mostrar mensajes de error si existen
    if ($error) {
        display_error($error);
    }
    ?>
    
    <?php if ($result): ?>
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Resultados WHOIS para: <strong><?php echo htmlspecialchars($domain); ?></strong></span>
          <button class="btn btn-sm btn-outline-primary copy-btn" data-clipboard-target="#whois-result">
            <i class="fas fa-copy"></i> Copiar
          </button>
        </div>
        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
          <pre id="whois-result"><?php echo $result; ?></pre>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>
  <script>
    // Inicializar clipboard para botón de copiar
    new ClipboardJS('.copy-btn');
    
    // Notificación de copiado
    document.addEventListener('DOMContentLoaded', function() {
      const copyBtn = document.querySelector('.copy-btn');
      if (copyBtn) {
        copyBtn.addEventListener('click', function() {
          const originalText = this.innerHTML;
          this.innerHTML = '<i class="fas fa-check"></i> Copiado';
          setTimeout(() => {
            this.innerHTML = originalText;
          }, 2000);
        });
      }
    });
  </script>
</body>
</html>
