(function () {
  window.Modal = {
    open: function (id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.classList.add('show');
      if (!document.getElementById('bc-backdrop')) {
        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.id = 'bc-backdrop';
        document.body.appendChild(backdrop);
        backdrop.offsetHeight;
        backdrop.classList.add('show');
      }
      document.body.classList.add('modal-open');
      el.dispatchEvent(new CustomEvent('modal:shown', { bubbles: true }));
    },
    close: function (id) {
      var el = document.getElementById(id);
      if (el) el.classList.remove('show');
      var backdrop = document.getElementById('bc-backdrop');
      if (backdrop) {
        backdrop.classList.remove('show');
        setTimeout(function () {
          var b = document.getElementById('bc-backdrop');
          if (b) b.remove();
        }, 300);
      }
      document.body.classList.remove('modal-open');
    },
    getInstance: function (el) {
      return {
        show: function () { Modal.open(el.id); },
        hide: function () { Modal.close(el.id); }
      };
    }
  };

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      var modals = document.querySelectorAll('.modal.show');
      for (var i = 0; i < modals.length; i++) {
        Modal.close(modals[i].id);
      }
    }
  });

  document.addEventListener('click', function (e) {
    var target = e.target;
    if (target.hasAttribute('data-modal-dismiss')) {
      Modal.close(target.getAttribute('data-modal-dismiss'));
    }
  });
})();
