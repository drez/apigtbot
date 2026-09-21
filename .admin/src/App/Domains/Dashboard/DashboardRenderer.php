<?php

namespace App\Domains\Dashboard;

use App\Domains\Bot\SimWallet;

/**
 * Pure HTML renderer for the trading dashboard: a self-contained block with a
 * scoped <style>, KPI tiles, the inline-SVG trade-point chart, a daily-P/L
 * sparkbar, and latest-cycles / latest-events tables. No DB, no globals — the
 * view model comes in, HTML goes out (fully unit-testable). Mirrors the apicrm
 * DashboardRenderer conventions (va-card, kpi-*, dash-* classes).
 */
final class DashboardRenderer
{
    public function render(array $vm): string
    {
        $base = rtrim((string) ($vm['baseUrl'] ?? ''), '/') . '/';
        $run = $vm['run'] ?? null;
        $style = $this->style();

        if (!$run) {
            $band = $this->globalBand($vm['band'] ?? []);
            return $style . <<<HTML
<div class="dash">
  {$band}
  <div class="dash-section"><div class="dash-section-body">
    <p class="dash-empty">No grid run yet. Create one to start trading.</p>
    <a class="dash-action-btn" href="{$base}GridRun/edit/">Create a grid run</a>
  </div></div>
</div>
HTML;
        }

        $k = $vm['kpis'] ?? [];
        $money = static fn ($n): string => number_format((float) $n, 2);
        $btc = static fn ($n): string => rtrim(rtrim(number_format((float) $n, 8, '.', ''), '0'), '.') ?: '0';

        // ---- run header + health ----
        $killAlert = !empty($run['kill_switch']);
        $stale = !empty($run['heartbeat_stale']);
        $statusBadge = $this->esc((string) ($run['status'] ?? ''));
        $runId = (int) ($run['id'] ?? 0);
        $healthBits = [];
        if ($killAlert) {
            $healthBits[] = '<span class="pill is-alert">Kill switch ON</span>';
            // one-click resume: clears the switch via Dashboard/restart (see View onReadyJs)
            $healthBits[] = '<button type="button" class="dash-restart-btn" data-run="' . $runId . '">Restart trading</button>';
        }
        // Halted = parked on purpose: no daemon, so neither "live" nor "stale"
        // applies — say "held" in grey instead (see DashboardData::isHeld)
        $healthBits[] = !empty($run['held'])
            ? '<span class="pill is-muted" title="Parked: the daemon is stopped by design (Hold funds); Release funds brings it back to Live">held &middot; no daemon</span>'
            : ($stale
                ? '<span class="pill is-alert">heartbeat stale</span>'
                : '<span class="pill is-ok">heartbeat live</span>');
        // per-run switch (grid_run.sell_at_loss, default OFF): OFF = a stop-out
        // below cost is held and a Flatten that would book a loss is refused
        // (a profitable one proceeds — LossGuard); ON = losses may be realized
        $healthBits[] = !empty($run['sell_at_loss'])
            ? '<span class="pill is-alert" title="Stop-outs below cost and Flatten may realize losses (grid_run.sell_at_loss)">Sell at loss ON</span>'
            : (!empty($run['sell_when_starved'])
                ? '<span class="pill is-ok" title="Sells below cost only when the ladder is starved: legacy exits are repriced to market, closest first, just enough to refund the ladder (grid_run.sell_when_starved)">Sell at loss OFF · when starved</span>'
                : '<span class="pill is-ok" title="Never sells below cost: a trend stop-out under breakeven is held and a Flatten below breakeven is refused; exits above cost still proceed (grid_run.sell_at_loss)">Sell at loss OFF</span>');
        // deploy 0% is a POSITION, not a pause: entries are disabled but exits
        // keep working. Without this the run reads plain "Live" — identical to
        // a fully armed one — and prod 2026-09-14 had two runs (1 and 8) parked
        // at deploy 0 holding inventory with nothing on screen to say so.
        // Absent deploy_pct (older payloads) says nothing rather than guessing.
        if (isset($run['deploy_pct']) && (int) $run['deploy_pct'] === 0) {
            $healthBits[] = '<span class="pill is-warn" title="deploy_pct is 0: this run places no new entries — it only manages and exits what it already holds">deploy 0% &middot; exit only</span>';
        }
        if (isset($k['alloc_mode'])) {
            // an actively-managed trend arm is pinned whatever its column says:
            // the activator re-asserts its slice every 15 min
            $healthBits[] = ($k['alloc_auto'] ?? true)
                ? '<span class="pill is-ok" title="This run takes part in automatic pool reallocation: a budget change, retire or purge spreads the delta pro-rata across Auto runs, never below their floor">alloc Auto</span>'
                : '<span class="pill is-muted" title="' . $this->esc((string) ($k['alloc_mode_why'] ?? 'pinned')) . ' — the allocator will not move this slice">alloc ' . $this->esc((string) ($k['alloc_mode'] ?? 'Fixed')) . '</span>';
        }
        // Daemon controls: enqueue bot_command rows via Dashboard/command
        // (start=Resume, stop=Pause keeps working orders, reload restarts the
        // daemon process — the watchdog/systemd relaunches it within a minute)
        $healthBits[] = '<span class="dash-cmds">'
            . '<button type="button" class="dash-cmd-btn" data-action="start" data-run="' . $runId . '" title="Resume placing orders (clears Pause)">&#9654; Start</button>'
            . '<button type="button" class="dash-cmd-btn" data-action="stop" data-run="' . $runId . '" title="Pause — working orders stay on the exchange">&#10073;&#10073; Pause</button>'
            . '<button type="button" class="dash-cmd-btn" data-action="reload" data-run="' . $runId . '" title="Restart the daemon process (reloads code; reboots from the DB)">&#8635; Reload</button>'
            // Hold/Release funds: park the run in Halted (slice leaves the
            // shared pool) or bring a held run back to Live (BudgetGuard
            // re-checked server-side) — Dashboard/funds via View onReadyJs.
            . ((string) ($run['status'] ?? '') === 'Halted'
                ? '<button type="button" class="dash-funds-btn" data-action="release" data-run="' . $runId . '" title="Back to Live — the budget must still fit the shared pool; the watchdog respawns the daemon">&#9650; Release funds</button>'
                : '<button type="button" class="dash-funds-btn" data-action="hold" data-run="' . $runId . '" title="Cancel open buys, stop the daemon, park in Halted — frees this run\'s slice from the shared pool">&#9646; Hold funds</button>')
            . '</span>';
        $health = implode(' ', $healthBits);
        $runLabel = $this->esc((string) ($run['label'] ?? ''));
        $runSymbol = $this->esc((string) ($run['symbol'] ?? ''));
        $lastTick = $this->timeCell((string) ($run['last_tick_at'] ?? '—'));

        // ---- KPI tiles ----
        $pnl = (float) ($k['realized_pnl'] ?? 0);
        $pnlTone = $pnl < 0 ? ' is-neg' : ($pnl > 0 ? ' is-pos' : '');
        $today = (float) ($k['realized_today'] ?? 0);
        $todayTone = $today < 0 ? ' is-neg' : ($today > 0 ? ' is-pos' : '');
        $unrealTile = '';
        if (isset($k['unrealized_pnl']) && $k['unrealized_pnl'] !== null) {
            $u = (float) $k['unrealized_pnl'];
            $uTone = $u < 0 ? ' is-neg' : ($u > 0 ? ' is-pos' : '');
            $unrealTile = $this->tile($money($u) . ' USDT', 'Unrealized P/L', $base . 'BotOrder', $uTone);
        }
        [$baseAsset, $quoteAsset] = $this->splitSymbol((string) ($run['symbol'] ?? ''));
        $has = static fn (string $key): bool => isset($k[$key]) && $k[$key] !== null && $k[$key] !== '';

        // ---- position pill: FLAT / LONG qty ≈ value, next to the status pill ----
        $posPill = '';
        if (array_key_exists('inventory', $k) && $k['inventory'] !== null && $k['inventory'] !== '') {
            $inventory = (string) $k['inventory'];
            if (bccomp($inventory, '0', 8) <= 0) {
                $posPill = ' <span class="dash-pos-pill dash-pos-flat">FLAT</span>';
            } else {
                $posBase = SimWallet::assetsFor((string) ($run['symbol'] ?? ''))[0];
                $valuePart = $has('inventory_value') ? ' &#8776; ' . $this->esc($money($k['inventory_value'])) : '';
                // NOTE: the ≈ entity must stay RAW (see holdingsStrip) — only
                // the numbers/asset are escaped, never the assembled string.
                $posPill = ' <span class="dash-pos-pill dash-pos-long">LONG ' . $this->esc($btc($inventory)) . ' ' . $this->esc($posBase) . $valuePart . '</span>';
            }
        }

        // ---- P/L group (cycle count folds into the fees tile) ----
        $pnlTiles = ''
            . $this->tile($money($pnl) . ' ' . $quoteAsset, 'Realized P/L', $base . 'TradeCycle', $pnlTone)
            . $unrealTile
            . $this->tile($money($today) . ' ' . $quoteAsset, 'P/L today', $base . 'TradeCycle', $todayTone)
            . $this->tile($money($k['fees'] ?? 0) . ' ' . $quoteAsset, 'Fees · ' . (int) ($k['cycles'] ?? 0) . ' cycles', $base . 'TradeCycle');

        // ---- Position group: where the budget actually sits right now.
        // Cost basis folds into the inventory label (value − cost is already
        // the Unrealized tile); Wallet base sits beside Inventory so bot-
        // tracked vs account-held coins compare at a glance. The per-run
        // Wallet quote / Account value tiles are gone — the global band
        // carries the account-truth versions once, not per run. ----
        $invLabel = 'Inventory ' . $btc($k['inventory'] ?? 0) . ' ' . $baseAsset . ' · cost ' . $money($k['invested'] ?? 0);
        $invTile = $has('inventory_value')
            ? $this->tile($money($k['inventory_value']) . ' ' . $quoteAsset, $invLabel, $base . 'BotOrder')
            : $this->tile($btc($k['inventory'] ?? 0) . ' ' . $baseAsset, 'Inventory · cost ' . $money($k['invested'] ?? 0), $base . 'BotOrder');
        $positionTiles = ''
            . $invTile
            . ($has('bal_base')
                ? $this->tile($btc($k['bal_base']) . ' ' . $baseAsset, 'Wallet ' . $baseAsset, $base . 'BotOrder')
                : '')
            . $this->tile($money($k['committed_buys'] ?? 0) . ' ' . $quoteAsset, 'In open buys (' . (int) ($k['open_buys'] ?? 0) . ')', $base . 'BotOrder')
            . $this->tile($money($k['open_sell_value'] ?? 0) . ' ' . $quoteAsset, 'In open sells (' . (int) ($k['open_sells'] ?? 0) . ')', $base . 'BotOrder')
            . ($has('quote_uncommitted')
                ? $this->tile($money($k['quote_uncommitted']) . ' ' . $quoteAsset, 'Budget free', $base . 'BotOrder')
                : '');

        // ---- Allocation: how the slice is used, and what it earns ----
        // 'committed' is quote the allocator may NOT reclaim (tied up in
        // inventory); 'idle' is what a rebalance could actually move.
        $allocTiles = '';
        if ($has('alloc_slice')) {
            $sliceLabel = 'Slice · ' . ($k['alloc_auto'] ? 'Auto' : 'Fixed');
            $allocTiles .= $this->tile(
                $money($k['alloc_slice']) . ' ' . $quoteAsset,
                $sliceLabel,
                $base . 'GridRun'
            );
            $allocTiles .= $this->tile(
                $money($k['alloc_committed'] ?? 0) . ' ' . $quoteAsset,
                'Committed · floor ' . $money($k['alloc_floor'] ?? 0),
                $base . 'BotOrder'
            );
            $idleTone = bccomp((string) ($k['alloc_idle'] ?? '0'), '0', 8) > 0 ? '' : 'is-muted';
            $allocTiles .= $this->tile(
                $money($k['alloc_idle'] ?? 0) . ' ' . $quoteAsset,
                'Idle · reallocatable',
                $base . 'GridRun',
                $idleTone
            );
            // the capital-efficiency score auto-allocate ranks runs by; null
            // until the run has enough cycles to have earned an opinion
            $per1k = $k['alloc_per_1k_day'] ?? null;
            $eligible = (bool) ($k['alloc_eligible'] ?? false);
            if ($per1k !== null && $eligible) {
                $earnTone = bccomp((string) $per1k, '0', 6) >= 0 ? 'is-ok' : 'is-alert';
                $allocTiles .= $this->tile(
                    number_format((float) $per1k, 3) . ' ' . $quoteAsset,
                    'Earning /1k/day · ' . (int) ($k['alloc_cycles'] ?? 0) . ' cycles / ' . (int) ($k['alloc_window_days'] ?? 14) . 'd',
                    $base . 'TradeCycle',
                    $earnTone
                );
            } else {
                $allocTiles .= $this->tile(
                    'n/a',
                    'Earning /1k/day · ' . $this->esc((string) ($k['alloc_why'] ?? 'not enough history')),
                    $base . 'TradeCycle',
                    'is-muted'
                );
            }
        }

        $tiles = $this->tileGroup('P/L', $pnlTiles)
            . $this->tileGroup('Position', $positionTiles)
            . ($allocTiles !== '' ? $this->tileGroup('Allocation', $allocTiles) : '');

        // ---- trading chart ----
        // Mount point only: project.js (gcTradeChart) fetches Dashboard/chart
        // for the run + chosen timeframe and draws candles, fills, the grid
        // ladder and the trend arm's lines with lightweight-charts. The
        // <noscript>/pre-boot text is what a scriptless render shows.
        $chart = '<div class="dash-chart" data-run="' . $runId . '" data-base="' . $this->esc($base) . '" data-symbol="' . $runSymbol . '">'
            . '<div class="dash-chart-boot">Loading chart…</div>'
            . '</div>';

        // ---- daily P/L sparkbars ----
        $daily = $this->dailyBars($vm['daily'] ?? []);

        // ---- latest cycles ----
        $cycleRows = array_map(fn ($r) => $this->row([
            'L' . $this->esc((string) ($r['level'] ?? '')),
            $this->esc($money($r['buy'] ?? 0)) . ' → ' . $this->esc($money($r['sell'] ?? 0)),
            $this->esc($btc($r['qty'] ?? 0)),
            $this->pnlCell((float) ($r['pnl'] ?? 0)),
            $this->timeCell((string) ($r['at'] ?? '')),
        ]), $vm['cycles'] ?? []);
        $cyclesPanel = $this->panel('Latest cycles', $base . 'TradeCycle', 'No completed cycles yet', $cycleRows);

        // ---- latest events ----
        $eventRows = array_map(fn ($r) => $this->row([
            '<span class="pill ' . $this->eventTone((string) ($r['level'] ?? '')) . '">' . $this->esc((string) ($r['level'] ?? '')) . '</span>',
            $this->esc((string) ($r['kind'] ?? '')),
            $this->esc((string) ($r['message'] ?? '')),
            $this->timeCell((string) ($r['at'] ?? '')),
        ]), $vm['events'] ?? []);
        $eventsPanel = $this->panel('Latest events', $base . 'BotEvent', 'No events yet', $eventRows);

        $tabBar = $this->tabBar($vm['tabs'] ?? []);
        $band = $this->globalBand($vm['band'] ?? []);

        return <<<HTML
{$style}
<div class="dash">
  {$band}
  {$tabBar}
  <div class="dash-section">
    <div class="dash-run-head">
      <div><span class="dash-run-title">{$runLabel}</span> <span class="pill is-info">{$statusBadge}</span> <span class="dash-run-sym">{$runSymbol}</span>{$posPill}</div>
      <div class="dash-run-health">{$health}</div>
    </div>
    <div class="dash-section-body">
      {$tiles}
      <div class="dash-sub"><div class="dash-sub-label">Market · {$runSymbol}</div>{$chart}</div>
      <div class="dash-sub"><div class="dash-sub-label">Realized P/L by day</div>{$daily}</div>
      <div class="dash-meta">Run #{$runId} · last tick {$lastTick}</div>
    </div>
  </div>
  {$cyclesPanel}
  {$eventsPanel}
</div>
HTML;
    }

    /**
     * One tab per active run, health dot included, so an unhealthy run stays
     * visible even when its tab isn't open. Hidden below two runs — a lone
     * run renders exactly the pre-tabs page.
     */
    private function tabBar(array $tabs): string
    {
        if (count($tabs) < 2) {
            return '';
        }
        $links = '';
        foreach ($tabs as $t) {
            $dot = !empty($t['kill_switch']) ? 'dot-alert'
                : (!empty($t['held']) ? 'dot-held'
                : (!empty($t['heartbeat_stale']) ? 'dot-warn' : 'dot-ok'));
            $sel = !empty($t['selected']) ? ' is-selected' : '';
            $label = $this->esc((string) ($t['symbol'] ?? '')) . ' #' . (int) ($t['id'] ?? 0);
            $href = $this->esc((string) ($t['href'] ?? '#'));
            $links .= '<a class="dash-tab' . $sel . '" href="' . $href . '">'
                . '<span class="dash-tab-dot ' . $dot . '"></span>' . $label . '</a>';
        }
        return '<div class="dash-tabs">' . $links . '</div>';
    }

    /**
     * The global system band: mode pill + switch, budget, wallet USDT, account
     * value, realized P/L (global, current-mode-per-run) and total P/L, plus
     * a holdings strip. The system trades ONE shared wallet (see ModeSwitch),
     * so this is system-wide, not scoped to the currently displayed run — it
     * renders above the tab bar, outside any run's section.
     */
    private function globalBand(array $band): string
    {
        $mode = (string) ($band['mode'] ?? 'none');
        $pill = '<span class="dash-mode-pill dash-mode-' . $this->esc($mode) . '">' . $this->esc(strtoupper($mode)) . '</span>';
        $buttons = match ($mode) {
            'simulated' => $this->modeBtn('real', 'Switch to REAL'),
            'real' => $this->modeBtn('simulated', 'Switch to SIMULATED'),
            'mixed' => $this->modeBtn('simulated', 'Switch to SIMULATED') . $this->modeBtn('real', 'Switch to REAL'),
            default => '', // 'none' — no runs to flip
        };

        $money = static fn ($n): string => number_format((float) $n, 2);
        $dash = static fn (?string $n): string => $n !== null ? $money($n) : '—';
        $tone = static function (?string $n): string {
            if ($n === null) {
                return '';
            }
            $f = (float) $n;
            return $f < 0 ? ' is-neg' : ($f > 0 ? ' is-pos' : '');
        };

        $budget = $band['budget'] ?? null;
        $slices = $band['slices_sum'] ?? null;
        $useAll = !empty($band['use_all_funds']);
        $budgetSub = ($budget !== null && $slices !== null)
            ? '<div class="dash-band-sub">' . $this->esc($money($slices)) . ' slices</div>'
            : '';
        // Why the slices stop short of the budget: the automatic allocators
        // plan against cap − gtbot_pool_reserve_pct, while the overcommit
        // guard still enforces the full cap. Without this line the operator
        // reads the gap as capital the machine forgot to put to work.
        $reservePct = $band['pool_reserve_pct'] ?? null;
        if ($reservePct !== null && bccomp((string) $reservePct, '0', 2) > 0 && isset($band['budget_allocatable'])) {
            $budgetSub .= '<div class="dash-band-sub">' . $this->esc($money($band['budget_allocatable']))
                . ' allocatable · ' . $this->esc(rtrim(rtrim((string) $reservePct, '0'), '.')) . '% reserve</div>';
        }
        // Edit budget: a pencil swaps the tile into an inline number input;
        // the JS confirms, POSTs Dashboard/budget (config write + Reload on
        // every active run) and warns that slices only resize at the next
        // refit. Hidden while gtbot_use_all_funds makes the cap follow the
        // wallet — there is no number to edit then, the tile says so.
        if ($useAll) {
            $budgetSub .= '<div class="dash-band-sub">all wallet funds · seed ' . $this->esc($money($band['budget_fixed'] ?? $budget)) . '</div>';
        } elseif ($budget !== null) {
            $wholeBudget = bcadd((string) $budget, '0', 0);
            $wholeSlices = $slices !== null ? bcadd((string) $slices, '0', 0) : '0';
            $budgetSub .= '<button type="button" class="dash-budget-edit" data-budget="' . $this->esc($wholeBudget) . '" data-slices="' . $this->esc($wholeSlices) . '" title="Change the shared budget (all active runs reload; slices are reallocated immediately)">&#9998; edit</button>'
                . '<form class="dash-budget-form" hidden><input type="number" name="budget" min="1" step="1" value="' . $this->esc($wholeBudget) . '" aria-label="New shared budget (USDT)">'
                . '<button type="submit">Save</button><button type="button" class="dash-budget-cancel">Cancel</button></form>';
        }

        $realizedToday = $band['realized_today'] ?? null;
        $realizedSub = $realizedToday !== null
            ? '<div class="dash-band-sub">' . $this->esc($money($realizedToday)) . ' today</div>'
            : '';

        $partialSub = !empty($band['value_partial'])
            ? '<div class="dash-band-sub">partial — some assets unpriced</div>'
            : '';

        // wallet-level drawdown floor under the Account value; the tile goes
        // red when equity sits below it (the daemons are about to stop, or did)
        $ddFloor = $band['drawdown_floor'] ?? null;
        $floorSub = $ddFloor !== null
            ? '<div class="dash-band-sub">floor ' . $this->esc($money($ddFloor)) . '</div>'
            : '';
        $accountTone = !empty($band['drawdown_under']) ? ' is-neg' : '';

        $tiles = ''
            . $this->bandTile('Budget', $dash($budget), $budgetSub)
            . $this->bandTile('Wallet USDT', $dash($band['wallet_usdt'] ?? null))
            . $this->bandTile('Account value', $dash($band['account_value'] ?? null), $partialSub . $floorSub, $accountTone)
            . $this->bandTile('Realized P/L', $dash($band['realized_total'] ?? null), $realizedSub, $tone($band['realized_total'] ?? null))
            . $this->bandTile('Total P/L', $dash($band['total_pl'] ?? null), '', $tone($band['total_pl'] ?? null));

        $holdings = $this->holdingsStrip($band['assets'] ?? [], $band);

        return <<<HTML
<div class="dash-band">
  <div class="dash-band-top">{$pill}{$buttons}</div>
  <div class="dash-band-tiles">{$tiles}</div>
  {$holdings}
</div>
HTML;
    }

    private function bandTile(string $label, string $value, string $sub = '', string $tone = ''): string
    {
        return '<div class="dash-band-tile' . $tone . '"><div class="kpi-val">' . $this->esc($value) . '</div>'
            . '<div class="kpi-lbl">' . $this->esc($label) . '</div>' . $sub . '</div>';
    }

    private function modeBtn(string $target, string $label): string
    {
        return '<button type="button" class="dash-mode-btn" data-target="' . $this->esc($target) . '">' . $this->esc($label) . '</button>';
    }

    /** Holdings strip: a full-width grid of small labeled cells — one per
     *  wallet asset (qty + ≈USDT value, "—" when unpriced), then the
     *  committed-capital figures: Holdings / Open sells / Open buys / Free
     *  budget (red tone when free budget has gone negative). */
    private function holdingsStrip(array $assets, array $band = []): string
    {
        if (!$assets) {
            return '';
        }
        // NOTE: the ≈ entity must stay RAW — number_format's output is
        // digits/comma/period/minus only (inherently HTML-safe), so numbers
        // are escaped inline and the result is used verbatim below, never
        // passed through $this->esc() again (that would double-encode the
        // entity's '&' into '&amp;#8776;').
        $money = static fn ($n): string => number_format((float) $n, 2);
        $dash = fn (?string $n): string => $n !== null ? '&#8776;' . $this->esc($money($n)) : '—';
        $cell = static function (string $valueHtml, string $label, string $extraClass = ''): string {
            return '<div class="dash-holding' . $extraClass . '">'
                . '<div class="dh-val">' . $valueHtml . '</div>'
                . '<div class="dh-lbl">' . $label . '</div></div>';
        };

        $cells = '';
        foreach ($assets as $a) {
            $asset = $this->esc((string) ($a['asset'] ?? ''));
            $qty = $this->esc($this->fmtQty((string) ($a['qty'] ?? '0')));
            $value = $a['value'] ?? null;
            $sub = $value !== null ? '&#8776;' . $this->esc($money($value)) : '—';
            $cells .= $cell($qty . ' <span class="dh-sub">' . $sub . '</span>', $asset . ' wallet');
        }

        $freeBudget = $band['free_budget'] ?? null;
        $freeTone = $freeBudget !== null && (float) $freeBudget < 0 ? ' is-neg' : '';
        $cells .= $cell($dash($band['holdings_value'] ?? null), 'Holdings')
            . $cell($dash($band['open_sell_value'] ?? null), 'Open sells')
            . $cell($dash($band['open_buy_value'] ?? null), 'Open buys')
            . $cell($dash($freeBudget), 'Free budget', ' dash-holdings-free' . $freeTone);

        return '<div class="dash-holdings">' . $cells . '</div>';
    }

    /** Trimmed decimal formatting for base-asset quantities (same trimming as the KPI $btc closure). */
    private function fmtQty(string $n): string
    {
        return rtrim(rtrim(number_format((float) $n, 8, '.', ''), '0'), '.') ?: '0';
    }

    private function dailyBars(array $daily): string
    {
        if (!$daily) {
            return '<div class="dash-empty">No P/L history yet</div>';
        }
        $max = 0.0;
        foreach ($daily as $d) {
            $max = max($max, abs((float) ($d['pnl'] ?? 0)));
        }
        $max = $max ?: 1.0;
        $bars = '';
        foreach ($daily as $d) {
            $v = (float) ($d['pnl'] ?? 0);
            $hgt = (int) round(abs($v) / $max * 46) + 2;
            $tone = $v < 0 ? 'is-neg' : 'is-pos';
            $day = $this->esc(substr((string) ($d['day'] ?? ''), 5)); // MM-DD
            $bars .= '<div class="dbar"><div class="dbar-fill ' . $tone . '" style="height:' . $hgt . 'px" title="' . $this->esc(number_format($v, 2)) . '"></div><div class="dbar-lbl">' . $day . '</div></div>';
        }
        return '<div class="dbars">' . $bars . '</div>';
    }

    /** A captioned row of KPI tiles. */
    private function tileGroup(string $caption, string $tilesHtml): string
    {
        return '<div class="dash-kpi-group"><div class="dash-sub-label">' . $this->esc($caption) . '</div>'
            . '<div class="dash-summary">' . $tilesHtml . '</div></div>';
    }

    /** "BTCUSDT" → ["BTC", "USDT"] (same suffix convention as the daemon). */
    private function splitSymbol(string $symbol): array
    {
        if ($symbol === '') {
            return ['base', 'quote'];
        }
        $quote = str_ends_with($symbol, 'USDT') ? 'USDT' : substr($symbol, -3);
        return [substr($symbol, 0, strlen($symbol) - strlen($quote)) ?: $symbol, $quote];
    }

    private function tile(string $value, string $label, string $href, string $tone = ''): string
    {
        $value = $this->esc($value);
        $label = $this->esc($label);
        $href = $this->esc($href);
        return <<<HTML
<a class="dash-summary-tile{$tone}" href="{$href}"><div class="kpi-val">{$value}</div><div class="kpi-lbl">{$label}</div></a>
HTML;
    }

    /** @param string[] $rowsHtml */
    private function panel(string $title, string $href, string $emptyText, array $rowsHtml): string
    {
        $title = $this->esc($title);
        $href = $this->esc($href);
        $body = $rowsHtml
            ? '<table>' . implode('', $rowsHtml) . '</table>'
            : '<div class="dash-empty">' . $this->esc($emptyText) . '</div>';
        return <<<HTML
<div class="dash-panel va-card">
  <div class="panel-heading"><span>{$title}</span><a class="panel-link" href="{$href}">View all</a></div>
  {$body}
</div>
HTML;
    }

    /** @param string[] $cells already-escaped HTML */
    private function row(array $cells): string
    {
        return '<tr><td>' . implode('</td><td>', $cells) . '</td></tr>';
    }

    private function pnlCell(float $v): string
    {
        $tone = $v < 0 ? 'is-neg' : ($v > 0 ? 'is-pos' : '');
        return '<span class="pnl ' . $tone . '">' . $this->esc(number_format($v, 2)) . '</span>';
    }

    private function eventTone(string $level): string
    {
        return match ($level) {
            'Alert', 'Error' => 'is-alert',
            'Warn' => 'is-warn',
            default => 'is-info',
        };
    }

    private function style(): string
    {
        return <<<'HTML'
<style>
 .dash{padding:4px 0 24px}
 .dash a{text-decoration:none;color:inherit}
 .dash .kpi-val{font-size:1.5rem;font-weight:700;line-height:1.1}
 .dash .kpi-lbl{margin-top:4px;font-size:.75rem;color:#6b7280;text-transform:uppercase;letter-spacing:.02em}
 .dash .dash-section{background:#fff;border:1px solid #e3e8ee;border-radius:12px;margin-bottom:1rem;overflow:hidden;box-shadow:0 1px 3px rgba(10,37,64,.06)}
 .dash .dash-run-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:12px 18px;background:#fafbfd;border-bottom:1px solid #eef1f5}
 .dash .dash-run-title{font-weight:700;color:#0a2540;font-size:15px}
 .dash .dash-run-sym{color:#8898aa;font-size:13px;margin-left:6px}
 .dash .dash-section-body{padding:14px 18px}
 .dash .dash-summary{display:flex;gap:1rem;flex-wrap:wrap}
 .dash .dash-kpi-group{margin-top:12px}
 .dash .dash-kpi-group:first-child{margin-top:0}
 .dash .dash-kpi-group .dash-sub-label{margin-bottom:6px}
 .dash .dash-summary-tile{flex:1 1 130px;min-width:120px}
 .dash .dash-summary-tile.is-pos .kpi-val{color:#00916e}
 .dash .dash-summary-tile.is-neg .kpi-val{color:#c0392b}
 .dash .dash-sub{margin-top:16px}
 .dash .dash-sub-label{font-size:11px;font-weight:700;letter-spacing:.06em;color:#8898aa;text-transform:uppercase;margin-bottom:8px}
 .dash .dash-meta{margin-top:14px;font-size:12px;color:#8898aa}
 .dash .dash-chart{position:relative;border:1px solid #e3e8ee;border-radius:10px;background:#fff;overflow:hidden}
 .dash .dash-chart-boot{padding:24px;font-size:12px;color:#8898aa;text-align:center}
 .dash .dash-chart-bar{display:flex;align-items:center;gap:6px;flex-wrap:wrap;padding:8px 10px;border-bottom:1px solid #eef1f5;background:#fafbfd}
 .dash .dash-chart-tf{border:1px solid #d5dbe3;background:#fff;color:#425466;border-radius:6px;padding:3px 10px;font-size:12px;font-weight:600;cursor:pointer}
 .dash .dash-chart-tf.is-active{background:#0a2540;border-color:#0a2540;color:#fff}
 .dash .dash-chart-status{margin-left:auto;font-size:11px;color:#8898aa;white-space:nowrap}
 .dash .dash-chart-status .pill{margin-left:6px}
 .dash .dash-chart-canvas{height:420px;width:100%}
 .dash .dash-chart-legend{display:flex;gap:14px;flex-wrap:wrap;padding:6px 10px;font-size:11px;color:#6b7280;border-top:1px solid #eef1f5}
 .dash .dash-chart-legend i{display:inline-block;width:10px;height:10px;border-radius:2px;margin-right:4px;vertical-align:-1px}
 .dash .dash-chart-legend i.l{height:0;width:14px;border-top:2px solid;border-radius:0;vertical-align:2px}
 .dash .pill{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700}
 .dash .pill.is-ok{background:#e6f7f2;color:#00916e}
 .dash .pill.is-info{background:#e8f0fe;color:#1a56db}
 .dash .pill.is-warn{background:#fff4e5;color:#b26a00}
 .dash .pill.is-alert{background:#fdecea;color:#c0392b}
 .dash .pill.is-muted{background:#eef1f5;color:#8898aa}
 .dash .dash-pos-pill{display:inline-block;margin-left:6px;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700}
 .dash .dash-pos-pill.dash-pos-flat{background:#eef1f5;color:#8898aa}
 .dash .dash-pos-pill.dash-pos-long{background:#e6fbf7;color:#00816a}
 .dash .pnl.is-pos{color:#00916e;font-weight:600}
 .dash .pnl.is-neg{color:#c0392b;font-weight:600}
 .dash .dbars{display:flex;gap:6px;align-items:flex-end;height:70px}
 .dash .dbar{display:flex;flex-direction:column;align-items:center;justify-content:flex-end;flex:0 0 auto}
 .dash .dbar-fill{width:16px;border-radius:3px 3px 0 0}
 .dash .dbar-fill.is-pos{background:#00b894}
 .dash .dbar-fill.is-neg{background:#e74c3c}
 .dash .dbar-lbl{margin-top:4px;font-size:9px;color:#8898aa}
 .dash .dash-panel{margin-top:1rem}
 .dash .dash-panel table{width:100%;border-collapse:collapse}
 .dash .dash-panel td{padding:6px 8px;border-bottom:1px solid #eee;font-size:.9rem}
 .dash .dash-empty{padding:12px 8px;color:#6b7280;font-style:italic}
 .dash .panel-heading{display:flex;justify-content:space-between;align-items:center;margin-bottom:4px}
 .dash .dash-action-btn{display:inline-block;padding:9px 16px;border-radius:8px;background:#00d1b2;color:#fff;font-weight:600;font-size:.9rem}
 .dash .dash-restart-btn{padding:4px 12px;border:0;border-radius:999px;background:#00d1b2;color:#fff;font-weight:700;font-size:11px;cursor:pointer}
 .dash .dash-restart-btn:disabled{opacity:.5;cursor:wait}
 .dash .dash-cmds{display:inline-flex;gap:6px;margin-left:6px}
 .dash .dash-cmd-btn{padding:4px 12px;border:1px solid #d0d7de;border-radius:999px;background:#fff;color:#425466;font-weight:700;font-size:11px;cursor:pointer}
 .dash .dash-cmd-btn:hover{border-color:#00d1b2;color:#0a2540}
 .dash .dash-cmd-btn:disabled{opacity:.5;cursor:wait}
 .dash .dash-funds-btn{padding:4px 12px;border:1px solid #b26a00;border-radius:999px;background:#fff8ef;color:#7a3b00;font-weight:700;font-size:11px;cursor:pointer}
 .dash .dash-funds-btn:hover{background:#b26a00;color:#fff}
 .dash .dash-funds-btn:disabled{opacity:.5;cursor:wait}
 .dash .dash-band{background:#fff;border:1px solid #e3e8ee;border-radius:12px;margin-bottom:1rem;padding:14px 18px;box-shadow:0 1px 3px rgba(10,37,64,.06)}
 .dash .dash-band-top{display:flex;align-items:center;gap:10px;margin-bottom:12px}
 .dash .dash-band-tiles{display:flex;gap:1rem;flex-wrap:wrap}
 .dash .dash-band-tile{flex:1 1 130px;min-width:120px}
 .dash .dash-budget-edit{margin-top:4px;padding:2px 10px;border:1px solid #6c757d;border-radius:999px;background:#fff;color:#495057;font-size:11px;font-weight:700;cursor:pointer}
 .dash .dash-budget-edit:hover{background:#495057;color:#fff}
 .dash .dash-budget-form{display:flex;gap:4px;margin-top:4px;align-items:center}
 .dash .dash-budget-form input{width:90px;padding:2px 6px;font-size:12px}
 .dash .dash-budget-form button{padding:2px 8px;font-size:11px;border-radius:999px;border:1px solid #6c757d;background:#fff;cursor:pointer}
 .dash .dash-budget-form button[type=submit]{background:#0d6efd;border-color:#0d6efd;color:#fff}
 .dash .dash-band-tile.is-pos .kpi-val{color:#00916e}
 .dash .dash-band-tile.is-neg .kpi-val{color:#c0392b}
 .dash .dash-band-sub{margin-top:2px;font-size:11px;color:#8898aa}
 .dash .dash-holdings{margin-top:12px;padding-top:12px;border-top:1px solid #eef1f5;display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:10px 18px}
 .dash .dash-holding .dh-val{font-size:1.05rem;font-weight:600;color:#0a2540;line-height:1.2;white-space:nowrap}
 .dash .dash-holding .dh-sub{font-size:.8rem;font-weight:500;color:#6b7280}
 .dash .dash-holding .dh-lbl{margin-top:2px;font-size:.72rem;color:#6b7280;text-transform:uppercase;letter-spacing:.02em}
 .dash .dash-holdings-free .dh-val{font-weight:700}
 .dash .dash-holdings-free.is-neg .dh-val{color:#c0392b}
 .dash .dash-mode-pill{display:inline-block;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:700;letter-spacing:.03em;color:#fff}
 .dash .dash-mode-pill.dash-mode-simulated{background:#f0ad4e}
 .dash .dash-mode-pill.dash-mode-real{background:#d9534f}
 .dash .dash-mode-pill.dash-mode-mixed{background:#d9534f}
 .dash .dash-mode-pill.dash-mode-none{background:#8898aa}
 .dash .dash-mode-btn{padding:5px 14px;border:1px solid #d0d7de;border-radius:999px;background:#fff;color:#425466;font-weight:700;font-size:11px;cursor:pointer}
 .dash .dash-mode-btn:hover{border-color:#d9534f;color:#0a2540}
 .dash .dash-mode-btn:disabled{opacity:.5;cursor:wait}
 .dash .dash-tabs{display:flex;gap:8px;margin-bottom:1rem;flex-wrap:wrap}
 .dash .dash-tab{display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border:1px solid #e3e8ee;border-radius:10px;background:#fff;color:#425466;font-weight:600;font-size:.9rem;box-shadow:0 1px 3px rgba(10,37,64,.06)}
 .dash .dash-tab.is-selected{border-color:#00d1b2;color:#0a2540;box-shadow:0 1px 3px rgba(0,209,178,.25)}
 .dash .dash-tab-dot{width:9px;height:9px;border-radius:999px;display:inline-block}
 .dash .dash-tab-dot.dot-ok{background:#00b894}
 .dash .dash-tab-dot.dot-warn{background:#f39c12}
 .dash .dash-tab-dot.dot-alert{background:#e74c3c}
 .dash .dash-tab-dot.dot-held{background:#b0bac6}
</style>
HTML;
    }

    /** A time cell: human-relative text with the absolute datetime on hover. */
    private function timeCell(string $dt): string
    {
        if ($dt === '' || $dt === '—') {
            return $this->esc($dt);
        }
        return '<span title="' . $this->esc($dt) . '">' . $this->esc($this->human($dt)) . '</span>';
    }

    /** "just now" / "5m ago" / "2h ago" / "3d ago" / "Jul 21" for older. */
    private function human(string $dt): string
    {
        $t = strtotime($dt);
        if ($t === false) {
            return $dt;
        }
        $diff = max(0, time() - $t);
        if ($diff < 45) {
            return 'just now';
        }
        if ($diff < 3600) {
            return (int) floor($diff / 60) . 'm ago';
        }
        if ($diff < 86400) {
            return (int) floor($diff / 3600) . 'h ago';
        }
        if ($diff < 7 * 86400) {
            return (int) floor($diff / 86400) . 'd ago';
        }
        return date('M j', $t);
    }

    private function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}
