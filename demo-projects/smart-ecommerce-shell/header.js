(function () {
  function init() {
    document.querySelectorAll('[data-sec-shell-header]:not([data-sec-shell-ready])').forEach(function (header) {
      var toggle = header.querySelector('.sec-shell-header__toggle');
      var nav = header.querySelector('.sec-shell-header__nav');
      if (!toggle || !nav) return;
      header.dataset.secShellReady = 'true';
      function close(restoreFocus) {
        toggle.setAttribute('aria-expanded', 'false');
        nav.classList.remove('is-open');
        if (restoreFocus) toggle.focus();
      }
      toggle.addEventListener('click', function () {
        var open = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
        nav.classList.toggle('is-open', !open);
      });
      header.addEventListener('click', function (event) {
        if (event.target.closest('.sec-shell-header__nav a')) close(false);
      });
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && nav.classList.contains('is-open')) close(true);
      });
      document.addEventListener('click', function (event) {
        if (!header.contains(event.target)) close(false);
      });
    });
  }
  init();
  window.addEventListener('aihl:content-ready', init);
})();
