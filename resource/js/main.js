"use strict";

(function () {
    var state = {
        header: null,
        backToTop: null,
        body: document.body,
        mobileRail: null,
        topbar: null,
        overlayMode: "auto",
        railAutohide: false,
        topbarScrollAway: false,
        lastScrollY: 0,
        isCustomizerPreview: false
    };

    function bindOnce(element, key, eventName, handler, options) {
        if (!element || element.dataset[key] === "1") {
            return;
        }

        element.addEventListener(eventName, handler, options || false);
        element.dataset[key] = "1";
    }

    function detectFullscreenHero() {
        var selectors = [
            ".sbs-video-hero",
            ".sbin-hero-fullscreen",
            ".sbs-magazine-hero",
            "[widget='4']",
            "[widget='7']",
            "[widget='13']",
            "[data-widget='13']",
            "[data-widget-id='13']",
            ".widget_13",
            ".smart-widget-13",
            ".sbs-widget-13",
            ".hero-fullscreen",
            ".hero-header"
        ];
        var hero = document.querySelector(selectors.join(","));
        if (!hero) {
            return null;
        }

        var box = hero.getBoundingClientRect();
        return box.height >= (window.innerHeight * 0.72) ? hero : null;
    }

    function refreshState() {
        state.header = document.querySelector(".aihl-header-nav");
        state.backToTop = document.querySelector(".back-to-top");
        state.body = document.body;
        state.mobileRail = document.querySelector(".aihl-mobile-rail");
        state.topbar = document.querySelector(".aihl-topbar");
        state.overlayMode = state.header ? String(state.header.getAttribute("data-overlay-mode") || "auto") : "auto";
        state.railAutohide = state.header ? String(state.header.getAttribute("data-rail-autohide") || "0") === "1" : false;
        state.topbarScrollAway = state.topbar && state.topbar.getAttribute("data-topbar-scroll") === "scroll-away";
        state.isCustomizerPreview = !!(window.wp && window.wp.customize);
    }

    function updateFullscreenHeroClass() {
        if (!state.body) {
            return;
        }

        if (state.overlayMode !== "never" && (state.overlayMode === "always" || detectFullscreenHero())) {
            state.body.classList.add("aihl-has-fullscreen-hero");
            return;
        }

        state.body.classList.remove("aihl-has-fullscreen-hero");
    }

    function onScroll() {
        var body = state.body || document.body;
        var scrolled = window.scrollY > 80;
        body.classList.toggle("aihl-page-is-scrolled", scrolled);

        if (state.header) {
            var isOverlayHeader = state.header.classList.contains("aihl-header-overlay") || body.classList.contains("aihl-has-fullscreen-hero");
            state.header.classList.toggle("is-scrolled", scrolled);
            state.header.classList.toggle("shadow-sm", scrolled && !isOverlayHeader);
        }

        if (state.backToTop) {
            state.backToTop.classList.toggle("is-visible", window.scrollY > 300);
        }

        if (state.mobileRail && state.railAutohide && !state.isCustomizerPreview) {
            state.mobileRail.classList.toggle("aihl-mobile-rail-hidden", window.scrollY < 40);
        }

        if (state.topbar && state.topbarScrollAway) {
            var currentY = window.scrollY;
            var topbarHidden = false;
            if (currentY > 60 && currentY > state.lastScrollY) {
                state.topbar.classList.add("aihl-topbar-hidden");
                topbarHidden = true;
            } else {
                state.topbar.classList.remove("aihl-topbar-hidden");
            }
            body.classList.toggle("aihl-topbar-is-hidden", topbarHidden);
            state.lastScrollY = currentY;
        }
    }

    function initBackToTop() {
        bindOnce(state.backToTop, "aihlBackToTopBound", "click", function (event) {
            event.preventDefault();
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }

    function initSearchToggle() {
        var dropdown = document.querySelector(".aihl-search-dropdown");
        var fullscreen = document.querySelector(".aihl-search-fullscreen");

        function openSearch(style) {
            var target = style === "icon-fullscreen" ? fullscreen : dropdown;
            if (!target) {
                return;
            }

            target.classList.add("is-open");
            target.setAttribute("aria-hidden", "false");
            var input = target.querySelector("input[type='search']");
            if (input) {
                input.focus();
            }
        }

        function closeSearch() {
            [dropdown, fullscreen].forEach(function (target) {
                if (!target) {
                    return;
                }
                target.classList.remove("is-open");
                target.setAttribute("aria-hidden", "true");
            });
        }

        document.querySelectorAll(".aihl-search-toggle").forEach(function (btn) {
            bindOnce(btn, "aihlSearchToggleBound", "click", function () {
                var style = btn.getAttribute("data-search-style") || "icon-dropdown";
                var target = style === "icon-fullscreen" ? fullscreen : dropdown;
                if (target && target.classList.contains("is-open")) {
                    closeSearch();
                    return;
                }
                closeSearch();
                openSearch(style);
            });
        });

        document.querySelectorAll(".aihl-search-close").forEach(function (btn) {
            bindOnce(btn, "aihlSearchCloseBound", "click", closeSearch);
        });

        var bottomBarSearchBtn = document.querySelector(".aihl-bottom-bar-search-btn");
        bindOnce(bottomBarSearchBtn, "aihlBottomSearchBound", "click", function () {
            openSearch(dropdown ? "icon-dropdown" : "icon-fullscreen");
        });
    }

    function initDesktopMenuHover() {
        if (!state.header || window.matchMedia("(max-width: 991.98px)").matches) {
            return;
        }

        state.header.querySelectorAll(".navbar-nav > .nav-item.dropdown").forEach(function (item) {
            if (item.dataset.aihlDesktopHoverBound === "1") {
                return;
            }

            var closeTimer = null;
            var open = function () {
                if (closeTimer) {
                    window.clearTimeout(closeTimer);
                    closeTimer = null;
                }
                item.classList.add("aihl-hover-open");
            };
            var close = function () {
                if (closeTimer) {
                    window.clearTimeout(closeTimer);
                }
                closeTimer = window.setTimeout(function () {
                    item.classList.remove("aihl-hover-open");
                }, 340);
            };

            item.addEventListener("mouseenter", open);
            item.addEventListener("mouseleave", close);

            var panel = item.querySelector(".dropdown-menu");
            if (panel) {
                panel.addEventListener("mouseenter", open);
                panel.addEventListener("mouseleave", close);
            }

            item.dataset.aihlDesktopHoverBound = "1";
        });
    }

    function initMobileMenuAccordion() {
        var mobileMenu = document.querySelector("#offcanvasNavbar .aihl-mobile-menu");
        if (!mobileMenu || mobileMenu.dataset.aihlAccordionBound === "1") {
            return;
        }

        function getDirectSubmenu(item) {
            if (!item) {
                return null;
            }
            for (var index = 0; index < item.children.length; index++) {
                var child = item.children[index];
                if (child.classList && child.classList.contains("aihl-mobile-submenu")) {
                    return child;
                }
            }
            return null;
        }

        function getDirectToggle(item) {
            var row = item ? item.firstElementChild : null;
            return row ? row.querySelector(".aihl-mobile-submenu-toggle") : null;
        }

        function setItemOpen(item, open) {
            var submenu = getDirectSubmenu(item);
            var toggle = getDirectToggle(item);
            if (!submenu || !toggle) {
                return;
            }

            item.classList.toggle("is-open", open);
            submenu.hidden = !open;
            toggle.setAttribute("aria-expanded", open ? "true" : "false");
        }

        function closeSiblingItems(item) {
            var siblings = item && item.parentElement ? item.parentElement.children : [];
            Array.prototype.forEach.call(siblings, function (sibling) {
                if (sibling !== item && sibling.classList && sibling.classList.contains("has-children")) {
                    setItemOpen(sibling, false);
                }
            });
        }

        mobileMenu.addEventListener("click", function (event) {
            var toggle = event.target.closest(".aihl-mobile-submenu-toggle");
            if (!toggle || !mobileMenu.contains(toggle)) {
                return;
            }

            var item = toggle.closest(".aihl-mobile-menu-item");
            if (!item) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            var willOpen = toggle.getAttribute("aria-expanded") !== "true";
            closeSiblingItems(item);
            setItemOpen(item, willOpen);
        });

        var offcanvas = mobileMenu.closest(".offcanvas");
        if (offcanvas) {
            offcanvas.addEventListener("hidden.bs.offcanvas", function () {
                mobileMenu.querySelectorAll(".aihl-mobile-menu-item.is-open").forEach(function (item) {
                    setItemOpen(item, false);
                });
            });
        }

        mobileMenu.dataset.aihlAccordionBound = "1";
    }

    function initOptionalPlugins() {
        if (typeof WOW !== "undefined") {
            new WOW().init();
        }

        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.owlCarousel) {
            window.jQuery(".testimonial-carousel:not(.owl-loaded)").owlCarousel({
                items: 1,
                autoplay: true,
                smartSpeed: 1000,
                dots: true,
                loop: true,
                nav: true,
                navText: [
                    '<i class="fa-solid fa-chevron-left"></i>',
                    '<i class="fa-solid fa-chevron-right"></i>'
                ]
            });
        }
    }

    function refreshLayoutState() {
        updateFullscreenHeroClass();

        if (state.mobileRail && state.railAutohide && !state.isCustomizerPreview) {
            state.mobileRail.classList.add("aihl-mobile-rail-hidden");
        } else if (state.mobileRail) {
            state.mobileRail.classList.remove("aihl-mobile-rail-hidden");
        }

        onScroll();
    }

    function initTheme() {
        refreshState();
        initBackToTop();
        initSearchToggle();
        initDesktopMenuHover();
        initMobileMenuAccordion();
        initOptionalPlugins();
        refreshLayoutState();
    }

    if (!window.aihlThemeEventsBound) {
        window.addEventListener("scroll", onScroll, { passive: true });
        window.addEventListener("resize", function () {
            refreshState();
            refreshLayoutState();
        }, { passive: true });
        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                document.querySelectorAll(".aihl-search-dropdown.is-open, .aihl-search-fullscreen.is-open").forEach(function (target) {
                    target.classList.remove("is-open");
                    target.setAttribute("aria-hidden", "true");
                });
            }
        });
        window.aihlThemeEventsBound = true;
    }

    window.aihlInitTheme = initTheme;

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initTheme);
    } else {
        initTheme();
    }
})();
