(function () {
  var container = document.querySelector('.bc-toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'bc-toast-container';
    document.body.appendChild(container);
  }

  window.toast = function (message, type) {
    type = type || 'info';
    var map = {
      success: { bg: 'toast-sys-success', icon: 'fa-check-circle' },
      error:   { bg: 'toast-sys-error',   icon: 'fa-times-circle' },
      warning: { bg: 'toast-sys-warning',  icon: 'fa-exclamation-triangle' },
      info:    { bg: 'toast-sys-info',     icon: 'fa-info-circle' }
    };
    var cfg = map[type] || map.info;
    var el = document.createElement('div');
    el.className = 'toast show ' + cfg.bg;
    el.innerHTML =
      '<div class="toast-body-custom">' +
        '<i class="fas ' + cfg.icon + '"></i><span>' + escapeHtml(message) + '</span>' +
      '</div>';
    container.appendChild(el);
    setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 4200);
  };

  window.callApi = async function (url, options) {
    options = options || {};
    var res = await fetch(url, options);
    var text = await res.text();
    var data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      if (!res.ok) {
        throw new Error('Error del servidor (HTTP ' + res.status + ')');
      }
      throw new Error('Respuesta no válida del servidor.');
    }
    if (!res.ok) {
      data = data || {};
      data.success = false;
      data.message = data.message || 'HTTP ' + res.status;
      return data;
    }
    return data;
  };

  window.escapeHtml = function (text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  };
})();
