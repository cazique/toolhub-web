<?php
// dns.php - Herramienta de DNS Lookup

function is_valid_domain($domain) {
    if (strpos($domain, '@') !== false || strpos($domain, ' ') !== false) {
        return false;
    }
    $domain = preg_replace('(^https?://)', '', $domain);
    $domain = preg_replace('/^www\./', '', $domain);
    // Validación básica de dominio
    return preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](\.[a-zA-Z]{2,})+$/', $domain);
}

function sanitize_domain($domain) {
    $domain = preg_replace('(^https?://)', '', $domain);
    $domain = preg_replace('/^www\./', '', $domain);
    $domain = strtolower(trim(explode('/', $domain)[0]));
    if (strpos($domain, '?') !== false) {
        $domain = explode('?', $domain)[0];
    }
    return $domain;
}

function get_dns_records($domain, $types = []) {
    $results = [];
    $error = null;
    
    if (empty($types)) {
        $types = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SOA', 'CAA'];
    }
    
    foreach ($types as $type) {
        try {
            $records = @dns_get_record($domain, constant('DNS_' . $type));
            if (!empty($records)) {
                $results[$type] = $records;
            }
        } catch (Exception $e) {
            // Log error silently
            error_log("DNS lookup error for {$domain}: " . $e->getMessage());
        }
    }
    
    if (empty($results)) {
        $ip = gethostbyname($domain);
        if ($ip === $domain) {
            $error = "No se encontraron registros DNS para '$domain' o el dominio no existe.";
        } else {
            $error = "No se encontraron registros para los tipos solicitados, pero el dominio parece existir.";
        }
    }
    
    return ['records' => $results, 'error' => $error];
}

function format_record_content($record, $type) {
    switch ($type) {
        case 'A': return $record['ip'];
        case 'AAAA': return $record['ipv6'];
        case 'CNAME': return $record['target'];
        case 'MX': return "{$record['target']} (Prioridad: {$record['pri']})";
        case 'NS': return $record['target'];
        case 'TXT': return $record['txt'];
        case 'SOA': return "MNAME: {$record['mname']} | Serial: {$record['serial']}";
        case 'CAA': return "Flags: {$record['flags']}, Tag: {$record['tag']}, Value: {$record['value']}";
        default: return json_encode($record);
    }
}

$domain = '';
$dns_data = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['domain'])) {
    $input = $_POST['domain'];
    $domain = sanitize_domain($input);

    if (is_valid_domain($domain)) {
        $types = isset($_POST['types']) ? $_POST['types'] : [];
        $result = get_dns_records($domain, $types);

        if ($result['error']) {
            $error = $result['error'];
        } else {
            $dns_data = $result['records'];
        }
    } else {
        $error = "Por favor, introduce un dominio válido.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DNS Lookup - ToolHub Web</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4"><i class="fas fa-network-wired me-2 text-success"></i>Consulta de registros DNS</h2>
        <p class="text-muted mb-4">Verifica todos los registros DNS de un dominio.</p>
        
        <form method="post">
            <div class="mb-3">
                <label for="domain" class="form-label">Dominio:</label>
                <input type="text" class="form-control" name="domain" id="domain" 
                       placeholder="ejemplo.com" value="<?php echo htmlspecialchars($domain); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipos de registros:</label>
                <div class="d-flex flex-wrap gap-3">
                    <?php
                    $types = ['A','AAAA','CNAME','MX','NS','TXT','SOA','CAA'];
                    $selected = $_POST['types'] ?? ['A','MX','NS'];
                    foreach ($types as $type) {
                        $checked = in_array($type, $selected) ? 'checked' : '';
                        echo "<div class='form-check form-switch'>";
                        echo "<input class='form-check-input' type='checkbox' name='types[]' id='dns-{$type}' value='{$type}' {$checked}>";
                        echo "<label class='form-check-label' for='dns-{$type}'>{$type}</label>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
            <button type="submit" class="btn btn-success">Consultar</button>
            <a href="../index.php" class="btn btn-outline-secondary">Volver al Dashboard</a>
        </form>

        <?php if ($error): ?>
            <div class="alert alert-danger mt-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($dns_data): ?>
            <h3 class="mt-5">Resultados para <strong><?php echo htmlspecialchars($domain); ?></strong></h3>
            <?php foreach ($dns_data as $type => $records): ?>
                <div class="card my-3">
                    <div class="card-header bg-success text-white">
                        <strong><?php echo $type; ?></strong> Records (<?php echo count($records); ?>)
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Contenido</th>
                                    <th>TTL</th>
                                    <th>Clase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $r): ?>
                                    <tr>
                                        <td><?php echo format_record_content($r, $type); ?></td>
                                        <td><?php echo $r['ttl'] ?? 'N/A'; ?></td>
                                        <td><?php echo $r['class'] ?? 'IN'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
