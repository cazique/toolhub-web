<?php
// blacklist.php - Verificador de listas negras

function sanitize_domain($domain) {
    $domain = trim(strtolower($domain));
    $domain = preg_replace('(^https?://)', '', $domain);
    $domain = preg_replace('/^www\./', '', $domain);
    return explode('/', $domain)[0];
}

// Validación para IP
function is_valid_ip($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP) !== false;
}

$input = '';
$results = [];
$is_ip = false;
$error = '';

// Lista de comprobadores de blacklist (se pueden ampliar con APIs reales)
$blacklist_servers = [
    "spam.spamrats.com",
    "b.barracudacentral.org",
    "bl.spamcop.net",
    "zen.spamhaus.org",
    "dnsbl.sorbs.net",
    "dnsbl-1.uceprotect.net",
    "bl.emailbasura.org",
    "cbl.abuseat.org"
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['input'])) {
    $input = $_POST['input'];
    
    // Determinar si es IP o dominio
    if (is_valid_ip($input)) {
        $ip = $input;
        $is_ip = true;
    } else {
        $domain = sanitize_domain($input);
        $ip = gethostbyname($domain);
        
        if ($ip === $domain) {
            $error = "No se pudo resolver la IP para '$domain'.";
        }
    }
    
    if (empty($error)) {
        // Comprobar en listas negras
        // Para una demo, simulamos resultados para no sobrecargar servidores reales
        
        // Reversemos la IP para consultas DNSBL
        $ip_parts = explode('.', $ip);
        $reverse_ip = implode('.', array_reverse($ip_parts));
        
        $results = [];
        
        foreach ($blacklist_servers as $server) {
            // En un entorno real, se haría una consulta DNS real
            // $check = checkdnsrr("$reverse_ip.$server", 'A');
            
            // Para demo, generamos resultados aleatorios
            $status = (mt_rand(0, 10) < 1) ? false : true; // 10% de probabilidad de estar listado
            
            $results[] = [
                'server' => $server,
                'status' => $status ? 'clean' : 'listed',
                'message' => $status ? 'No listado' : 'IP/Dominio encontrado en blacklist'
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Blacklist Checker - ToolHub Web</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2 class="mb-4"><i class="fas fa-ban me-2 text-danger"></i>Blacklist Checker</h2>
    <p class="text-muted mb-4">Verifica si un dominio o IP está en listas negras de spam o malware.</p>
    
    <form method="post" class="mb-4">
      <div class="mb-3">
        <label for="input" class="form-label">Dominio o IP:</label>
        <input type="text" class="form-control" name="input" id="input" 
               placeholder="ejemplo.com o 8.8.8.8" required value="<?php echo htmlspecialchars($input); ?>">
      </div>
      <button type="submit" class="btn btn-danger">Verificar blacklists</button>
      <a href="../index.php" class="btn btn-outline-secondary">Volver al Dashboard</a>
    </form>
    
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif (!empty($results)): ?>
      <div class="card">
        <div class="card-header bg-danger text-white">
          <strong>Resultados para: <?php echo htmlspecialchars($input); ?> <?php if (!$is_ip): ?>(IP: <?php echo $ip; ?>)<?php endif; ?></strong>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between mb-4">
            <div>
              <?php 
                $listed = array_filter($results, function($item) { return $item['status'] === 'listed'; });
                $listedCount = count($listed);
                $totalCount = count($results);
              ?>
              <h5 class="mb-0">
                <?php if ($listedCount > 0): ?>
                  <span class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Listado en <?php echo $listedCount; ?> de <?php echo $totalCount; ?> servidores</span>
                <?php else: ?>
                  <span class="text-success"><i class="fas fa-check-circle me-2"></i>No listado en ningún servidor</span>
                <?php endif; ?>
              </h5>
              <div class="text-muted small mt-1">Verificación realizada: <?php echo date('d/m/Y H:i:s'); ?></div>
            </div>
            
            <div>
              <div class="progress" style="width: 180px; height: 30px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo (($totalCount - $listedCount) / $totalCount) * 100; ?>%">
                  <?php echo $totalCount - $listedCount; ?> OK
                </div>
                <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo ($listedCount / $totalCount) * 100; ?>%">
                  <?php echo $listedCount; ?> ❌
                </div>
              </div>
            </div>
          </div>
          
          <table class="table table-striped table-hover">
            <thead class="table-light">
              <tr>
                <th>Servidor</th>
                <th>Estado</th>
                <th>Mensaje</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($results as $result): ?>
                <tr>
                  <td><?php echo htmlspecialchars($result['server']); ?></td>
                  <td>
                    <?php if ($result['status'] === 'clean'): ?>
                      <span class="badge bg-success"><i class="fas fa-check me-1"></i>Limpio</span>
                    <?php else: ?>
                      <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Listado</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo htmlspecialchars($result['message']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          
          <div class="alert alert-warning mt-3 mb-0">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Nota:</strong> Por razones de demostración, esta herramienta muestra resultados simulados. En un entorno de producción,
            se realizarían consultas reales a las listas negras DNSBL.
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
