/**
 * Script principal para ToolHub Web
 */

document.addEventListener("DOMContentLoaded", function() {
  // Efecto hover mejorado para las tarjetas de herramientas
  const toolCards = document.querySelectorAll('.tool-card');
  
  toolCards.forEach(card => {
    card.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-8px)';
    });
    
    card.addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0)';
    });
  });
  
  // Inicializar tooltips de Bootstrap si existen
  if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
      new bootstrap.Tooltip(tooltip);
    });
  }
  
  // Mensaje de copia al portapapeles
  const copyBtns = document.querySelectorAll('.copy-btn');
  
  if (copyBtns.length > 0 && typeof ClipboardJS !== 'undefined') {
    const clipboard = new ClipboardJS('.copy-btn');
    
    clipboard.on('success', function(e) {
      const originalText = e.trigger.innerHTML;
      e.trigger.innerHTML = '<i class="fas fa-check"></i> Copiado';
      
      setTimeout(function() {
        e.trigger.innerHTML = originalText;
      }, 2000);
      
      e.clearSelection();
    });
  }
  
  // Validación para formularios
  const forms = document.querySelectorAll('form');
  
  forms.forEach(form => {
    form.addEventListener('submit', function(event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      
      form.classList.add('was-validated');
    }, false);
  });
  
  // Manejo de banners cerrados
  const alertBanners = document.querySelectorAll('.alert-dismissible');
  alertBanners.forEach(banner => {
    const closeBtn = banner.querySelector('.btn-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', function() {
        const bannerId = banner.getAttribute('data-banner-id');
        if (bannerId) {
          localStorage.setItem('banner_' + bannerId + '_closed', 'true');
        }
      });
    }
  });
  
  // Restaurar estado de consentimiento
  const consentBanner = document.getElementById('consent-banner');
  if (consentBanner && checkCookie('toolhub_logging_consent')) {
    consentBanner.style.display = 'none';
  }
  
  // Botones de descarga de resultados
  const downloadButtons = document.querySelectorAll('.download-results');
  downloadButtons.forEach(button => {
    button.addEventListener('click', function() {
      const contentId = this.getAttribute('data-content');
      const contentElement = document.getElementById(contentId);
      const fileName = this.getAttribute('data-filename') || 'toolhub-results.txt';
      
      if (contentElement) {
        downloadText(contentElement.innerText, fileName);
      }
    });
  });

  // Modo oscuro (dark mode)
  const darkModeToggle = document.getElementById('dark-mode-toggle');
  if (darkModeToggle) {
    // Verificar preferencia almacenada
    const prefersDarkMode = localStorage.getItem('dark-mode') === 'true';
    
    // Verificar preferencia del sistema
    const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Aplicar modo oscuro si está guardado o el sistema lo prefiere por defecto
    if (prefersDarkMode || (systemPrefersDark && localStorage.getItem('dark-mode') === null)) {
      document.body.classList.add('dark-mode');
      if (darkModeToggle.checked !== undefined) {
        darkModeToggle.checked = true;
      }
    }
    
    // Manejar cambio de modo
    darkModeToggle.addEventListener('change', function() {
      if (this.checked) {
        document.body.classList.add('dark-mode');
        localStorage.setItem('dark-mode', 'true');
      } else {
        document.body.classList.remove('dark-mode');
        localStorage.setItem('dark-mode', 'false');
      }
    });
  }

  // Inicialización de gráficos dinámicos si existen
  initializeCharts();
  
  console.log("ToolHub Web v" + (window.TOOLHUB_VERSION || "0.2") + " cargado correctamente");
});

/**
 * Descargar contenido como archivo de texto
 */
function downloadText(text, filename) {
  const element = document.createElement('a');
  element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
  element.setAttribute('download', filename);
  element.style.display = 'none';
  document.body.appendChild(element);
  element.click();
  document.body.removeChild(element);
}

/**
 * Verificar si una cookie existe
 */
function checkCookie(name) {
  const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  return match !== null;
}

/**
 * Inicializar gráficos en la página si existen
 */
function initializeCharts() {
  // Solo se inicializa si Chart.js está disponible
  if (typeof Chart === 'undefined') {
    return;
  }
  
  // Buscar canvas de gráficos
  const chartCanvases = document.querySelectorAll('[data-chart-type]');
  
  chartCanvases.forEach(canvas => {
    const chartType = canvas.getAttribute('data-chart-type');
    const chartData = JSON.parse(canvas.getAttribute('data-chart-data') || '{}');
    const chartOptions = JSON.parse(canvas.getAttribute('data-chart-options') || '{}');
    
    // Crear el gráfico con los datos proporcionados
    new Chart(canvas, {
      type: chartType,
      data: chartData,
      options: chartOptions
    });
  });
}

/**
 * Verificar conexión a Internet
 */
function checkInternetConnection(callback) {
  const xhr = new XMLHttpRequest();
  const randomNum = Math.floor(Math.random() * 10000);
  const timeoutDuration = 5000; // 5 segundos
  let timeout;
  
  // Manejar timeout
  timeout = setTimeout(function() {
    xhr.abort();
    callback(false); // Sin conexión
  }, timeoutDuration);
  
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      clearTimeout(timeout);
      callback(xhr.status === 200); // Conexión exitosa
    }
  };
  
  // Hacer una solicitud a un recurso confiable
  xhr.open('HEAD', 'https://www.cloudflare.com/cdn-cgi/trace?' + randomNum, true);
  xhr.send();
}
