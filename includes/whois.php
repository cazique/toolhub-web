<?php
// whois.php - Herramienta WHOIS Lookup

// Incluir archivos necesarios
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/error_handler.php';
require_once dirname(__DIR__) . '/includes/offline_mode.php';
require_once dirname(__DIR__) . '/includes/usage_logger.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitiza el dominio recibido: quita protocolos, www y rutas adicionales.
 */
function sanitize_domain($domain) {
    $domain = trim(strtolower($domain));
    // Eliminar http:// o https://
    $domain = preg_replace('/^https?:\/\//i', '', $domain);
    // Eliminar www.
    $domain = preg_replace('/^www\./i', '', $domain);
    // Tomar sólo la parte antes de la primera barra (en caso de URL completa)
    $domain = explode('/', $domain)[0];
    return $domain;
}

/**
 * Consulta WHOIS mediante fsockopen.
 * Se utiliza una lista extensa de TLDs (más de 200 de los más usados).
 *
 * @param string $domain El dominio a consultar.
 * @return string La respuesta WHOIS o un mensaje de error.
 */
function whois_query($domain) {
    // Array extenso de TLDs y sus servidores WHOIS:
    $tld_servers = array(
        'ac'    => 'whois.nic.ac',
        'ad'    => 'whois.nic.ad',
        'ae'    => 'whois.aeda.net.ae',
        'af'    => 'whois.nic.af',
        'ag'    => 'whois.nic.ag',
        'ai'    => 'whois.ai',
        'al'    => 'whois.ripe.net',
        'am'    => 'whois.amnic.net',
        'ao'    => 'whois.centralnic.com',
        'aq'    => 'whois.iana.org',
        'ar'    => 'whois.nic.ar',
        'as'    => 'whois.nic.as',
        'asia'  => 'whois.nic.asia',
        'at'    => 'whois.nic.at',
        'au'    => 'whois.audns.net.au',
        'aw'    => 'whois.nic.aw',
        'ax'    => 'whois.ax',
        'az'    => 'whois.ripe.net',
        'ba'    => 'whois.ripe.net',
        'bb'    => 'whois.nic.bb',
        'bd'    => 'whois.btcl.net.bd',
        'be'    => 'whois.dns.be',
        'bf'    => 'whois.afilias.net',
        'bg'    => 'whois.register.bg',
        'bh'    => 'whois.bahrain.bh',
        'bi'    => 'whois.nic.bi',
        'biz'   => 'whois.nic.biz',
        'bj'    => 'whois.nic.bj',
        'bn'    => 'whois.bn',
        'bo'    => 'whois.nic.bo',
        'br'    => 'whois.registro.br',
        'bs'    => 'whois.bsnic.net',
        'bt'    => 'whois.bt',
        'bw'    => 'whois.nic.net.bw',
        'by'    => 'whois.cctld.by',
        'bz'    => 'whois.belizenic.bz',
        'ca'    => 'whois.cira.ca',
        'cat'   => 'whois.cat',
        'cc'    => 'whois.nic.cc',
        'cd'    => 'whois.nic.cd',
        'ch'    => 'whois.nic.ch',
        'ci'    => 'whois.nic.ci',
        'ck'    => 'whois.ck',
        'cl'    => 'whois.nic.cl',
        'cm'    => 'whois.netcom.cm',
        'cn'    => 'whois.cnnic.cn',
        'co'    => 'whois.nic.co',
        'com'   => 'whois.verisign-grs.com',
        'coop'  => 'whois.nic.coop',
        'cr'    => 'whois.nic.cr',
        'cu'    => 'whois.nic.cu',
        'cv'    => 'whois.cnv', 
        'cw'    => 'whois.nic.cw',
        'cx'    => 'whois.nic.cx',
        'cy'    => 'whois.nic.cy',
        'cz'    => 'whois.nic.cz',
        'de'    => 'whois.denic.de',
        'dj'    => 'whois.nic.dj',
        'dk'    => 'whois.dk-hostmaster.dk',
        'dm'    => 'whois.nic.dm',
        'do'    => 'whois.nic.do',
        'dz'    => 'whois.nic.dz',
        'ec'    => 'whois.nic.ec',
        'edu'   => 'whois.educause.edu',
        'ee'    => 'whois.eenet.ee',
        'eg'    => 'whois.ripe.net',
        'es'    => 'whois.nic.es',
        'eu'    => 'whois.eu',
        'fi'    => 'whois.ficora.fi',
        'fj'    => 'whois.fj',
        'fk'    => 'whois.fk',
        'fm'    => 'whois.nic.fm',
        'fo'    => 'whois.fo',
        'fr'    => 'whois.afnic.fr',
        'ga'    => 'whois.nic.ga',
        'gb'    => 'whois.nic.uk', // gb se usa ahora uk
        'gd'    => 'whois.nic.gd',
        'ge'    => 'whois.ge',
        'gg'    => 'whois.gg',
        'gh'    => 'whois.nic.gh',
        'gi'    => 'whois.gg',
        'gl'    => 'whois.global.tc',
        'gm'    => 'whois.gm',
        'gn'    => 'whois.nic.gn',
        'gov'   => 'whois.dotgov.gov',
        'gp'    => 'whois.nic.gp',
        'gq'    => 'whois.dominio.gq',
        'gr'    => 'whois.ics.forth.gr',
        'gs'    => 'whois.nic.gs',
        'gt'    => 'whois.gt',
        'gu'    => 'whois.gu',
        'gw'    => 'whois.nic.gw',
        'gy'    => 'whois.registry.gy',
        'hk'    => 'whois.hkirc.hk',
        'hm'    => 'whois.registry.hm',
        'hn'    => 'whois.nic.hn',
        'hr'    => 'whois.dns.hr',
        'ht'    => 'whois.nic.ht',
        'hu'    => 'whois.nic.hu',
        'id'    => 'whois.pandi.or.id',
        'ie'    => 'whois.domainregistry.ie',
        'il'    => 'whois.isoc.org.il',
        'im'    => 'whois.nic.im',
        'in'    => 'whois.registry.in',
        'info'  => 'whois.afilias.net',
        'int'   => 'whois.iana.org',
        'io'    => 'whois.nic.io',
        'iq'    => 'whois.cmc.iq',
        'ir'    => 'whois.nic.ir',
        'is'    => 'whois.isnic.is',
        'it'    => 'whois.nic.it',
        'je'    => 'whois.je',
        'jm'    => 'whois.nic.jm',
        'jo'    => 'whois.jo',
        'jobs'  => 'jobswhois.verisign-grs.com',
        'jp'    => 'whois.jprs.jp',
        'ke'    => 'whois.kenic.or.ke',
        'kg'    => 'whois.kg',
        'kh'    => 'whois.nic.kh',
        'ki'    => 'whois.nic.ki',
        'km'    => 'whois-com.km',
        'kn'    => 'whois.nic.kn',
        'kp'    => 'whois.kprss.or.kr',
        'kr'    => 'whois.kr',
        'kw'    => 'whois.kw',
        'ky'    => 'whois.ky',
        'kz'    => 'whois.nic.kz',
        'la'    => 'whois.nic.la',
        'lb'    => 'whois.lb',
        'lc'    => 'whois.nic.lc',
        'li'    => 'whois.nic.li',
        'lk'    => 'whois.nic.lk',
        'lr'    => 'whois.lr',
        'ls'    => 'whois.nic.ls',
        'lt'    => 'whois.domreg.lt',
        'lu'    => 'whois.dns.lu',
        'lv'    => 'whois.nic.lv',
        'ly'    => 'whois.nic.ly',
        'ma'    => 'whois.iam.net.ma',
        'mc'    => 'whois.mc',
        'md'    => 'whois.nic.md',
        'me'    => 'whois.nic.me',
        'mg'    => 'whois.nic.mg',
        'mh'    => 'whois.registrymh.net',
        'mil'   => 'whois.nic.mil',
        'mk'    => 'whois.ripe.net',
        'ml'    => 'whois.dotml.org',
        'mm'    => 'whois.nic.mm',
        'mn'    => 'whois.nic.mn',
        'mo'    => 'whois.monic.net.mo',
        'mobi'  => 'whois.dotmobiregistry.net',
        'mp'    => 'whois.nic.mp',
        'mq'    => 'whois.nic.net.mx',
        'mr'    => 'whois.nic.mr',
        'ms'    => 'whois.nic.ms',
        'mt'    => 'whois.nic.org.mt',
        'mu'    => 'whois.nic.mu',
        'museum'=> 'whois.museum',
        'mx'    => 'whois.mx',
        'my'    => 'whois.mynic.my',
        'mz'    => 'whois.nic.mz',
        'na'    => 'whois.na-nic.com.na',
        'name'  => 'whois.nic.name',
        'nc'    => 'whois.nc',
        'ne'    => 'whois.nic.ne',
        'net'   => 'whois.verisign-grs.com',
        'nf'    => 'whois.nic.nf',
        'ng'    => 'whois.nic.net.ng',
        'ni'    => 'whois.nic.ni',
        'nl'    => 'whois.domain-registry.nl',
        'no'    => 'whois.norid.no',
        'np'    => 'whois.np',
        'nr'    => 'whois.nr',
        'nu'    => 'whois.nic.nu',
        'nz'    => 'whois.srs.net.nz',
        'om'    => 'whois.registry.om',
        'org'   => 'whois.pir.org',
        'pa'    => 'whois.nic.pa',
        'pe'    => 'whois.nic.pe',
        'pf'    => 'whois.registry.pf',
        'pg'    => 'whois.nic.pg',
        'ph'    => 'whois.dot.ph',
        'pk'    => 'whois.pknic.net.pk',
        'pl'    => 'whois.dns.pl',
        'pm'    => 'whois.nic.pm',
        'pn'    => 'whois.pn',
        'post'  => 'whois.dotpostregistry.net',
        'pr'    => 'whois.nic.pr',
        'pro'   => 'whois.registrypro.pro',
        'ps'    => 'whois.pnina.ps',
        'pt'    => 'whois.dns.pt',
        'pw'    => 'whois.domreg.pw',
        'py'    => 'whois.nic.py',
        'qa'    => 'whois.registry.qa',
        're'    => 'whois.nic.re',
        'ro'    => 'whois.rotld.ro',
        'rs'    => 'whois.rnids.rs',
        'ru'    => 'whois.tcinet.ru',
        'rw'    => 'whois.ricta.org.rw',
        'sa'    => 'whois.nic.net.sa',
        'sb'    => 'whois.nic.net.sb',
        'sc'    => 'whois2.afilias-grs.net',
        'se'    => 'whois.iis.se',
        'sg'    => 'whois.sgnic.sg',
        'sh'    => 'whois.nic.sh',
        'si'    => 'whois.arnes.si',
        'sk'    => 'whois.sk-nic.sk',
        'sm'    => 'whois.nic.sm',
        'sn'    => 'whois.nic.sn',
        'so'    => 'whois.nic.so',
        'st'    => 'whois.nic.st',
        'su'    => 'whois.tcinet.ru',
        'sv'    => 'whois.sv',
        'sx'    => 'whois.sx',
        'sy'    => 'whois.tld.sy',
        'tc'    => 'whois.meridiantld.net',
        'td'    => 'whois.tld.td',
        'tel'   => 'whois.nic.tel',
        'tf'    => 'whois.nic.tf',
        'tg'    => 'whois.nic.tg',
        'th'    => 'whois.thnic.co.th',
        'tj'    => 'whois.nic.tj',
        'tk'    => 'whois.dot.tk',
        'tl'    => 'whois.tl',
        'tm'    => 'whois.nic.tm',
        'tn'    => 'whois.ati.tn',
        'to'    => 'whois.tonic.to',
        'tp'    => 'whois.nic.tp',
        'tr'    => 'whois.nic.tr',
        'travel'=> 'whois.nic.travel',
        'tt'    => 'whois.nic.tt',
        'tv'    => 'tvwhois.verisign-grs.com',
        'tw'    => 'whois.twnic.net.tw',
        'tz'    => 'whois.tznic.or.tz',
        'ua'    => 'whois.ua',
        'ug'    => 'whois.co.ug',
        'uk'    => 'whois.nic.uk',
        'us'    => 'whois.nic.us',
        'uy'    => 'whois.nic.org.uy',
        'uz'    => 'whois.cctld.uz',
        'va'    => 'whois.ripe.net',
        'vc'    => 'whois2.afilias-grs.net',
        've'    => 'whois.nic.ve',
        'vg'    => 'whois.adamsnames.tc',
        'wf'    => 'whois.nic.wf',
        'ws'    => 'whois.website.ws',
        'xxx'   => 'whois.nic.xxx',
        'ye'    => 'whois.yemen.net.ye',
        'yt'    => 'whois.nic.yt',
        'za'    => 'whois.registry.net.za'
        // La lista incluye ya más de 200 TLD. Puedes agregar o ajustar según lo requieras.
    );

    $parts = explode('.', $domain);
    $tld = strtolower(end($parts));

    if (!isset($tld_servers[$tld])) {
        return "❌ El TLD .$tld no está soportado por este cliente WHOIS.";
    }

    $server = $tld_servers[$tld];
    $port = 43;

    // Verificar que fsockopen esté habilitada
    if (!function_exists('fsockopen')) {
        return "❌ La función fsockopen() está deshabilitada en este servidor.";
    }

    $fp = @fsockopen($server, $port, $errno, $errstr, 10);
    if (!$fp) {
        return "❌ Error al conectar con el servidor WHOIS ($server): $errstr (Error $errno)";
    }

    // Enviar la consulta (el servidor WHOIS espera el dominio seguido de CRLF)
    fwrite($fp, $domain . "\r\n");

    $response = '';
    while (!feof($fp)) {
        $response .= fgets($fp, 128);
    }
    fclose($fp);

    return $response ? $response : "❌ No se recibió respuesta del servidor WHOIS.";
}

// Variables de proceso
$domain = '';
$result = '';
$error = '';

// Verificar si la herramienta está disponible en modo offline
if (is_offline() && !is_tool_available_offline('whois')) {
    $error = handle_error(
        "Esta herramienta no está disponible en modo offline. Por favor, activa tu conexión a Internet.",
        TOOLHUB_ERROR_WARNING,
        "Offline Mode"
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['domain'])) {
    $domain = sanitize_domain($_POST['domain']);

    try {
        // Realizar la consulta WHOIS usando fsockopen
        $whoisRaw = whois_query($domain);

        // Condición para detectar respuestas insuficientes
        if (stripos($whoisRaw, 'no match') !== false || strlen($whoisRaw) < 100) {
            $error = handle_error(
                "⚠️ El dominio no fue encontrado o el resultado es insuficiente.",
                TOOLHUB_ERROR_WARNING,
                "WHOIS Lookup"
            );
            log_tool_usage('whois', $domain, false);
        } else {
            $result = nl2br(htmlspecialchars($whoisRaw));
            log_tool_usage('whois', $domain, true);
        }
    } catch (Exception $e) {
        $error = handle_error(
            "Ocurrió un error al consultar el WHOIS para '$domain'.",
            TOOLHUB_ERROR_ERROR,
            "WHOIS Lookup",
            $e
        );
        log_tool_usage('whois', $domain, false);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>WHOIS Lookup - ToolHub Web</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
  <div class="container py-5">
    <!-- Banner de modo offline -->
    <?php display_offline_banner(); ?>
    
    <h2 class="mb-4"><i class="fas fa-search-location me-2 text-primary"></i>WHOIS Lookup</h2>
    <p class="text-muted mb-4">Consulta la información de registro de cualquier dominio.</p>
    
    <form method="post" class="mb-4">
      <div class="mb-3">
        <label for="domain" class="form-label">Dominio:</label>
        <input type="text" class="form-control" name="domain" id="domain" 
               placeholder="ejemplo.com" required 
               value="<?php echo htmlspecialchars($domain); ?>"
               <?php echo (is_offline() && !is_tool_available_offline('whois')) ? 'disabled' : ''; ?>>
      </div>
      <button type="submit" class="btn btn-primary" <?php echo (is_offline() && !is_tool_available_offline('whois')) ? 'disabled' : ''; ?>>
        Consultar
      </button>
      <a href="../index.php" class="btn btn-outline-secondary">Volver al Dashboard</a>
    </form>
    
    <?php 
    // Mostrar mensajes de error si existen
    if ($error) {
        display_error($error);
    }
    ?>
    
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

  <!-- Bootstrap JS y ClipboardJS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>
  <script>
    // Inicializar ClipboardJS para botón de copiar
    new ClipboardJS('.copy-btn');
    
    // Notificación de copiado
    document.addEventListener('DOMContentLoaded', function() {
      const copyBtn = document.querySelector('.copy-btn');
      if (copyBtn) {
        copyBtn.addEventListener('click', function() {
          const originalText = this.innerHTML;
          this.innerHTML = '<i class="fas fa-check"></i> Copiado';
          setTimeout(() => {
            this.innerHTML = originalText;
          }, 2000);
        });
      }
    });
  </script>
</body>
</html>
