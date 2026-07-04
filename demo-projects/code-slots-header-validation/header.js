(function () {
  var header = document.querySelector("[data-aics-header]");
  if (!header) {
    return;
  }

  var toggle = header.querySelector(".aics-menu-toggle");
  var nav = header.querySelector(".aics-nav");
  if (!toggle || !nav) {
    return;
  }

  function closeMenu() {
    toggle.setAttribute("aria-expanded", "false");
    nav.classList.remove("is-open");
  }

  toggle.addEventListener("click", function () {
    var open = toggle.getAttribute("aria-expanded") === "true";
    toggle.setAttribute("aria-expanded", open ? "false" : "true");
    nav.classList.toggle("is-open", !open);
  });

  nav.addEventListener("click", function (event) {
    if (event.target.closest("a")) {
      closeMenu();
    }
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      closeMenu();
      toggle.focus();
    }
  });

  document.addEventListener("click", function (event) {
    if (!header.contains(event.target)) {
      closeMenu();
    }
  });

  window.addEventListener("resize", function () {
    if (window.innerWidth > 1100) {
      closeMenu();
    }
  });
})();
