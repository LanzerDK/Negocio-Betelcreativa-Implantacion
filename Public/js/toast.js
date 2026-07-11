(function () {
  // Verificar si el contenedor de toasts ya existe en el DOM
  var container = document.querySelector('.bc-toast-container');
  if (!container) {
    // Crear el contenedor global para todos los toasts
    container = document.createElement('div');
    container.className = 'bc-toast-container';
    document.body.appendChild(container);
  }

  // Función global para mostrar notificaciones temporales
  window.toast = function (message, type) {
    // Normalizar el tipo de notificación, por defecto 'info'
    type = type || 'info';
    // Mapa de tipos con clase de fondo e icono correspondiente
    var map = {
      success: { bg: 'toast-sys-success', icon: 'fa-check-circle' },
      error:   { bg: 'toast-sys-error',   icon: 'fa-times-circle' },
      warning: { bg: 'toast-sys-warning',  icon: 'fa-exclamation-triangle' },
      info:    { bg: 'toast-sys-info',     icon: 'fa-info-circle' }
    };
    var cfg = map[type] || map.info;
    // Crear el elemento del toast y asignar clases CSS
    var el = document.createElement('div');
    el.className = 'toast show ' + cfg.bg;
    // Contenido interno: icono y mensaje escapado
    el.innerHTML =
      '<div class="toast-body-custom">' +
        '<i class="fas ' + cfg.icon + '"></i><span>' + escapeHtml(message) + '</span>' +
      '</div>';
    container.appendChild(el);
    // Auto-remover el toast tras 4.2 segundos
    setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 4200);
  };

  // Función genérica para llamadas AJAX con manejo de errores
  window.callApi = async function (url, options) {
    options = options || {};
    var res = await fetch(url, options);
    var text = await res.text();
    var data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      // Si la respuesta no es JSON, lanzar error descriptivo
      if (!res.ok) {
        throw new Error('Error del servidor (HTTP ' + res.status + ')');
      }
      throw new Error('Respuesta no válida del servidor.');
    }
    if (!res.ok) {
      // Si el código HTTP no es 2xx, regresar objeto con error
      data = data || {};
      data.success = false;
      data.message = data.message || 'HTTP ' + res.status;
      return data;
    }
    return data;
  };

  // Escapa caracteres HTML para evitar inyección de código
  window.escapeHtml = function (text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  };
})();
