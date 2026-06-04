/**
 * Hotel Zizu — JS asíncrono global
 */
(function () {
    'use strict';

    /** Inicializa iconos Lucide (solo en elementos con data-lucide, usados en botones) */
    function initLucideButtons() {
        if (typeof lucide === 'undefined') return;
        lucide.createIcons({
            attrs: { class: 'w-4 h-4' },
            nameAttr: 'data-lucide',
        });
    }

    /** Cierra alertas con animación */
    function initDismissAlerts() {
        document.querySelectorAll('[data-dismiss-alert]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var alert = btn.closest('[data-alert]');
                if (!alert) return;
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-4px)';
                setTimeout(function () { alert.remove(); }, 200);
            });
        });
    }

    /** Confirmación para formularios de eliminar */
    function initDeleteForms() {
        document.querySelectorAll('form[data-confirm-delete]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                var msg = form.getAttribute('data-confirm-delete') || '¿Eliminar este registro?';
                if (!window.confirm(msg)) {
                    e.preventDefault();
                }
            });
        });
    }

    /** Resalta enlace activo en navegación */
    function highlightActiveNav() {
        var path = window.location.pathname.replace(/\/$/, '');
        document.querySelectorAll('[data-nav-link]').forEach(function (link) {
            var href = link.getAttribute('href');
            if (!href) return;
            try {
                var linkPath = new URL(href, window.location.origin).pathname.replace(/\/$/, '');
                if (path === linkPath || (linkPath !== '/' && path.startsWith(linkPath))) {
                    link.classList.add('nav-link-active');
                }
            } catch (_) { /* ignore */ }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initLucideButtons();
        initDismissAlerts();
        initDeleteForms();
        highlightActiveNav();
    });

    window.HotelApp = {
        initLucideButtons: initLucideButtons,
        debounce: function (fn, ms) {
            var t;
            return function () {
                var args = arguments;
                var ctx = this;
                clearTimeout(t);
                t = setTimeout(function () { fn.apply(ctx, args); }, ms);
            };
        },
    };
})();
