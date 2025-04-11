<?php
// index.php - Dashboard principal para herramientas web

// Incluir archivos necesarios
require_once 'config.php';
require_once 'includes/error_handler.php';
require_once 'includes/offline_mode.php';
require_once 'includes/usage_logger.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Comprobar actualizaciones si estamos en modo online
$update_info = null;
if (!is_offline()) {
    $update_info = check_for_updates();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>ToolHub Web – Utilidades Técnicas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  
  <!-- Estilos personalizados -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="container py-5">
    <!-- Banner de modo offline -->
    <?php display_offline_banner(); ?>
    
    <!-- Banner de consentimiento de registro -->
    <?php display_consent_banner(); ?>
    
    <!-- Banner de actualización si hay disponible -->
    <?php if ($update_info && version_compare($update_info['version'], TOOLHUB_VERSION, '>')): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <div class="d-flex align-items-center">
        <div>
          <i class="fas fa-sync-alt me-2"></i>
          <strong>Nueva versión disponible:</strong> <?php echo htmlspecialchars($update_info['version']); ?>
          <a href="<?php echo htmlspecialchars($update_info['url']); ?>" class="alert-link" target="_blank">Ver detalles</a>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
    <?php endif; ?>
    
    <h1 class="text-center mb-2">
      <i class="fas fa-toolbox me-2 text-primary"></i>ToolHub Web
    </h1>
    <p class="text-center text-muted mb-1">
      Suite de utilidades técnicas para dominios, servidores, seguridad y análisis web
    </p>
    <p class="text-center small mb-3">
      <span class="badge bg-secondary">v<?php echo TOOLHUB_VERSION; ?></span>
      <div class="form-check form-switch dark-mode-switch">
        <input class="form-check-input" type="checkbox" id="dark-mode-toggle">
        <label class="form-check-label" for="dark-mode-toggle">
          <i class="fas fa-moon"></i> Modo oscuro
        </label>
      </div>
    </p>
    
    <div class="row mb-5">
      <div class="col-md-6 mx-auto">
        <div class="input-group">
          <input type="text" class="form-control" id="quick-search" placeholder="Búsqueda rápida...">
          <button class="btn btn-primary" type="button" id="quick-search-btn">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- WHOIS -->
      <div class="col-md-4">
        <a href="includes/whois.php" class="text-decoration-none">
          <div class="card tool-card border-primary">
            <div class="card-body text-center">
              <i class="fas fa-search-location fa-2x text-primary mb-2"></i>
              <h5 class="card-title">WHOIS Lookup</h5>
              <p class="card-text text-muted">Información del dominio y registrante</p>
            </div>
          </div>
        </a>
      </div>
      <!-- DNS -->
      <div class="col-md-4">
        <a href="includes/dns.php" class="text-decoration-none">
          <div class="card tool-card border-success">
            <div class="card-body text-center">
              <i class="fas fa-network-wired fa-2x text-success mb-2"></i>
              <h5 class="card-title">DNS Records</h5>
              <p class="card-text text-muted">Consulta A, MX, TXT, CNAME, NS, etc.</p>
            </div>
          </div>
        </a>
      </div>
      <!-- IP Lookup -->
      <div class="col-md-4">
        <a href="includes/iplookup.php" class="text-decoration-none">
          <div class="card tool-card border-warning">
            <div class="card-body text-center">
              <i class="fas fa-globe fa-2x text-warning mb-2"></i>
              <h5 class="card-title">IP Lookup / GeoIP</h5>
              <p class="card-text text-muted">Ubicación y proveedor de una IP o dominio</p>
            </div>
          </div>
        </a>
      </div>
      <!-- Headers -->
      <div class="col-md-4">
        <a href="includes/headers.php" class="text-decoration-none">
          <div class="card tool-card border-secondary">
            <div class="card-body text-center">
              <i class="fas fa-file-alt fa-2x text-secondary mb-2"></i>
              <h5 class="card-title">HTTP Headers</h5>
              <p class="card-text text-muted">Ver cabeceras devueltas por una web</p>
            </div>
          </div>
        </a>
      </div>
      <!-- SSL -->
      <div class="col-md-4">
        <a href="includes/ssl.php" class="text-decoration-none">
          <div class="card tool-card border-dark">
            <div class="card-body text-center">
              <i class="fas fa-lock fa-2x text-dark mb-2"></i>
              <h5 class="card-title">SSL Checker</h5>
              <p class="card-text text-muted">Verifica el certificado SSL de un dominio</p>
            </div>
          </div>
        </a>
      </div>
      <!-- Tecnologías -->
      <div class="col-md-4">
        <a href="includes/tech.php" class="text-decoration-none">
          <div class="card tool-card border-info">
            <div class="card-body text-center">
              <i class="fas fa-code fa-2x text-info mb-2"></i>
              <h5 class="card-title">CMS / Tecnologías</h5>
              <p class="card-text text-muted">Detecta qué CMS o librerías usa una web</p>
            </div>
          </div>
        </a>
      </div>
      <!-- Blacklist -->
      <div class="col-md-4">
        <a href="includes/blacklist.php" class="text-decoration-none">
          <div class="card tool-card border-danger">
            <div class="card-body text-center">
              <i class="fas fa-ban fa-2x text-danger mb-2"></i>
              <h5 class="card-title">Blacklist Checker</h5>
              <p class="card-text text-muted">¿Está el dominio en una lista negra?</p>
            </div>
          </div>
        </a>
      </div>

      <!-- Nuevas herramientas aquí -->
    </div>

    <!-- Sección de administración -->
    <div class="card mt-5 bg-light">
      <div class="card-body">
        <h5 class="card-title"><i class="fas fa-cog me-2"></i>Administración</h5>
        <div class="row">
          <div class="col-md-6">
            <a href="admin/stats.php" class="btn btn-outline-primary btn-sm">
              <i class="fas fa-chart-bar me-1"></i> Estadísticas de uso
            </a>
            
            <?php if (has_logging_consent()): ?>
            <button class="btn btn-outline-danger btn-sm" onclick="setLoggingConsent(false)">
              <i class="fas fa-times-circle me-1"></i> Revocar consentimiento de registro
            </button>
            <?php else: ?>
            <button class="btn btn-outline-success btn-sm" onclick="setLoggingConsent(true)">
              <i class="fas fa-check-circle me-1"></i> Permitir registro anónimo
            </button>
            <?php endif; ?>
          </div>
          <div class="col-md-6 text-md-end mt-2 mt-md-0">
            <?php if (is_offline()): ?>
            <button class="btn btn-outline-success btn-sm" onclick="checkConnectionAndReload()">
              <i class="fas fa-sync me-1"></i> Verificar conexión
            </button>
            <?php endif; ?>

            <a href="https://github.com/cazique/toolhub-web" target="_blank" class="btn btn-outline-dark btn-sm">
              <i class="fab fa-github me-1"></i> GitHub
            </a>
          </div>
        </div>
      </div>
    </div>

    <footer class="text-center text-muted mt-5">
      <hr>
      <p>© <?php echo date("Y"); ?> ToolHub Web | <a href="https://github.com/cazique/toolhub-web" target="_blank">GitHub</a></p>
    </footer>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/script.js"></script>
  
  <script>
  // Función para manejar el consentimiento de registro
  function setLoggingConsent(consent) {
    const value = consent ? "yes" : "no";
    document.cookie = "toolhub_logging_consent=" + value + ";path=/;max-age=31536000"; // 1 año
    location.reload();
  }
  
  // Función para verificar conexión y recargar
  function checkConnectionAndReload() {
    const btn = event.target;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Verificando...';
    btn.disabled = true;
    
    // Simular verificación (en producción, hacer una solicitud AJAX real)
    setTimeout(() => {
      location.reload();
    }, 1500);
  }
  </script>
