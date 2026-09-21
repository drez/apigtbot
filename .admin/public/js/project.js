/*
 * project.js — this project's own client-side code.
 *
 * The JS counterpart of public/css/project.scss: created from the template
 * once, then YOURS. `gc build --sync-template` and `gc upgrade` never overwrite
 * it, and drift against the template is never reported. Every other file under
 * public/js/ is template-managed and WILL be overwritten.
 *
 * It is the last entry in the asset bundle, so everything else (gcList,
 * gcScreens, gcRealtime, the form widgets) already exists when this runs.
 */
(function () {
    'use strict';

    /*
     * gcTradeChart — the dashboard's trading chart.
     *
     * Server contract: DashboardRenderer emits one
     *   <div class="dash-chart" data-run="N" data-base="…/" data-symbol="BTCUSDT">
     * and GET {base}Dashboard/chart?run=N&tf=1h answers with candles (epoch
     * seconds, UTC), the run's fills, working orders, grid geometry, the trend
     * arm's entry/stop/hwm and lifecycle events (see DashboardData::chartModel).
     * Drawn with lightweight-charts (window.LightweightCharts, vendored in
     * public/js/vendor/). Polls every 60 s while the tab is visible; the
     * timeframe choice persists in localStorage.
     *
     * Times: lightweight-charts labels the axis in UTC; every timestamp is
     * shifted by the browser's offset before it is handed to the chart so the
     * axis reads in local time (the shift is display-only).
     */
    var TFS = ['1m', '5m', '15m', '1h', '4h'];
    var TF_SECONDS = { '1m': 60, '5m': 300, '15m': 900, '1h': 3600, '4h': 14400 };
    var STORE_KEY = 'gtbot.chart.tf';
    var POLL_MS = 60000;
    var COLORS = {
        up: '#00916e', down: '#c0392b', upVol: 'rgba(0,145,110,.35)', downVol: 'rgba(192,57,43,.35)',
        gridBuy: 'rgba(0,145,110,.7)', gridSell: 'rgba(192,57,43,.7)', band: '#0a2540',
        openBuy: '#00916e', openSell: '#c0392b',
        entry: '#1a56db', stop: '#c0392b', hwm: '#b26a00', event: '#6b7280', text: '#425466', lines: '#eef1f5',
        ema20: '#f59e0b', ema50: '#6366f1'
    };
    var EMA_PERIODS = { ema20: 20, ema50: 50 };

    // exponential moving average over closes; null until the window is full
    function ema(candles, period) {
        var k = 2 / (period + 1), out = [], v = null, sum = 0;
        for (var i = 0; i < candles.length; i++) {
            var c = candles[i].close;
            if (i < period - 1) { sum += c; continue; }
            if (i === period - 1) { sum += c; v = sum / period; }
            else { v = c * k + v * (1 - k); }
            out.push({ time: tzShift(candles[i].time), value: v });
        }
        return out;
    }

    function tzShift(t) { return t - new Date().getTimezoneOffset() * 60; }
    function floorTo(t, step) { return Math.floor(t / step) * step; }
    function fmtPrice(p) { return p >= 100 ? p.toLocaleString(undefined, { maximumFractionDigits: 2 }) : String(p); }
    function ago(sec) {
        if (sec < 90) { return sec + 's'; }
        if (sec < 5400) { return Math.round(sec / 60) + 'm'; }
        if (sec < 172800) { return Math.round(sec / 3600) + 'h'; }
        return Math.round(sec / 86400) + 'd';
    }
    function el(tag, cls, text) {
        var e = document.createElement(tag);
        if (cls) { e.className = cls; }
        if (text !== undefined) { e.textContent = text; }
        return e;
    }

    function TradeChart(mount) {
        this.mount = mount;
        this.run = parseInt(mount.getAttribute('data-run'), 10);
        this.base = mount.getAttribute('data-base') || '';
        this.symbol = mount.getAttribute('data-symbol') || '';
        this.tf = this.savedTf();
        this.priceLines = [];
        this.userScrolled = false;
        this.timer = null;
        this.build();
        this.load();
        this.arm();
    }

    TradeChart.prototype.savedTf = function () {
        try {
            var v = localStorage.getItem(STORE_KEY);
            if (v && TFS.indexOf(v) !== -1) { return v; }
        } catch (e) { /* storage blocked */ }
        return '1h';
    };

    TradeChart.prototype.build = function () {
        var self = this;
        var LWC = window.LightweightCharts;
        this.mount.textContent = '';

        var bar = el('div', 'dash-chart-bar');
        this.tfButtons = {};
        TFS.forEach(function (tf) {
            var b = el('button', 'dash-chart-tf' + (tf === self.tf ? ' is-active' : ''), tf);
            b.type = 'button';
            b.addEventListener('click', function () { self.setTf(tf); });
            self.tfButtons[tf] = b;
            bar.appendChild(b);
        });
        this.status = el('span', 'dash-chart-status', 'loading…');
        bar.appendChild(this.status);
        this.mount.appendChild(bar);

        this.canvas = el('div', 'dash-chart-canvas');
        this.mount.appendChild(this.canvas);

        var legend = el('div', 'dash-chart-legend');
        [['d', COLORS.up, 'buy fill'], ['d', COLORS.down, 'sell fill'], ['l', COLORS.ema20, 'EMA20'], ['l', COLORS.ema50, 'EMA50'],
         ['l', COLORS.gridBuy, 'grid buy level'], ['l', COLORS.gridSell, 'grid sell level'], ['l', COLORS.band, 'range'],
         ['l', COLORS.openBuy, 'open buy'], ['l', COLORS.openSell, 'open sell'], ['l', COLORS.entry, 'trend entry'], ['l', COLORS.stop, 'trend stop'],
         ['l', COLORS.hwm, 'trend high-water'], ['d', COLORS.event, 'event']].forEach(function (it) {
            var s = el('span');
            var i = el('i', it[0]);
            if (it[0] === 'l') { i.style.borderTopColor = it[1]; } else { i.style.background = it[1]; }
            s.appendChild(i);
            s.appendChild(document.createTextNode(it[2]));
            legend.appendChild(s);
        });
        this.mount.appendChild(legend);

        this.chart = LWC.createChart(this.canvas, {
            autoSize: true,
            layout: { background: { color: '#ffffff' }, textColor: COLORS.text, fontSize: 11 },
            grid: { vertLines: { color: COLORS.lines }, horzLines: { color: COLORS.lines } },
            rightPriceScale: { borderColor: '#d5dbe3', scaleMargins: { top: 0.08, bottom: 0.22 } },
            timeScale: { borderColor: '#d5dbe3', timeVisible: true, secondsVisible: false, rightOffset: 4 },
            crosshair: { mode: LWC.CrosshairMode.Normal }
        });
        this.candles = this.chart.addSeries(LWC.CandlestickSeries, {
            upColor: COLORS.up, downColor: COLORS.down, borderVisible: false,
            wickUpColor: COLORS.up, wickDownColor: COLORS.down,
            priceFormat: { type: 'price', precision: 2, minMove: 0.01 }
        });
        this.volume = this.chart.addSeries(LWC.HistogramSeries, {
            priceFormat: { type: 'volume' }, priceScaleId: 'vol', lastValueVisible: false, priceLineVisible: false
        });
        this.chart.priceScale('vol').applyOptions({ scaleMargins: { top: 0.82, bottom: 0 } });
        this.emas = {};
        Object.keys(EMA_PERIODS).forEach(function (key) {
            self.emas[key] = self.chart.addSeries(LWC.LineSeries, {
                color: COLORS[key], lineWidth: 1, priceLineVisible: false, lastValueVisible: false, crosshairMarkerVisible: false
            });
        });
        this.markers = LWC.createSeriesMarkers(this.candles, []);

        // once the user pans/zooms, stop snapping to the newest bar on refresh
        this.chart.timeScale().subscribeVisibleLogicalRangeChange(function () {
            if (self.settingRange) { return; }
            self.userScrolled = true;
        });
    };

    TradeChart.prototype.setTf = function (tf) {
        if (tf === this.tf) { return; }
        this.tf = tf;
        try { localStorage.setItem(STORE_KEY, tf); } catch (e) { /* storage blocked */ }
        for (var k in this.tfButtons) {
            if (Object.prototype.hasOwnProperty.call(this.tfButtons, k)) {
                this.tfButtons[k].classList.toggle('is-active', k === tf);
            }
        }
        this.userScrolled = false;
        this.load();
    };

    TradeChart.prototype.arm = function () {
        var self = this;
        this.timer = setInterval(function () {
            if (document.visibilityState === 'visible') { self.load(); }
        }, POLL_MS);
        window.addEventListener('beforeunload', function () { clearInterval(self.timer); });
    };

    TradeChart.prototype.load = function () {
        var self = this;
        var tf = this.tf;
        // Plain XHR on purpose (same reason as the dashboard's buttons): the
        // admin's global fetch patch has interception layers that can hide a
        // failure; the chart must show its own error text instead.
        var xhr = new XMLHttpRequest();
        xhr.open('GET', this.base + 'Dashboard/chart?run=' + encodeURIComponent(this.run) + '&tf=' + encodeURIComponent(tf) + '&_=' + Date.now(), true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.timeout = 15000;
        xhr.onload = function () {
            if (tf !== self.tf) { return; } // a later click won
            var body = null;
            try { body = JSON.parse(xhr.responseText); } catch (e) { body = null; }
            if (xhr.status >= 200 && xhr.status < 300 && body && body.status === 'ok') {
                self.render(body);
                return;
            }
            self.status.textContent = 'chart: HTTP ' + xhr.status + (body && body.status ? ' ' + body.status : '');
        };
        xhr.onerror = function () { self.status.textContent = 'chart: network error'; };
        xhr.ontimeout = function () { self.status.textContent = 'chart: no response after 15s'; };
        xhr.send();
    };

    TradeChart.prototype.render = function (d) {
        var self = this;
        var step = TF_SECONDS[d.tf] || d.tf_seconds || 60;
        var first = d.candles.length ? d.candles[0].time : 0;
        var last = d.candles.length ? d.candles[d.candles.length - 1].time : 0;

        this.candles.setData(d.candles.map(function (c) {
            return { time: tzShift(c.time), open: c.open, high: c.high, low: c.low, close: c.close };
        }));
        this.volume.setData(d.candles.map(function (c) {
            return { time: tzShift(c.time), value: c.volume, color: c.close >= c.open ? COLORS.upVol : COLORS.downVol };
        }));
        Object.keys(EMA_PERIODS).forEach(function (key) {
            self.emas[key].setData(ema(d.candles, EMA_PERIODS[key]));
        });

        // markers: fills snap to the bar they fell in and are anchored at
        // their fill price (atPrice*), not at the bar's low/high — several
        // fills in one bar would otherwise stack on the same pixel row.
        // Events have no price and hang above the bar. Anything before the
        // first stored bar cannot be placed and is left out.
        var marks = [];
        (d.fills || []).forEach(function (f) {
            var t = floorTo(f.time, step);
            if (!first || t < first) { return; }
            var buy = f.side === 'Buy';
            marks.push({
                time: tzShift(t), price: f.price,
                position: buy ? 'atPriceBottom' : 'atPriceTop', shape: buy ? 'arrowUp' : 'arrowDown',
                color: buy ? COLORS.up : COLORS.down, size: 1,
                text: (buy ? 'B' : 'S') + ' L' + f.level + ' @' + fmtPrice(f.price)
            });
        });
        (d.events || []).forEach(function (e) {
            var t = floorTo(e.time, step);
            if (!first || t < first) { return; }
            marks.push({ time: tzShift(t), position: 'aboveBar', shape: 'circle', color: COLORS.event, size: 0, text: e.label });
        });
        marks.sort(function (a, b) { return a.time - b.time; });
        this.markers.setMarkers(marks);

        // price lines: grid ladder, range, working orders, trend lines
        this.priceLines.forEach(function (l) { self.candles.removePriceLine(l); });
        this.priceLines = [];
        var LS = window.LightweightCharts.LineStyle;
        var line = function (price, color, style, width, title, label) {
            if (price === null || price === undefined || !(price > 0)) { return; }
            self.priceLines.push(self.candles.createPriceLine({
                price: price, color: color, lineWidth: width || 1, lineStyle: style,
                axisLabelVisible: !!label, title: title || ''
            }));
        };
        // grid ladder: levels below the last price are where buys sit,
        // above it where sells sit — coloured by side so the ladder reads
        // at a glance; the range bounds are solid and labelled on the axis
        var g = d.grid || {};
        var ref = d.last_price || (d.candles.length ? d.candles[d.candles.length - 1].close : 0);
        (g.levels || []).forEach(function (p) {
            line(p, p <= ref ? COLORS.gridBuy : COLORS.gridSell, LS.Dashed, 1, '', false);
        });
        line(g.p_low, COLORS.band, LS.Solid, 2, 'range low', true);
        line(g.p_high, COLORS.band, LS.Solid, 2, 'range high', true);
        (d.orders_open || []).forEach(function (o) {
            line(o.price, o.side === 'Buy' ? COLORS.openBuy : COLORS.openSell, LS.Solid, 1, '', false);
        });
        if (d.trend) {
            line(d.trend.entry, COLORS.entry, LS.Solid, 2, 'entry', true);
            line(d.trend.stop, COLORS.stop, LS.Dashed, 2, 'stop', true);
            line(d.trend.hwm, COLORS.hwm, LS.Dotted, 1, 'hwm', true);
        }

        // viewport: last ~120 bars on first paint / timeframe change; keep the
        // user's own pan/zoom on refreshes
        if (!this.userScrolled && d.candles.length) {
            this.settingRange = true;
            var n = d.candles.length;
            this.chart.timeScale().setVisibleLogicalRange({ from: Math.max(0, n - 120), to: n + 4 });
            this.settingRange = false;
        }

        // status line
        var age = last ? Math.max(0, d.server_time - last) : null;
        this.status.textContent = '';
        this.status.appendChild(document.createTextNode(
            d.symbol + ' · ' + d.tf + ' · ' + d.candles.length + ' bars'
            + (age !== null ? ' · last bar ' + ago(age) + ' ago' : ' · no candles yet')
            + (d.last_price ? ' · last ' + fmtPrice(d.last_price) : '')
        ));
        if (d.stale) {
            var pill = el('span', 'pill is-warn', 'stale');
            pill.title = 'the candle collector has not written this timeframe recently';
            this.status.appendChild(pill);
        }
    };

    function boot() {
        var mounts = document.querySelectorAll('.dash-chart[data-run]');
        if (!mounts.length) { return; }
        if (!window.LightweightCharts) {
            Array.prototype.forEach.call(mounts, function (m) {
                m.textContent = 'chart library missing (public/js/vendor/lightweight-charts)';
            });
            return;
        }
        Array.prototype.forEach.call(mounts, function (m) { new TradeChart(m); });
    }
    window.gcTradeChart = { boot: boot };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
