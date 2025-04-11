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
          <?php
