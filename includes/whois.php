<?php
// whois.php - Herramienta WHOIS Lookup

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['domain'])) {
    $domain = sanitize_domain($_POST['domain']);
    // Evitar inyección de comandos
    $safe_domain = escapeshellarg($domain);
    // Ejecutar comando whois
    $output = shell_exec("whois {$safe_domain}");
    $result = nl2br(htmlspecialchars($output));
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
    <h2 class="mb-4"><i class="fas fa-search-location me-2 text-primary"></i>WHOIS Lookup</h2>
    <p class="text-muted mb-4">Consulta la información de registro de cualquier dominio.</p>
    
    <form method="post" class="mb-4">
      <div class="mb-3">
        <label for="domain" class="form-label">Dominio:</label>
        <input type="text" class="form-control" name="domain" id="domain" 
               placeholder="ejemplo.com" required value="<?php echo htmlspecialchars($domain); ?>">
      </div>
      <button type="submit" class="btn btn-primary">Consultar</button>
      <a href="../index.php" class="btn btn-outline-secondary">Volver al Dashboard</a>
    </form>
    
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
  </script>
</body>
</html>
