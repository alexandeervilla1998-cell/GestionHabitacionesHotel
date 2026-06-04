/**
 * Gráficas de métricas — Chart.js (carga diferida)
 */
(function () {
    'use strict';

    function initCharts() {
        var reservasEl = document.getElementById('chartReservasEstado');
        var ingresosEl = document.getElementById('chartIngresosMes');
        if (!reservasEl && !ingresosEl) return;

        var datosReservas = window.__metricasReservas || {};
        var datosIngresos = window.__metricasIngresos || {};

        var palette = ['#1a3c34', '#2d5a4e', '#6b7280', '#9ca3af', '#3d8b7a'];

        if (reservasEl && typeof Chart !== 'undefined') {
            var labelsR = Object.keys(datosReservas);
            var valuesR = Object.values(datosReservas).map(Number);
            new Chart(reservasEl, {
                type: 'doughnut',
                data: {
                    labels: labelsR,
                    datasets: [{
                        data: valuesR,
                        backgroundColor: palette,
                        borderWidth: 2,
                        borderColor: '#fff',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 14, padding: 14, font: { family: 'DM Sans' } },
                        },
                    },
                },
            });
        }

        if (ingresosEl && typeof Chart !== 'undefined') {
            var labelsI = Object.keys(datosIngresos);
            var valuesI = Object.values(datosIngresos).map(Number);
            new Chart(ingresosEl, {
                type: 'bar',
                data: {
                    labels: labelsI,
                    datasets: [{
                        label: 'Ingresos ($)',
                        data: valuesI,
                        backgroundColor: '#2d5a4e',
                        borderColor: '#1a3c34',
                        borderWidth: 2,
                        borderRadius: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#e5e7eb' },
                            ticks: {
                                callback: function (v) { return '$' + Number(v).toLocaleString('es'); },
                            },
                        },
                        x: { grid: { display: false } },
                    },
                    plugins: { legend: { display: false } },
                },
            });
        }
    }

    function loadChartJs() {
        if (typeof Chart !== 'undefined') {
            initCharts();
            return;
        }
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
        s.async = true;
        s.onload = initCharts;
        document.head.appendChild(s);
    }

    document.addEventListener('DOMContentLoaded', loadChartJs);
})();
