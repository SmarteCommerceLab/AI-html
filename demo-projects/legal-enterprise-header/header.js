(function () {
  var header = document.querySelector("[data-aihlegal-header]");
  if (!header) {
    return;
  }

  var menuToggle = header.querySelector(".aihlegal-menu-toggle");
  var navigation = header.querySelector(".aihlegal-navigation");
  var dropdowns = Array.prototype.slice.call(header.querySelectorAll("[data-aihlegal-dropdown]"));

  function closeDropdowns(except) {
    dropdowns.forEach(function (dropdown) {
      if (dropdown === except) {
        return;
      }
      dropdown.classList.remove("is-open");
      var trigger = dropdown.querySelector(".aihlegal-nav-trigger");
      if (trigger) {
        trigger.setAttribute("aria-expanded", "false");
      }
    });
  }

  function closeNavigation() {
    if (menuToggle && navigation) {
      menuToggle.setAttribute("aria-expanded", "false");
      navigation.classList.remove("is-open");
    }
    closeDropdowns();
  }

  if (menuToggle && navigation) {
    menuToggle.addEventListener("click", function () {
      var willOpen = menuToggle.getAttribute("aria-expanded") !== "true";
      menuToggle.setAttribute("aria-expanded", String(willOpen));
      navigation.classList.toggle("is-open", willOpen);
      if (!willOpen) {
        closeDropdowns();
      }
    });
  }

  dropdowns.forEach(function (dropdown) {
    var trigger = dropdown.querySelector(".aihlegal-nav-trigger");
    if (!trigger) {
      return;
    }

    trigger.addEventListener("click", function () {
      var willOpen = trigger.getAttribute("aria-expanded") !== "true";
      closeDropdowns(dropdown);
      trigger.setAttribute("aria-expanded", String(willOpen));
      dropdown.classList.toggle("is-open", willOpen);
    });
  });

  header.addEventListener("click", function (event) {
    if (event.target.closest(".aihlegal-navigation > a, .aihlegal-megamenu a")) {
      closeNavigation();
    }
  });

  document.addEventListener("click", function (event) {
    if (!header.contains(event.target)) {
      closeNavigation();
    }
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      closeNavigation();
      if (menuToggle && window.innerWidth <= 1240) {
        menuToggle.focus();
      }
    }
  });

  window.addEventListener("resize", function () {
    if (window.innerWidth > 1240) {
      closeNavigation();
    }
  });

  function updateScrolledState() {
    header.setAttribute("data-scrolled", window.scrollY > 8 ? "true" : "false");
  }

  updateScrolledState();
  window.addEventListener("scroll", updateScrolledState, { passive: true });

  var currentPath = window.location.pathname.replace(/\/+$/, "") || "/";
  header.querySelectorAll("a[href^='/']").forEach(function (link) {
    var linkPath = new URL(link.href, window.location.origin).pathname.replace(/\/+$/, "") || "/";
    if (linkPath === currentPath && !link.classList.contains("aihlegal-brand")) {
      link.setAttribute("aria-current", "page");
    }
  });
})();
