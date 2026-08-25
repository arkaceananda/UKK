(function () {
    'use strict';

    function getCssVar(name, fallback) {
        var val = getComputedStyle(document.documentElement).getPropertyValue(name);
        return val.trim() || fallback;
    }

    function getTheme() {
        var dark = document.documentElement.classList.contains('dark');
        return {
            text: getCssVar('--chart-text', dark ? '#B0B0B0' : '#6B7280'),
            grid: getCssVar('--chart-grid', dark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)'),
            tooltip: dark ? 'dark' : 'light',
            dark: dark
        };
    }

    function renderSalesChart(filter) {
        var el = document.getElementById('sales-chart');
        if (!el) return;

        var url = (window.routes && window.routes.apiChartSales || '/api/chart/sales') + '?filter=' + (filter || '7d');

        fetch(url)
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                var c = getTheme();
                var options = {
                    chart: { type: 'area', height: 300, toolbar: { show: false }, background: 'transparent', foreColor: c.text },
                    series: [{ name: 'Pendapatan', data: data.totals || [] }],
                    xaxis: { categories: data.labels || [], labels: { style: { colors: c.text, fontSize: '11px' } } },
                    yaxis: { labels: { style: { colors: c.text, fontSize: '11px' }, formatter: function (v) { return 'Rp ' + (v || 0).toLocaleString('id-ID'); } } },
                    stroke: { curve: 'smooth', width: 3 },
                    colors: [getCssVar('--chart-daun', '#4E9A51')],
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05 } },
                    grid: { borderColor: c.grid, strokeDashArray: 4 },
                    tooltip: { theme: c.tooltip, y: { formatter: function (v) { return 'Rp ' + (v || 0).toLocaleString('id-ID'); } } },
                    theme: { mode: c.dark ? 'dark' : 'light' },
                    animations: { enabled: true, easing: 'easeinout', speed: 800 },
                    noData: { text: 'Tidak ada data', align: 'center', verticalAlign: 'middle' }
                };

                if (el._apexChart) el._apexChart.destroy();
                el._apexChart = new ApexCharts(el, options);
                el._apexChart.render();
            })
            .catch(function (err) {
                console.error('Sales chart error:', err);
            });
    }

    function renderTopMenuChart(filter) {
        var el = document.getElementById('top-menu-chart');
        if (!el) return;

        var url = (window.routes && window.routes.apiChartTopMenu || '/api/chart/top-menu') + '?filter=' + (filter || '7d');

        fetch(url)
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                var c = getTheme();
                var options = {
                    chart: { type: 'bar', height: 300, toolbar: { show: false }, background: 'transparent', foreColor: c.text },
                    plotOptions: { bar: { horizontal: false, columnWidth: '45%', borderRadius: 6, distributed: true, dataLabels: { position: 'top' } } },
                    dataLabels: { enabled: true, offsetY: -20, style: { fontSize: '11px', colors: [c.text] } },
                    series: [{ name: 'Jumlah Terjual', data: data.totals || [] }],
                    xaxis: { categories: data.labels || [], labels: { style: { colors: c.text, fontSize: '11px' }, rotate: -25, rotateAlways: (data.labels || []).length > 5 } },
                    yaxis: { labels: { style: { colors: c.text, fontSize: '11px' } } },
                    colors: [
                        getCssVar('--chart-cabai', '#D64545'),
                        getCssVar('--chart-daun', '#4E9A51'),
                        getCssVar('--chart-gas', '#4FA8C9'),
                        getCssVar('--chart-yellow', '#EAB308'),
                        getCssVar('--chart-purple', '#9B59B6')
                    ],
                    grid: { borderColor: c.grid, strokeDashArray: 4 },
                    legend: { show: false },
                    tooltip: { theme: c.tooltip, y: { formatter: function (v) { return (v || 0).toLocaleString('id-ID') + ' porsi'; } } },
                    theme: { mode: c.dark ? 'dark' : 'light' },
                    animations: { enabled: true, easing: 'easeinout', speed: 800 },
                    noData: { text: 'Tidak ada data', align: 'center', verticalAlign: 'middle' }
                };

                if (el._apexChart) el._apexChart.destroy();
                el._apexChart = new ApexCharts(el, options);
                el._apexChart.render();
            })
            .catch(function (err) {
                console.error('Top menu chart error:', err);
            });
    }

    // Reusable filter component
    function chartFilter(chartType, initialFilter) {
        return {
            filter: initialFilter || '7d',
            options: [
                { value: '1d', label: '1H' },
                { value: '7d', label: '7H' },
                { value: '30d', label: '30H' }
            ],
            setFilter: function (f) {
                this.filter = f;
                if (chartType === 'sales') {
                    renderSalesChart(f);
                } else if (chartType === 'top-menu') {
                    renderTopMenuChart(f);
                }
            }
        };
    }

    window.chartFilter = chartFilter;
    window.renderSalesChart = renderSalesChart;
    window.renderTopMenuChart = renderTopMenuChart;
})();
