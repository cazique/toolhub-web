<?php
// headers.php - Verificación de cabeceras HTTP

function sanitize_url($url) {
    $url = trim($url);
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = 'http://' . $url;
    }
    return $url;
}

$url = '';
$headers = [];
$error = '';
$statusCode = '';
$statusClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url'])) {
    $url = sanitize_url($_POST['url']);
    
    // Configurar contexto para seguir redirecciones y establecer timeout
    $context = stream_context_create([
        'http' => [
            'follow_location' => 1,
            'timeout' => 5,
            'user_agent' => 'ToolHub Web Header Checker/1.0'
        ]
    ]);
    
    $headers = @get_headers($url, 1, $context);

    if ($headers === false) {
        $error = "No se pudieron obtener los headers para '$url'. Comprueba que la URL sea accesible.";
    } else {
        // Obtener el código de estado HTTP
        $statusLine = $headers[0];
        preg_match('/HTTP\/\d\.\d\s+(\d+)/', $statusLine, $matches);
        if (isset($matches[1])) {
            $statusCode = $matches[1];
            $statusClass = 'text-danger';
            
            if ($statusCode >= 200 && $statusCode < 300) {
                $statusClass = 'text-success';
            } else if ($statusCode >= 300 && $statusCode < 400) {
                $statusClass = 'text-warning';
            }
        }
    }
}

function format_header_value($value) {
    if (is_array($value)) {
        return implode('<br>', array_map('htmlspecialchars', $value));
    }
    return htmlspecialchars($value);
}

// Categorías de cabeceras para agruparlas
function get_header_category($header) {
    $header = strtolower($header);
    
    if (preg_match('/^(cache|expires|etag|last-modified|age)/', $header)) {
        return 'cache';
    } elseif (preg_match('/^(content|accept)/', $header)) {
        return 'content';
    } elseif (preg_match('/^(x-|cf-|server|powered-by)/', $header)) {
        return 'server';
    } elseif (preg_match('/^(strict-transport-security|content-security|x-xss|x-frame|x-content)/', $header)) {
        return 'security';
    } elseif (preg_match('/^(set-cookie|cookie)/', $header)) {
        return 'cookies';
    } else {
        return 'other';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>HTTP Headers Checker - ToolHub Web</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2 class="mb-4"><i class="fas fa-file-alt me-2 text-secondary"></i>HTTP Headers Checker</h2>
    <p class="text-muted mb-4">Analiza las cabeceras HTTP que devuelve cualquier sitio web.</p>
    
    <form method="post" class="mb-4">
      <div class="mb-3">
        <label for="url" class="form-label">URL:</label>
        <input type="text" class="form-control" name="url" id="url" 
               placeholder="ejemplo.com" required value="<?php echo htmlspecialchars($url); ?>">
      </div>
      <button type="submit" class="btn btn-secondary">Analizar cabeceras</button>
      <a href="../index.php" class="btn btn-outline-secondary">Volver al Dashboard</a>
    </form>
    
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif (!empty($headers)): ?>
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Cabeceras para: <strong><?php echo htmlspecialchars($url); ?></strong></span>
          <?php if (!empty($statusCode)): ?>
            <span class="badge <?php echo $statusClass; ?>"><?php echo $statusCode; ?></span>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <div class="accordion" id="headersAccordion">
            <?php
            // Agrupar por categorías
            $headersByCategory = [];
            
            foreach ($headers as $header => $value) {
              if (is_int($header)) continue; // Saltamos la línea de status HTTP
              
              $category = get_header_category($header);
              if (!isset($headersByCategory[$category])) {
                $headersByCategory[$category] = [];
              }
              
              $headersByCategory[$category][$header] = $value;
            }
            
            // Definir iconos e información para categorías
            $categories = [
              'security' => ['name' => 'Seguridad', 'icon' => 'shield-alt', 'desc' => 'Cabeceras relacionadas con la seguridad de la página'],
              'content' => ['name' => 'Contenido', 'icon' => 'file-alt', 'desc' => 'Información sobre el tipo y características del contenido'],
              'cache' => ['name' => 'Caché', 'icon' => 'clock', 'desc' => 'Cabeceras relacionadas con el caché y expiración de contenido'],
              'server' => ['name' => 'Servidor', 'icon' => 'server', 'desc' => 'Información sobre el servidor web y tecnologías utilizadas'],
              'cookies' => ['name' => 'Cookies', 'icon' => 'cookie-bite', 'desc' => 'Cookies enviadas por el servidor'],
              'other' => ['name' => 'Otras', 'icon' => 'ellipsis-h', 'desc' => 'Otras cabeceras no categorizadas']
            ];
            
            // Mostrar headers por categoría
            foreach ($categories as $catId => $category) {
              if (!isset($headersByCategory[$catId]) || empty($headersByCategory[$catId])) {
                continue;
              }
              
              echo '<div class="accordion-item">';
              echo '<h2 class="accordion-header" id="heading-' . $catId . '">';
              echo '<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-' . $catId . '" aria-expanded="true" aria-controls="collapse-' . $catId . '">';
              echo '<i class="fas fa-' . $category['icon'] . ' me-2"></i> ' . $category['name'] . ' <span class="badge bg-secondary ms-2">' . count($headersByCategory[$catId]) . '</span>';
              echo '</button>';
              echo '</h2>';
              echo '<div id="collapse-' . $catId . '" class="accordion-collapse collapse show" aria-labelledby="heading-' . $catId . '" data-bs-parent="#headersAccordion">';
              echo '<div class="accordion-body">';
              echo '<p class="text-muted small mb-3">' . $category['desc'] . '</p>';
              echo '<table class="table table-sm table-hover">';
              echo '<thead><tr><th width="30%">Cabecera</th><th>Valor</th></tr></thead>';
              echo '<tbody>';
              
              foreach ($headersByCategory[$catId] as $header => $value) {
                echo '<tr>';
                echo '<td><strong>' . htmlspecialchars($header) . '</strong></td>';
                echo '<td>' . format_header_value($value) . '</td>';
                echo '</tr>';
              }
              
              echo '</tbody>';
              echo '</table>';
              echo '</div>';
              echo '</div>';
              echo '</div>';
            }
            ?>
          </div>
          
          <!-- Evaluación de seguridad básica -->
          <?php 
          // Comprobar cabeceras de seguridad comunes
          $securityHeaders = [
            'Strict-Transport-Security' => false,
            'Content-Security-Policy' => false,
            'X-Content-Type-Options' => false,
            'X-Frame-Options' => false,
            'X-XSS-Protection' => false,
            'Referrer-Policy' => false
          ];
          
          foreach ($headers as $header => $value) {
            if (is_int($header)) continue;
            
            $headerLower = strtolower($header);
            foreach ($securityHeaders as $secHeader => $found) {
              if (strtolower($secHeader) === $headerLower) {
                $securityHeaders[$secHeader] = true;
                break;
              }
            }
          }
          
          // Contar cuántas cabeceras de seguridad están presentes
          $securityScore = array_sum(array_map('intval', $securityHeaders));
          $maxScore = count($securityHeaders);
          $scorePercentage = ($securityScore / $maxScore) * 100;
          
          // Determinar clase y mensaje según puntuación
          if ($scorePercentage >= 80) {
            $scoreClass = 'success';
            $scoreMessage = 'Buena configuración de seguridad';
          } elseif ($scorePercentage >= 50) {
            $scoreClass = 'warning';
            $scoreMessage = 'Configuración de seguridad mejorable';
          } else {
            $scoreClass = 'danger';
            $scoreMessage = 'Configuración de seguridad débil';
          }
          ?>
          
          <div class="card mt-4">
            <div class="card-header">Evaluación de cabeceras de seguridad</div>
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="progress flex-grow-1 me-3" style="height: 25px;">
                  <div class="progress-bar bg-<?php echo $scoreClass; ?>" role="progressbar" 
                       style="width: <?php echo $scorePercentage; ?>%" 
                       aria-valuenow="<?php echo $securityScore; ?>" 
                       aria-valuemin="0" 
                       aria-valuemax="<?php echo $maxScore; ?>">
                    <?php echo $securityScore; ?>/<?php echo $maxScore; ?>
                  </div>
                </div>
                <span class="badge bg-<?php echo $scoreClass; ?>"><?php echo $scoreMessage; ?></span>
              </div>
              
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>Cabecera de seguridad</th>
                      <th>Estado</th>
                      <th>Descripción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $headerDescriptions = [
                      'Strict-Transport-Security' => 'Fuerza conexiones HTTPS (HSTS)',
                      'Content-Security-Policy' => 'Previene ataques XSS y de inyección',
                      'X-Content-Type-Options' => 'Previene ataques de MIME sniffing',
                      'X-Frame-Options' => 'Protege contra clickjacking',
                      'X-XSS-Protection' => 'Filtro para ataques XSS',
                      'Referrer-Policy' => 'Controla información en cabecera Referer'
                    ];
                    
                    foreach ($securityHeaders as $header => $present) {
                      echo '<tr>';
                      echo '<td><code>' . $header . '</code></td>';
                      if ($present) {
                        echo '<td><span class="badge bg-success"><i class="fas fa-check"></i> Presente</span></td>';
                      } else {
                        echo '<td><span class="badge bg-danger"><i class="fas fa-times"></i> Ausente</span></td>';
                      }
                      echo '<td>' . $headerDescriptions[$header] . '</td>';
                      echo '</tr>';
                    }
                    ?>
                  </tbody>
                </table>
              </div>
              
              <div class="alert alert-info mt-3 mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Las cabeceras de seguridad son importantes para proteger tu sitio web contra ataques comunes.
                Considera implementar las cabeceras faltantes para mejorar la seguridad.
              </div>
            </div>
          </div>
          
          <!-- Botón para mostrar cabeceras en formato crudo -->
          <div class="text-center mt-4">
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#rawHeaders">
              Ver cabeceras en formato crudo
            </button>
          </div>
          
          <div class="collapse mt-3" id="rawHeaders">
            <div class="card card-body">
              <pre><?php print_r($headers); ?></pre>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
