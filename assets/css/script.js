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
  
  console.log("ToolHub Web cargado correctamente");
});
