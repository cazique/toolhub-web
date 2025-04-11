<?php
// index.php - Dashboard principal para herramientas web
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
    <h1 class="text-center mb-4">
      <i class="fas fa-toolbox me-2 text-primary"></i>ToolHub Web
    </h1>
    <p class="text-center text-muted mb-5">
      Suite de utilidades técnicas para dominios, servidores, seguridad y análisis web
    </p>

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
    </div>

    <footer class="text-center text-muted mt-5">
      <hr>
      <p>© <?php echo date("Y"); ?> ToolHub Web | <a href="https://github.com/cazique/toolhub-web" target="_blank">GitHub</a></p>
    </footer>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/script.js"></script>
</body>
</html>
