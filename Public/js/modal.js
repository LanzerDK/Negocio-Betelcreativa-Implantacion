(function () {
  // Objeto global para gestionar la apertura y cierre de modales
  window.Modal = {
    // Abre un modal por su ID y crea el backdrop si no existe
    open: function (id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.classList.add('show');
      if (!document.getElementById('bc-backdrop')) {
        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.id = 'bc-backdrop';
        document.body.appendChild(backdrop);
        backdrop.offsetHeight; // Fuerza el reflow para activar transición CSS
        backdrop.classList.add('show');
      }
      document.body.classList.add('modal-open');
      // Dispara evento personalizado para que otros módulos puedan escucharlo
      el.dispatchEvent(new CustomEvent('modal:shown', { bubbles: true }));
    },
    // Cierra el modal y remueve el backdrop tras la animación
    close: function (id) {
      var el = document.getElementById(id);
      if (el) el.classList.remove('show');
      var backdrop = document.getElementById('bc-backdrop');
      if (backdrop) {
        backdrop.classList.remove('show');
        // Espera a que termine la transición CSS antes de eliminar el backdrop
        setTimeout(function () {
          var b = document.getElementById('bc-backdrop');
          if (b) b.remove();
        }, 300);
      }
      document.body.classList.remove('modal-open');
    },
    // Retorna objeto compatible con la API Bootstrap para modal
    getInstance: function (el) {
      return {
        show: function () { Modal.open(el.id); },
        hide: function () { Modal.close(el.id); }
      };
    }
  };

  // Cerrar el modal activo al presionar Escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      var modals = document.querySelectorAll('.modal.show');
      for (var i = 0; i < modals.length; i++) {
        Modal.close(modals[i].id);
      }
    }
  });

  // Delegación de eventos para atributo data-modal-dismiss
  document.addEventListener('click', function (e) {
    var target = e.target;
    if (target.hasAttribute('data-modal-dismiss')) {
      Modal.close(target.getAttribute('data-modal-dismiss'));
    }
  });
})();
