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
    var data = await res.json();
    if (!res.ok) {
      var err = new Error(data.message || 'HTTP ' + res.status);
      err.errors = data.errors || null;
      throw err;
    }
    return data;
  };

  function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
})();
