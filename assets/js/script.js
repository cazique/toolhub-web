/**
 * Script principal para ToolHub Web
 * @author 
 */

/* global bootstrap, ClipboardJS, Chart, TOOLHUB_VERSION */

document.addEventListener("DOMContentLoaded", function () {
  // Efecto hover para tarjetas
  document.querySelectorAll('.tool-card').forEach(card => {
    card.addEventListener('mouseenter', () => card.style.transform = 'translateY(-8px)');
    card.addEventListener('mouseleave', () => card.style.transform = 'translateY(0)');
  });

  // Tooltips Bootstrap
  if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
      new bootstrap.Tooltip(el);
    });
  }

  // Botones de copiar
  if (typeof ClipboardJS !== 'undefined') {
    const clipboard = new ClipboardJS('.copy-btn');
    clipboard.on('success', function (e) {
      const originalText = e.trigger.innerHTML;
      e.trigger.innerHTML = '<i class="fas fa-check"></i> Copiado';
      setTimeout(() => e.trigger.innerHTML = originalText, 2000);
      e.clearSelection();
    });
  }

  // Validación de formularios
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });

  // Guardar banners cerrados
  document.querySelectorAll('.alert-dismissible').forEach(banner => {
    const closeBtn = banner.querySelector('.btn-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        const id = banner.getAttribute('data-banner-id');
        if (id) {
          localStorage.setItem(`banner_${id}_closed`, 'true');
        }
      });
    }
  });

  // Consentimiento cookies
  const consentBanner = document.getElementById('consent-banner');
  if (consentBanner && checkCookie('toolhub_logging_consent')) {
    consentBanner.style.display = 'none';
  }

  // Botones de descarga
  document.querySelectorAll('.download-results').forEach(button => {
    button.addEventListener('click', function () {
      const id = this.getAttribute('data-content');
      const el = document.getElementById(id);
      const filename = this.getAttribute('data-filename') || 'toolhub-results.txt';
      if (el) downloadText(el.innerText, filename);
    });
  });

  // Modo oscuro con compatibilidad Bootstrap
  const toggle = document.getElementById('darkModeToggle') || document.getElementById('dark-mode-toggle');
  const html = document.documentElement;
  const prefersDark = localStorage.getItem('dark-mode') === 'true' || 
                      (window.matchMedia('(prefers-color-scheme: dark)').matches && localStorage.getItem('dark-mode') === null);

  if (prefersDark) {
    html.setAttribute('data-bs-theme', 'dark');
    document.body.classList.add('dark-mode');
    if (toggle) toggle.checked = true;
  }

  if (toggle) {
    toggle.addEventListener('change', function () {
      const isDark = this.checked;
      html.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
      document.body.classList.toggle('dark-mode', isDark);
      localStorage.setItem('dark-mode', isDark.toString());
    });
  }

  // Inicializar charts
  initializeCharts();

  // Log versión
  console.log("ToolHub Web v" + (window.TOOLHUB_VERSION || "0.2") + " cargado correctamente");
});

function downloadText(text, filename) {
  const a = document.createElement('a');
  a.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent(text);
  a.download = filename;
  a.style.display = 'none';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
}

function checkCookie(name) {
  return document.cookie.split(';').some(c => c.trim().startsWith(name + '='));
}

function initializeCharts() {
  if (typeof Chart === 'undefined') return;
  document.querySelectorAll('[data-chart-type]').forEach(canvas => {
    const type = canvas.getAttribute('data-chart-type');
    const data = JSON.parse(canvas.getAttribute('data-chart-data') || '{}');
    const options = JSON.parse(canvas.getAttribute('data-chart-options') || '{}');
    new Chart(canvas, { type, data, options });
  });
}
