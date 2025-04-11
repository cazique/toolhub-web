<?php
// iplookup.php - IP Lookup & Geolocalización

function sanitize_domain($domain) {
    $domain = trim(strtolower($domain));
    $domain = preg_replace('(^https?://)', '', $domain);
    $domain = preg_replace('/^www\./', '', $domain);
    return explode('/', $domain)[0];
}

// Validación para IP v4 e IP v6
function is_valid_ip($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP) !== false;
}

$input = '';
$ipInfo = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['input'])) {
    $input = $_POST['input'];
    
    // Determinar si es una IP o un dominio
    if (is_valid_ip($input)) {
        $ip = $input;
    } else {
        $domain = sanitize_domain($input);
        $ip = gethostbyname($domain);
        
        if ($ip === $domain) {
            $error = "No se pudo resolver la IP de '$domain'.";
        }
    }
    
    if (empty($error)) {
        // Usar ip-api.com para obtener información geográfica
        $url = "http://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city,zip,lat,lon,timezone,isp,org,as,query";
        $json = @file_get_contents($url);
        $data = json_decode($json, true);
        
        if ($data && $data['status'] === 'success') {
            $ipInfo = $data;
        } else {
            $error = "No se pudo obtener la geolocalización para $ip.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>IP Lookup / GeoIP - ToolHub Web</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2 class="mb-4"><i class="fas fa-globe me-2 text-warning"></i>IP Lookup / Geolocalización</h2>
    <p class="text-muted mb-4">Consulta información geográfica y de red para cualquier dominio o IP.</p>
    
    <form method="post" class="mb-4">
      <div class="mb-3">
        <label for="input" class="form-label">Dominio o IP:</label>
        <input type="text" class="form-control" name="input" id="input" 
               placeholder="ejemplo.com o 8.8.8.8" required value="<?php echo htmlspecialchars($input); ?>">
      </div>
      <button type="submit" class="btn btn-warning text-dark">Consultar</button>
      <a href="../index.php" class="btn btn-outline-secondary">Volver al Dashboard</a>
    </form>
    
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($ipInfo): ?>
      <div class="card">
        <div class="card-header bg-warning text-dark">
          <strong>Información para: <?php echo htmlspecialchars($ipInfo['query']); ?></strong>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <ul class="list-group mb-3">
                <li class="list-group-item d-flex justify-content-between">
                  <span><i class="fas fa-flag me-2"></i>País:</span>
                  <strong><?php echo htmlspecialchars($ipInfo['country']); ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span><i class="fas fa-map-marker-alt me-2"></i>Región:</span>
                  <strong><?php echo htmlspecialchars($ipInfo['regionName']); ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span><i class="fas fa-city me-2"></i>Ciudad:</span>
                  <strong><?php echo htmlspecialchars($ipInfo['city']); ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span><i class="fas fa-mail-bulk me-2"></i>Código Postal:</span>
                  <strong><?php echo htmlspecialchars($ipInfo['zip']); ?></strong>
                </li>
              </ul>
            </div>
            <div class="col-md-6">
              <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between">
                  <span><i class="fas fa-clock me-2"></i>Zona Horaria:</span>
                  <strong><?php echo htmlspecialchars($ipInfo['timezone']); ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span><i class="fas fa-network-wired me-2"></i>ISP:</span>
                  <strong><?php echo htmlspecialchars($ipInfo['isp']); ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span><i class="fas fa-building me-2"></i>Organización:</span>
                  <strong><?php echo htmlspecialchars($ipInfo['org']); ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span><i class="fas fa-server me-2"></i>ASN:</span>
                  <strong><?php echo htmlspecialchars($ipInfo['as']); ?></strong>
                </li>
              </ul>
            </div>
          </div>
          
          <?php if (isset($ipInfo['lat']) && isset($ipInfo['lon'])): ?>
          <div class="mt-4">
            <h5>Mapa de localización</h5>
            <div class="ratio ratio-16x9">
              <iframe 
                src="https://maps.google.com/maps?q=<?php echo $ipInfo['lat']; ?>,<?php echo $ipInfo['lon']; ?>&z=10&output=embed" 
                allowfullscreen>
              </iframe>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
