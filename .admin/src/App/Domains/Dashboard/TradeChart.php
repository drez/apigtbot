<?php

namespace App\Domains\Dashboard;

/**
 * Dependency-free inline-SVG scatter of recent trade points: price on Y,
 * chronological index on X, buy/sell colored, open vs filled by fill style,
 * with the configured grid range drawn as a shaded band. Pure and
 * self-contained (no external JS/CSS, CSP-safe) — mirrors the vanilla,
 * emit-markup ethos of the rest of the stack.
 */
final class TradeChart
{
    /** marker-kind → css class; colors declared in the <style> block below.
     *  Every kind in DashboardData::MARKER_KINDS must have an entry here
     *  (pinned by MarkerKindCoverageTest, I3a final-fix review) — a marker
     *  whose kind isn't in this map is silently dropped by markers(). */
    private const MARKER_CLASS = [
        'algo_update' => 'tc-mk-algo',
        'bot_start' => 'tc-mk-start',
        'bot_stop' => 'tc-mk-stop',
        'bot_restart' => 'tc-mk-restart',
        'algo_switch' => 'tc-mk-switch',
        'algo_cutover' => 'tc-mk-cutover',
        'engine_missing' => 'tc-mk-missing',
        'stranded_book' => 'tc-mk-stranded',
    ];

    private const MARKER_LABEL = [
        'algo_update' => 'algo update',
        'bot_start' => 'start',
        'bot_stop' => 'stop',
        'bot_restart' => 'restart',
        'algo_switch' => 'algo switch',
        'algo_cutover' => 'algo cutover',
        'engine_missing' => 'engine missing',
        'stranded_book' => 'stranded book',
    ];

    /**
     * @param array<int, array{price:string, side:string, state:string, at?:string}> $points chronological (oldest first)
     * @param array{pLow?:string, pHigh?:string, lastPrice?:string, width?:int, height?:int,
     *              markers?:array<int, array{kind:string, message:string, at:string}>}  $opts
     */
    public static function svg(array $points, array $opts = []): string
    {
        $w = (int) ($opts['width'] ?? 640);
        $h = (int) ($opts['height'] ?? 180);
        $padX = 8;
        $padY = 10;
        $lastPrice = isset($opts['lastPrice']) && (string) $opts['lastPrice'] !== ''
            ? (float) $opts['lastPrice'] : null;
        /** @var array<int, array{at:string, close:string}> $history oldest→newest */
        $history = (array) ($opts['history'] ?? []);

        // Y scale from the price envelope (range ∪ observed prices ∪ current
        // price ∪ historical closes), padded — history closes join the
        // envelope so the pale line never clips.
        $prices = array_map(static fn ($p) => (float) $p['price'], $points);
        foreach ($history as $hpt) {
            $prices[] = (float) $hpt['close'];
        }
        $lo = isset($opts['pLow']) ? (float) $opts['pLow'] : (($prices) ? min($prices) : 0.0);
        $hi = isset($opts['pHigh']) ? (float) $opts['pHigh'] : (($prices) ? max($prices) : 1.0);
        foreach ($prices as $p) {
            $lo = min($lo, $p);
            $hi = max($hi, $p);
        }
        if ($lastPrice !== null) {
            $lo = min($lo, $lastPrice);
            $hi = max($hi, $lastPrice);
        }
        if ($hi <= $lo) {
            $hi = $lo + 1.0;
        }
        $span = ($hi - $lo) * 1.05;
        $mid = ($hi + $lo) / 2;
        $lo = $mid - $span / 2;
        $hi = $mid + $span / 2;

        $yOf = static function (float $price) use ($lo, $hi, $h, $padY): float {
            $frac = ($price - $lo) / ($hi - $lo);
            $y = $h - $padY - $frac * ($h - 2 * $padY);
            return round(max(0.0, min((float) $h, $y)), 1);
        };

        $trend = count($history) >= 2 ? self::emaSeries($history) : [];

        $style = <<<CSS
<style>
 .tc-wrap{width:100%;overflow-x:auto}
 .tc-band{fill:rgba(0,209,178,.10)}
 .tc-grid{stroke:#e3e8ee;stroke-width:1}
 line.tc-lvl{stroke:#e3e8ee;stroke-width:1;stroke-dasharray:2 3}
 .tc-buy{fill:#00b894}
 .tc-sell{fill:#e74c3c}
 .tc-open{fill-opacity:.35;stroke-width:1.5}
 .tc-buy.tc-open{stroke:#00b894}
 .tc-sell.tc-open{stroke:#e74c3c}
 .tc-lbl{fill:#8898aa;font-size:10px}
 .tc-date{fill:#8898aa;font-size:9px}
 line.tc-day{stroke:#cfd6de;stroke-width:1;stroke-dasharray:1 3}
 line.tc-mark{stroke-width:1.5;stroke-dasharray:3 3;cursor:pointer}
 .tc-mk-algo{stroke:#8e44ad}
 .tc-mk-start{stroke:#00b894}
 .tc-mk-stop{stroke:#e67e22}
 .tc-mk-restart{stroke:#3498db}
 .tc-mk-switch{stroke:#f39c12}
 .tc-mk-cutover{stroke:#34495e}
 .tc-mk-missing{stroke:#c0392b}
 .tc-mk-stranded{stroke:#7b241c}
 .tc-legend i.l{width:14px;height:0;display:inline-block}
 .tc-legend i.l.tc-mk-algo{border-top:2px dashed #8e44ad}
 .tc-legend i.l.tc-mk-start{border-top:2px dashed #00b894}
 .tc-legend i.l.tc-mk-stop{border-top:2px dashed #e67e22}
 .tc-legend i.l.tc-mk-restart{border-top:2px dashed #3498db}
 .tc-legend i.l.tc-mk-switch{border-top:2px dashed #f39c12}
 .tc-legend i.l.tc-mk-cutover{border-top:2px dashed #34495e}
 .tc-legend i.l.tc-mk-missing{border-top:2px dashed #c0392b}
 .tc-legend i.l.tc-mk-stranded{border-top:2px dashed #7b241c}
 circle.tc-buy,circle.tc-sell{cursor:pointer}
 .tc-legend{display:flex;gap:14px;margin-top:6px;font-size:11px;color:#6b7280}
 .tc-legend span{display:inline-flex;align-items:center;gap:5px}
 .tc-legend i.d{width:9px;height:9px;border-radius:50%;display:inline-block}
 .tc-legend i.tc-buy{background:#00b894}
 .tc-legend i.tc-sell{background:#e74c3c}
 .tc-legend i.tc-open{background:transparent;border:1.5px solid #00b894}
 .tc-history{stroke:#b6bec9;stroke-width:1;opacity:.55;fill:none}
</style>
CSS;

        // current-price marker: dashed line + label, drawn with or without points
        $priceLine = '';
        $priceStyle = '';
        $priceLegend = '';
        if ($lastPrice !== null) {
            $priceStyle = '<style>'
                . ' line.tc-price{stroke:#1a56db;stroke-width:1.5;stroke-dasharray:5 4}'
                . ' .tc-price-lbl{fill:#1a56db;font-size:10px;font-weight:700}'
                . ' .tc-legend i.l.tc-price{width:14px;height:0;border-top:2px dashed #1a56db;display:inline-block}'
                . '</style>';
            $priceLegend = '<span><i class="l tc-price"></i>current price</span>';
        }

        $trendStyle = '';
        if ($trend) {
            $trendStyle = '<style>'
                . ' .tc-trend{stroke:#d63384;stroke-width:1.5;opacity:.8;fill:none}'
                . ' .tc-legend i.l.tc-trend{width:14px;height:0;border-top:2px solid #d63384;display:inline-block}'
                . '</style>';
        }

        if (!$points) {
            if ($lastPrice !== null) {
                $y = $yOf($lastPrice);
                $priceLine = '<line class="tc-price" x1="' . $padX . '" y1="' . $y . '" x2="' . ($w - $padX) . '" y2="' . $y . '"/>'
                    . '<text x="' . ($w - $padX) . '" y="' . max(10, $y - 4) . '" text-anchor="end" class="tc-price-lbl">' . self::esc(self::money((string) $opts['lastPrice'])) . '</text>';
            }
            // full-width, time-scaled historical line drawn BEHIND everything
            $historyLine = self::historyPolylineTimeScaled($history, $padX, $w, $yOf);
            $trendLine = $trend ? self::historyPolylineTimeScaled($trend, $padX, $w, $yOf, 'tc-trend') : '';
            // faint grid-level lines + shaded range band — same as the
            // with-points branch, so a flat run still shows where its grid
            // is armed; drawn over the history line, under the current-price
            // line and the caption (both stay on top)
            $bandLine = self::levelsAndBand($opts, $yOf, $padX, $w, $h);
            $empty = '<text x="' . ($w / 2) . '" y="' . ($h / 2) . '" text-anchor="middle" class="tc-lbl">No trades yet</text>';
            return $style . $priceStyle . $trendStyle . '<div class="tc-wrap"><svg viewBox="0 0 ' . $w . ' ' . $h . '" width="100%" height="' . $h . '" role="img" aria-label="Trade points">' . $historyLine . $trendLine . $bandLine . $priceLine . $empty . '</svg></div>';
        }

        $n = count($points);
        $points = array_values($points);
        $xStep = $n > 1 ? ($w - 2 * $padX) / ($n - 1) : 0;

        $svg = '';
        // pale historical price line — under everything else (grid, band,
        // points, markers), matched per trade-point timestamp, edges pinned
        // to the candle at/before the first point and the newest candle.
        $svg .= self::historyPolylineForPoints($history, $points, $xStep, $padX, $w, $yOf);

        if ($trend) {
            $svg .= self::historyPolylineForPoints($trend, $points, $xStep, $padX, $w, $yOf, 'tc-trend');
        }

        // faint grid-level lines (points visibly sit on their ladder lines)
        // + the shaded range band, shared with the empty-state branch
        $svg .= self::levelsAndBand($opts, $yOf, $padX, $w, $h);

        if ($lastPrice !== null) {
            $y = $yOf($lastPrice);
            $priceLine = '<line class="tc-price" x1="' . $padX . '" y1="' . $y . '" x2="' . ($w - $padX) . '" y2="' . $y . '"/>'
                . '<text x="' . ($w - $padX) . '" y="' . max(10, $y - 4) . '" text-anchor="end" class="tc-price-lbl">' . self::esc(self::money((string) $opts['lastPrice'])) . '</text>';
        }

        // day boundaries: a faint tick + date label wherever the point date
        // rolls over, plus the first point's date anchoring the left edge
        $svg .= self::dayBoundaries($points, $xStep, $padX, $h);

        // lifecycle markers (algo updates, start/stop/restart) interleaved
        // between trade points by timestamp
        [$markerSvg, $markerKinds] = self::markers((array) ($opts['markers'] ?? []), $points, $xStep, $padX, $w, $h, $padY);
        $svg .= $markerSvg;

        foreach ($points as $i => $pt) {
            $x = round($padX + $i * $xStep, 1);
            $y = $yOf((float) $pt['price']);
            $sideName = ($pt['side'] ?? 'Buy') === 'Sell' ? 'Sell' : 'Buy';
            $side = $sideName === 'Sell' ? 'tc-sell' : 'tc-buy';
            $state = (string) ($pt['state'] ?? '');
            $open = in_array($state, ['BUY_OPEN', 'SELL_OPEN', 'Intended', 'PartFilled'], true) ? ' tc-open' : '';
            $lvl = isset($pt['level']) ? sprintf('L%02d ', (int) $pt['level']) : '';
            $at = (string) ($pt['at'] ?? '');
            // native hover tooltip — no JS
            $title = sprintf('%s%s @ %s — %s', $lvl, $sideName, self::money((string) $pt['price']), $state !== '' ? $state : 'n/a')
                . ($at !== '' ? ' — ' . $at : '');
            $svg .= '<circle class="' . $side . $open . '" cx="' . $x . '" cy="' . $y . '" r="4"><title>' . self::esc($title) . '</title></circle>';
        }

        $markerLegend = '';
        foreach ($markerKinds as $kind => $_) {
            $markerLegend .= '<span><i class="l ' . self::MARKER_CLASS[$kind] . '"></i>' . self::esc(self::MARKER_LABEL[$kind]) . '</span>';
        }

        $legend = '<div class="tc-legend">'
            . '<span><i class="d tc-buy"></i>Buy</span>'
            . '<span><i class="d tc-sell"></i>Sell</span>'
            . '<span><i class="d tc-buy tc-open"></i>open order</span>'
            . ($trend ? '<span><i class="l tc-trend"></i>trend (EMA20)</span>' : '')
            . $priceLegend
            . $markerLegend
            . '</div>';

        return $style . $priceStyle . $trendStyle
            . '<div class="tc-wrap"><svg viewBox="0 0 ' . $w . ' ' . $h . '" width="100%" height="' . $h . '" role="img" aria-label="Trade points">'
            . $svg . $priceLine
            . '</svg></div>'
            . $legend;
    }

    /**
     * Faint vertical tick + "MM-DD" label at every date rollover between
     * consecutive points, plus the first point's date at the left edge. When
     * boundaries crowd, lines all draw but only every k-th gets a label.
     */
    private static function dayBoundaries(array $points, float $xStep, int $padX, int $h): string
    {
        $bounds = [];
        $prev = null;
        foreach ($points as $i => $pt) {
            $at = (string) ($pt['at'] ?? '');
            if ($at === '') {
                continue;
            }
            $day = substr($at, 0, 10);
            if ($prev !== null && $day !== $prev) {
                $bounds[] = ['i' => $i, 'day' => $day];
            }
            $prev = $day;
        }
        $svg = '';
        $first = (string) ($points[0]['at'] ?? '');
        if ($first !== '') {
            $svg .= '<text x="' . $padX . '" y="' . ($h - 1) . '" class="tc-date">' . self::esc(substr($first, 5, 5)) . '</text>';
        }
        $labelEvery = max(1, (int) ceil(count($bounds) / 6));
        foreach ($bounds as $k => $b) {
            $x = round($padX + ($b['i'] - 0.5) * $xStep, 1);
            $svg .= '<line class="tc-day" x1="' . $x . '" y1="0" x2="' . $x . '" y2="' . $h . '"/>';
            if ($k % $labelEvery === 0) {
                $svg .= '<text x="' . ($x + 2) . '" y="' . ($h - 1) . '" class="tc-date">' . self::esc(substr($b['day'], 5)) . '</text>';
            }
        }
        return $svg;
    }

    /**
     * Vertical dashed marker per lifecycle event, positioned between the
     * trade points that bracket its timestamp. Markers older than the oldest
     * visible point are outside the window and skipped.
     *
     * @return array{0:string, 1:array<string, true>} svg + kinds drawn (for the legend)
     */
    private static function markers(array $markers, array $points, float $xStep, int $padX, int $w, int $h, int $padY): array
    {
        $svg = '';
        $kinds = [];
        $firstAt = (string) ($points[0]['at'] ?? '');
        foreach ($markers as $m) {
            $kind = (string) ($m['kind'] ?? '');
            $at = (string) ($m['at'] ?? '');
            if (!isset(self::MARKER_CLASS[$kind]) || $at === '' || $firstAt === '' || $at < $firstAt) {
                continue;
            }
            // count points at or before the marker; the line sits between them
            $k = 0;
            foreach ($points as $pt) {
                if ((string) ($pt['at'] ?? '') <= $at) {
                    $k++;
                } else {
                    break;
                }
            }
            $x = round(min((float) ($w - $padX), $padX + ($k - 0.5) * $xStep), 1);
            $title = sprintf('%s — %s%s', self::MARKER_LABEL[$kind], $at, ($m['message'] ?? '') !== '' ? ' — ' . $m['message'] : '');
            $svg .= '<line class="tc-mark ' . self::MARKER_CLASS[$kind] . '" x1="' . $x . '" y1="' . $padY . '" x2="' . $x . '" y2="' . ($h - $padY) . '">'
                . '<title>' . self::esc($title) . '</title></line>';
            $kinds[$kind] = true;
        }
        return [$svg, $kinds];
    }

    /**
     * Faint grid-level lines (opts['levels']) plus the shaded grid-range
     * band with its high/low labels (opts['pLow']/opts['pHigh']) — shared
     * verbatim between the with-points and empty-state branches so a flat
     * run still shows where its grid is armed.
     */
    private static function levelsAndBand(array $opts, callable $yOf, int $padX, int $w, int $h): string
    {
        $svg = '';
        foreach ((array) ($opts['levels'] ?? []) as $lvl) {
            $y = $yOf((float) $lvl);
            $svg .= '<line class="tc-lvl" x1="' . $padX . '" y1="' . $y . '" x2="' . ($w - $padX) . '" y2="' . $y . '"/>';
        }
        if (isset($opts['pLow'], $opts['pHigh'])) {
            $yHi = $yOf((float) $opts['pHigh']);
            $yLo = $yOf((float) $opts['pLow']);
            $svg .= '<rect class="tc-band" x="' . $padX . '" y="' . $yHi . '" width="' . ($w - 2 * $padX) . '" height="' . round($yLo - $yHi, 1) . '"/>';
            $svg .= '<text x="' . ($w - $padX) . '" y="' . max(10, $yHi - 2) . '" text-anchor="end" class="tc-lbl">' . self::esc(self::money((string) $opts['pHigh'])) . '</text>';
            $svg .= '<text x="' . ($w - $padX) . '" y="' . min($h - 2, $yLo + 10) . '" text-anchor="end" class="tc-lbl">' . self::esc(self::money((string) $opts['pLow'])) . '</text>';
        }
        return $svg;
    }

    /**
     * Pale historical-price polyline matched to each trade point's own x
     * position: y at point i is the reconstructed candle close whose time is
     * the latest ≤ the point's timestamp (nearest fallback at the edges).
     * The line is pinned to the full chart width — a leading vertex at the
     * candle at/before the first point, a trailing vertex at the newest
     * candle — so it always spans left edge to right edge even when trade
     * points don't.
     *
     * @param array<int, array{at:string, close:string}> $history oldest→newest
     * @param array<int, array{at?:string}> $points
     */
    private static function historyPolylineForPoints(array $history, array $points, float $xStep, int $padX, int $w, callable $yOf, string $class = 'tc-history'): string
    {
        if (!$history || !$points) {
            return '';
        }
        $verts = [];
        $verts[] = [$padX, $yOf((float) self::matchHistoryClose($history, (string) ($points[0]['at'] ?? '')))];
        foreach ($points as $i => $pt) {
            $x = round($padX + $i * $xStep, 1);
            $close = self::matchHistoryClose($history, (string) ($pt['at'] ?? ''));
            $verts[] = [$x, $yOf((float) $close)];
        }
        $newest = (string) $history[count($history) - 1]['close'];
        $verts[] = [$w - $padX, $yOf((float) $newest)];

        return self::polylineFromVerts($verts, $class);
    }

    /**
     * Empty-state historical polyline: no trade points to anchor to, so the
     * full history is time-scaled across the chart width instead.
     *
     * @param array<int, array{at:string, close:string}> $history oldest→newest
     */
    private static function historyPolylineTimeScaled(array $history, int $padX, int $w, callable $yOf, string $class = 'tc-history'): string
    {
        if (!$history) {
            return '';
        }
        $n = count($history);
        $tMin = strtotime((string) $history[0]['at']);
        $tMax = strtotime((string) $history[$n - 1]['at']);
        $span = $tMax - $tMin;
        $verts = [];
        foreach ($history as $hpt) {
            $t = strtotime((string) $hpt['at']);
            $x = $span > 0 ? round($padX + ($t - $tMin) / $span * ($w - 2 * $padX), 1) : $padX;
            $verts[] = [$x, $yOf((float) $hpt['close'])];
        }
        return self::polylineFromVerts($verts, $class);
    }

    /** Latest history close at or before $at; falls back to the earliest close when $at predates all of history. */
    private static function matchHistoryClose(array $history, string $at): string
    {
        $match = null;
        foreach ($history as $hpt) {
            if ((string) $hpt['at'] <= $at) {
                $match = $hpt;
            } else {
                break;
            }
        }
        return (string) ($match['close'] ?? $history[0]['close']);
    }

    /** Same shape as $history with each close replaced by the running EMA. */
    private static function emaSeries(array $history, int $period = 20): array
    {
        $k = 2 / ($period + 1);
        $ema = null;
        $out = [];
        foreach ($history as $hpt) {
            $close = (float) $hpt['close'];
            $ema = $ema === null ? $close : $close * $k + $ema * (1 - $k);
            $out[] = ['at' => (string) $hpt['at'], 'close' => (string) $ema];
        }
        return $out;
    }

    /** @param array<int, array{0:float, 1:float}> $verts */
    private static function polylineFromVerts(array $verts, string $class = 'tc-history'): string
    {
        $pts = implode(' ', array_map(static fn ($v) => $v[0] . ',' . $v[1], $verts));
        return '<polyline class="' . $class . '" points="' . $pts . '"/>';
    }

    private static function money(string $n): string
    {
        return number_format((float) $n, 0);
    }

    private static function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}
