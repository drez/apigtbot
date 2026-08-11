<?php
namespace App\Mcp\Tools;

use ApiGoat\Mcp\ToolError;
use ApiGoat\Sessions\AuthySession;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\MarketCollector;
use App\Domains\Bot\MarketStore;

/**
 * Self-contained market signal for choosing a grid: per-timeframe (1h/4h/1d)
 * EMA20/50/200, RSI14, ATR, trend and swing high/low, plus the current price.
 * The tool REFRESHES the stored data itself when it's stale (fetching klines
 * on demand), then answers entirely from the database + a fresh price — so the
 * caller reads only this response and never fetches anything. The collector
 * cron keeps the cache warm between calls.
 */
class GtbotMarketTool extends AbstractGtbotBase
{
    /**
     * @param ?string $priceOverride  live price stand-in (tests, no network)
     * @param ?BinanceGateway $gateway  gateway for refresh/price (tests inject a sim)
     */
    public function __construct(
        private ?string $priceOverride = null,
        private ?BinanceGateway $gateway = null
    ) {
    }

    public function name(): string
    {
        return 'gtbot_market';
    }

    public function description(): string
    {
        return 'Read-only market signal for choosing a grid: per-timeframe (1h/4h/1d) EMA20/50/200, RSI14, '
            . 'ATR (abs + %), ADX14 (trend strength: <20 ranging/grid-friendly, >30 trending), ATR% '
            . 'percentile rank (is volatility spiking vs its own history), taker buy ratio + volume '
            . 'z-score (aggression/participation), trend classification and recent swing high/low — served '
            . 'from the database (collected on a schedule) — plus the current LIVE price and where it sits '
            . 'in the run trading THAT symbol (active_run.refit_pending=true means your last refit has not '
            . 'applied yet — do not stack another). Use this to form a directional/volatility view, then '
            . 'set the grid with gtbot_set_grid. Signals are analytics, not a guarantee — the risk caps + '
            . 'kill switch remain the backstop. If a timeframe reads stale, the collector cron may be behind.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'symbol' => ['type' => 'string', 'description' => 'Trading pair (default: the active run\'s symbol, else BTCUSDT)'],
            ],
        ];
    }

    public function requiredRight(): ?array
    {
        return ['GridRun', 'r'];
    }

    protected function run(array $args, AuthySession $session): array
    {
        // Resolve the run BY SYMBOL when one is asked for — the newest active
        // run may trade a different pair, and showing its grid against this
        // symbol's price produced nonsense (in_range=false, 203812% position).
        $run = null;
        $symbolArg = strtoupper(trim((string) ($args['symbol'] ?? '')));
        if ($symbolArg !== '') {
            $run = $this->runForSymbol($symbolArg);
            $symbol = $symbolArg;
        } else {
            try {
                $run = $this->resolveRun($args);
            } catch (ToolError) {
                // market signal doesn't require a run
            }
            $symbol = strtoupper((string) ($run ? $run->getSymbol() : 'BTCUSDT'));
        }
        $gw = $this->gateway ?? MarketCollector::analysisGateway();

        // Self-heal: refresh the stored data if it's missing/stale, so the
        // answer is current without the caller fetching anything.
        try {
            MarketCollector::refreshIfStale($symbol, $gw);
        } catch (\Throwable $e) {
            error_log('gtbot_market refresh failed: ' . $e->getMessage());
        }

        $frames = MarketStore::summaries($symbol);
        $price = $this->livePrice($symbol, $gw);

        $payload = [
            'symbol' => $symbol,
            'price' => $price,
            'timeframes' => $frames,
            'source' => 'served from the database; auto-refreshed on call when stale',
        ];
        if (!$frames) {
            $payload['note'] = 'no market data available (refresh failed and cache is empty) — '
                . 'check network / GTBOT_ANALYSIS_BASE.';
        }

        if ($run) {
            $pLow = (float) $run->getPLow();
            $pHigh = (float) $run->getPHigh();
            $pos = ($pHigh > $pLow && $price !== null) ? round(($price - $pLow) / ($pHigh - $pLow) * 100, 1) : null;
            $payload['active_run'] = [
                'id' => (int) $run->getIdGridRun(),
                'symbol' => (string) $run->getSymbol(),
                'status' => (string) $run->getStatus(),
                'p_low' => (string) $run->getPLow(),
                'p_high' => (string) $run->getPHigh(),
                'n_levels' => (int) $run->getNLevels(),
                'price_position_pct' => $pos,
                'in_range' => $price !== null && $price > $pLow && $price < $pHigh,
                'refit_pending' => $this->refitPending($run),
            ];
            // Your own scored history — consult it before deciding again:
            // repeated Losses on similar reads should make you humbler.
            $payload['track_record'] = \App\Domains\Bot\DecisionScorer::trackRecord((int) $run->getIdGridRun());
        }
        return $payload ? $this->ok($payload) : $this->ok(['symbol' => $symbol]);
    }

    /** Newest run trading this symbol (running states first), or null. */
    private function runForSymbol(string $symbol): ?\App\GridRun
    {
        return \App\GridRunQuery::create()
            ->filterBySymbol($symbol)
            ->filterByStatus(['Live', 'Testnet', 'DryRun'], \Criteria::IN)
            ->orderByIdGridRun(\Criteria::DESC)
            ->findOne()
            ?? \App\GridRunQuery::create()
                ->filterBySymbol($symbol)
                ->filterByStatus('Done', \Criteria::NOT_EQUAL)
                ->orderByIdGridRun(\Criteria::DESC)
                ->findOne();
    }

    /** True when the run row's geometry differs from what the ladder was built on. */
    private function refitPending(\App\GridRun $run): bool
    {
        $applied = json_decode((string) ($run->getAppliedGeometry() ?? ''), true);
        if (!is_array($applied)) {
            return false;
        }
        return bccomp((string) ($applied['p_low'] ?? '0'), (string) $run->getPLow(), 8) !== 0
            || bccomp((string) ($applied['p_high'] ?? '0'), (string) $run->getPHigh(), 8) !== 0
            || (int) ($applied['n_levels'] ?? 0) !== (int) $run->getNLevels()
            || (string) ($applied['spacing'] ?? '') !== (string) $run->getSpacing()
            || (int) ($applied['deploy_pct'] ?? 100) !== (int) ($run->getDeployPct() ?? 100);
    }

    private function livePrice(string $symbol, BinanceGateway $gw): ?string
    {
        if ($this->priceOverride !== null) {
            return $this->priceOverride;
        }
        try {
            return $gw->tickerPrice($symbol);
        } catch (\Throwable) {
            return null;
        }
    }
}
