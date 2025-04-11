<?php
// tech.php - Detector de tecnologías web

function sanitize_url($url) {
    $url = trim($url);
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = 'http://' . $url;
    }
    return $url;
}

$url = '';
$techInfo = [];
$techs = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url'])) {
    $url = sanitize_url($_POST['url']);
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'ToolHub Web Tech Detector/1.0'
        ]
    ]);
    
    $html = @file_get_contents($url, false, $context);

    if ($html === false) {
        $error = "No se pudo acceder a la URL. Comprueba que sea accesible.";
    } else {
        // Almacenar las tecnologías detectadas
        $techInfo = [];
        
        // 1. Detectar CMS por meta generator
        if (preg_match('/<meta\s+name=["\']generator["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            $techInfo[] = [
                'type' => 'CMS / Framework',
                'name' => $matches[1],
                'icon' => 'fas fa-cubes'
            ];
        }
        
        // 2. Detectar WordPress
        if (strpos($html, 'wp-content') !== false || strpos($html, 'wp-includes') !== false) {
            $techInfo[] = [
                'type' => 'CMS',
                'name' => 'WordPress',
                'icon' => 'fab fa-wordpress'
            ];
        }
        
        // 3. Detectar Joomla
        if (strpos($html, 'joomla') !== false || preg_match('/content="Joomla/i', $html)) {
            $techInfo[] = [
                'type' => 'CMS',
                'name' => 'Joomla',
                'icon' => 'fab fa-joomla'
            ];
        }
        
        // 4. Detectar Drupal
        if (strpos($html, 'drupal') !== false || preg_match('/Drupal.settings/i', $html)) {
            $techInfo[] = [
                'type' => 'CMS',
                'name' => 'Drupal',
                'icon' => 'fab fa-drupal'
            ];
        }
        
        // 5. Detectar Frameworks JS
        if (strpos($html, 'react') !== false || strpos($html, 'reactjs') !== false) {
            $techInfo[] = [
                'type' => 'Framework JS',
                'name' => 'React',
                'icon' => 'fab fa-react'
            ];
        }
        
        if (strpos($html, 'vue') !== false || strpos($html, 'vuejs') !== false) {
            $techInfo[] = [
                'type' => 'Framework JS',
                'name' => 'Vue.js',
                'icon' => 'fab fa-vuejs'
            ];
        }
        
        if (strpos($html, 'angular') !== false) {
            $techInfo[] = [
                'type' => 'Framework JS',
                'name' => 'Angular',
                'icon' => 'fab fa-angular'
            ];
        }
        
        // 6. Detectar jQuery
        if (strpos($html, 'jquery') !== false) {
            $techInfo[] = [
                'type' => 'Biblioteca JS',
                'name' => 'jQuery',
                'icon' => 'fas fa-code'
            ];
        }
        
        // 7. Detectar Bootstrap
        if (strpos($html, 'bootstrap') !== false) {
            $techInfo[] = [
                'type' => 'Framework CSS',
                'name' => 'Bootstrap',
                'icon' => 'fab fa-bootstrap'
            ];
        }
        
        // 8. Detectar servidores comunes
        $headers = get_headers($url, 1);
        if ($headers !== false) {
            if (isset($headers['Server'])) {
                $server = is_array($headers['Server']) ? $headers['Server'][0] : $headers['Server'];
                $techInfo[] = [
                    'type' => 'Servidor',
                    'name' => $server,
                    'icon' => 'fas fa-server'
                ];
            }
            
            if (isset($headers['X-Powered-By'])) {
                $powered = is_array($headers['X-Powered-By']) ? $headers['X-Powered-By'][0] : $headers['X-Powered-By'];
                $techInfo[] = [
                    'type' => 'Tecnología de servidor',
                    'name' => $powered,
                    'icon' => 'fas fa-cog'
                ];
            }
        }
        
        // Si no se encontró ninguna tecnología
        if (empty($techInfo)) {
            $techInfo[] = [
                'type' => 'Información',
                'name' => 'No se detectaron tecnologías conocidas',
                'icon' => 'fas fa-info-circle'
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Detector de Tecnologías - ToolHub Web</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2 class="mb-4"><i class="fas fa-code me-2 text-info"></i>Detector de Tecnologías</h2>
    <p class="text-muted mb-4">Identifica qué tecnologías, CMS o frameworks utiliza un sitio web.</p>
    
    <form method="post" class="mb-4">
      <div class="mb-3">
        <label for="url" class="form-label">URL del sitio web:</label>
        <input type="text" class="form-control" name="url" id="url" 
               placeholder="ejemplo.com" required value="<?php echo htmlspecialchars($url); ?>">
      </div>
      <button type="submit" class="btn btn-info text-white">Detectar tecnologías</button>
      <a href="../index.php" class="btn btn-outline-secondary">Volver al Dashboard</a>
    </form>
    
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif (!empty($techInfo)): ?>
      <div class="card">
        <div class="card-header bg-info text-white">
          <strong>Tecnologías detectadas en: <?php echo htmlspecialchars($url); ?></strong>
        </div>
        <div class="card-body">
          <div class="row">
            <?php foreach ($techInfo as $tech): ?>
            <div class="col-md-6 mb-3">
              <div class="border rounded p-3 h-100">
                <h5>
                  <i class="<?php echo $tech['icon']; ?> me-2"></i>
                  <?php echo htmlspecialchars($tech['name']); ?>
                </h5>
                <div class="text-muted small"><?php echo htmlspecialchars($tech['type']); ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          
          <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle me-2"></i>
            Esta herramienta realiza una detección básica basada en marcadores comunes en el código HTML y cabeceras HTTP.
            La detección puede no ser exhaustiva.
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
