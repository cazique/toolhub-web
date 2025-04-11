<?php
// ssl.php - Verificación de certificados SSL

function sanitize_url($url) {
    $url = trim($url);
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = 'https://' . $url;
    }
    return $url;
}

$url = '';
$certInfo = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url'])) {
    $url = sanitize_url($_POST['url']);
    $host = parse_url($url, PHP_URL_HOST);
    $port = 443;

    $ctx = stream_context_create(["ssl" => ["capture_peer_cert" => true, "verify_peer" => false]]);
    $client = @stream_socket_client("ssl://$host:$port", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $ctx);

    if (!$client) {
        $error = "No se pudo conectar a $host:$port. Error: $errstr ($errno)";
    } else {
        $params = stream_context_get_params($client);
        $cert = $params["options"]["ssl"]["peer_certificate"] ?? false;
        if ($cert) {
            $certInfo = openssl_x509_parse($cert);
        } else {
            $error = "No se pudo obtener el certificado SSL.";
        }
        fclose($client);
    }
}

// Función para formatear fechas del certificado
function formatCertDate($timestamp) {
    return date('d/m/Y H:i:s', $timestamp);
}

// Función para validar si el certificado está vigente
function getCertStatus($validFrom, $validTo) {
    $now = time();
    if ($now < $validFrom) {
        return ['status' => 'warning', 'message' => 'El certificado aún no es válido'];
    } elseif ($now > $validTo) {
        return ['status' => 'danger', 'message' => 'El certificado ha expirado'];
    } else {
        // Calcular días restantes
        $daysLeft = ceil(($validTo - $now) / 86400);
        if ($daysLeft <= 15) {
            return ['status' => 'warning', 'message' => "El certificado expira pronto ($daysLeft días)"];
        } else {
            return ['status' => 'success', 'message' => "El certificado es válido ($daysLeft días restantes)"];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>SSL Checker - ToolHub Web</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2 class="mb-4"><i class="fas fa-lock me-2 text-dark"></i>SSL Checker</h2>
    <p class="text-muted mb-4">Verifica y analiza certificados SSL de cualquier dominio.</p>
    
    <form method="post" class="mb-4">
      <div class="mb-3">
        <label for="url" class="form-label">Dominio o URL:</label>
        <input type="text" class="form-control" name="url" id="url" 
               placeholder="ejemplo.com" required value="<?php echo htmlspecialchars($url); ?>">
        <div class="form-text">Si no se especifica el protocolo, se usará HTTPS por defecto</div>
      </div>
      <button type="submit" class="btn btn-dark">Verificar SSL</button>
      <a href="../index.php" class="btn btn-outline-secondary">Volver al Dashboard</a>
    </form>
    
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif (!empty($certInfo)): ?>
      <?php
        $certStatus = getCertStatus($certInfo['validFrom_time_t'], $certInfo['validTo_time_t']);
      ?>
      <div class="card mb-4">
        <div class="card-header bg-dark text-white">
          <strong>Certificado SSL de: <?php echo htmlspecialchars($host); ?></strong>
        </div>
        <div class="card-body">
          <div class="alert alert-<?php echo $certStatus['status']; ?>">
            <i class="fas fa-<?php echo $certStatus['status'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $certStatus['message']; ?>
          </div>
          
          <h5 class="mt-3">Información General</h5>
          <table class="table table-striped">
            <tr>
              <th width="30%">Nombre Común (CN)</th>
              <td><?php echo htmlspecialchars($certInfo['subject']['CN'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
              <th>Organización</th>
              <td><?php echo htmlspecialchars($certInfo['subject']['O'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
              <th>Emitido Por</th>
              <td><?php echo htmlspecialchars($certInfo['issuer']['O'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
              <th>Válido Desde</th>
              <td><?php echo formatCertDate($certInfo['validFrom_time_t']); ?></td>
            </tr>
            <tr>
              <th>Válido Hasta</th>
              <td><?php echo formatCertDate($certInfo['validTo_time_t']); ?></td>
            </tr>
            <tr>
              <th>Algoritmo de Firma</th>
              <td><?php echo htmlspecialchars($certInfo['signatureTypeSN'] ?? 'N/A'); ?></td>
            </tr>
          </table>
          
          <?php if (!empty($certInfo['extensions']['subjectAltName'])): ?>
            <h5 class="mt-4">Nombres Alternativos (SAN)</h5>
            <div class="bg-light p-3 rounded">
              <?php 
                $sans = explode(', ', $certInfo['extensions']['subjectAltName']);
                foreach ($sans as $san) {
                  $san = str_replace('DNS:', '', $san);
                  echo "<span class='badge bg-secondary me-2 mb-2'>" . htmlspecialchars($san) . "</span>";
                }
              ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
