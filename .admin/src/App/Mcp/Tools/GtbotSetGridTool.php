<?php
namespace App\Mcp\Tools;

use ApiGoat\Mcp\ToolError;
use ApiGoat\Sessions\AuthySession;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\GridMath;
use App\Domains\Bot\MarketStore;
use App\Domains\Bot\RegimeGate;
use App\Domains\Bot\RiskManager;
use App\Domains\Bot\TelegramNotifier;

/**
 * Claude's apply path: set the active run's grid geometry directly. This is
 * how the scheduled routine acts on its own market read — it decides the
 * range/levels (its "predictive edge") and writes them; the daemon re-anchors
 * the live ladder in place within a tick — held inventory is carried as
 * legacy exits, so a refit never waits for a flat grid. Validated hard (fee floor,
 * brackets price, sane bounds) so a bad call can't place a losing grid, and
 * every change is logged with Claude's reason (→ bot_event + Telegram).
 */
class GtbotSetGridTool extends AbstractGtbotBase
{
    public function __construct(private ?string $priceOverride = null)
    {
    }

    public function name(): string
    {
        return 'gtbot_set_grid';
    }

    public function description(): string
    {
        return 'Set the ACTIVE run\'s grid geometry (p_low, p_high, n_levels, spacing) based on your own '
            . 'market analysis — this is how you re-fit the live grid. The daemon re-anchors the ladder in '
            . 'place within a tick (held inventory rides out as legacy exits — no waiting for flat). '
            . 'Requires a `reason` (your rationale, logged + Telegrammed). '
            . 'Validated: the range MUST bracket the current price and clear the fee floor (spacing ≥ 3× '
            . 'round-trip fee) or it is rejected. Use dry_run:true to preview levels without applying. '
            . 'Pair with gtbot_market for the signals to base the decision on.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'p_low' => ['type' => 'string', 'description' => 'Grid floor (must be below current price)'],
                'p_high' => ['type' => 'string', 'description' => 'Grid ceiling (must be above current price)'],
                'n_levels' => ['type' => 'integer', 'description' => 'Number of grid intervals (≥ 2)'],
                'spacing' => ['type' => 'string', 'enum' => ['Geometric', 'Arithmetic'], 'description' => 'default Geometric'],
                'allocation' => ['type' => 'string', 'enum' => ['EqualQuote', 'EqualBase', 'BottomWeighted'], 'description' => 'capital ladder shape; BottomWeighted = 2x quote at the floor tapering to 1x at the top (use when confident in support). Omit to keep current'],
                'deploy_pct' => ['type' => 'integer', 'description' => 'Percent of the human-set budget the ladder commits (0 or 10-100; 0 = FLAT — no buys placed, working sells/legacy exits keep working). YOUR exposure lever: the walk-forward sweeps showed the sign of a grid week is decided by regime exposure, not spacing, and the deploy-policy sweep showed hostile regimes reward ZERO exposure. NOTE: in a hostile regime (4h trend down-family OR 4h ADX>=30, per the stored market summary) values above 25 are CAPPED to 25 at write time (regime-sweep gate — the response reports it as regime_gate); pass <=25 or 0 yourself to stay in charge of the size. Omit to keep current'],
                'override_regime_gate' => ['type' => 'boolean', 'description' => 'Bypass the hostile-regime deploy_pct cap for THIS write. Use only with a specific thesis that the sweep-average does not apply (e.g. deploying into a confirmed reversal) and say so in `reason` — the override is journaled and Telegrammed, never silent'],
                'reason' => ['type' => 'string', 'description' => 'REQUIRED. Your rationale (trend/RSI/ATR/levels) — logged and Telegrammed'],
                'run' => ['type' => ['integer', 'string'], 'description' => 'id or label; omit for the active run'],
                'dry_run' => ['type' => 'boolean', 'description' => 'Preview the levels + checks without writing'],
            ],
            'required' => ['p_low', 'p_high', 'n_levels', 'reason'],
        ];
    }

    public function requiredRight(): ?array
    {
        return ['GridRun', 'w'];
    }

    protected function run(array $args, AuthySession $session): array
    {
        $reason = trim((string) ($args['reason'] ?? ''));
        if ($reason === '') {
            throw new ToolError('reason is required — explain why this grid (trend/RSI/ATR/support/resistance)');
        }
        $run = $this->resolveRun($args);
        if ((string) ($run->getAlgo() ?: 'Grid') === 'Trend') {
            throw new ToolError('run ' . $run->getIdGridRun() . ' runs algo=Trend — grid geometry does not apply; switch algo back to Grid first');
        }
        $pLow = (string) ($args['p_low'] ?? '0');
        $pHigh = (string) ($args['p_high'] ?? '0');
        $n = (int) ($args['n_levels'] ?? 0);
        $spacing = ($args['spacing'] ?? 'Geometric') === 'Arithmetic' ? 'Arithmetic' : 'Geometric';
        $deployPct = isset($args['deploy_pct']) ? (int) $args['deploy_pct'] : (int) ($run->getDeployPct() ?? 100);
        // Sweep-validated hostile-regime cap (see RegimeGate): reads the
        // stored 4h summary; a stale/missing row fails OPEN. An explicit
        // override keeps the requested size but is reported, not silent —
        // the hostile read still lands in the journal/Telegram.
        $gate = ['pct' => $deployPct, 'capped' => false, 'why' => null];
        $overridden = false;
        $s4 = MarketStore::summaries((string) $run->getSymbol(), 3600)['4h'] ?? null;
        if ($s4 !== null && empty($s4['stale'])) {
            $gate = RegimeGate::capDeployPct($deployPct, $s4['trend'] ?? null, $s4['adx14'] ?? null);
            if ($gate['capped'] && !empty($args['override_regime_gate'])) {
                $overridden = true;
                $gate['why'] = 'regime gate OVERRIDDEN by explicit request — ' . $gate['why'];
            } else {
                $deployPct = $gate['pct'];
            }
        }
        $price = $this->price((string) $run->getSymbol());

        $config = [
            'p_low' => $pLow, 'p_high' => $pHigh, 'n_levels' => $n, 'spacing' => $spacing,
            'budget_quote' => (string) $run->getBudgetQuote(), 'fee_pct' => (string) $run->getFeePct(),
            'deploy_pct' => $deployPct,
            'min_notional' => '5',
        ];
        $errors = RiskManager::validateRunConfig($config);
        // must bracket the current price, or the grid sits idle / underwater
        if ($price !== null) {
            if (bccomp($pLow, $price, 8) >= 0 || bccomp($pHigh, $price, 8) <= 0) {
                $errors[] = sprintf('range [%s, %s] must bracket the current price %s', $pLow, $pHigh, $price);
            }
        }

        $levels = [];
        if (bccomp($pLow, '0', 8) > 0 && bccomp($pHigh, $pLow, 8) > 0 && $n >= 2) {
            $levels = GridMath::levels($pLow, $pHigh, $n, $spacing);
        }
        $preview = [
            'p_low' => $pLow, 'p_high' => $pHigh, 'n_levels' => $n, 'spacing' => $spacing,
            'deploy_pct' => $deployPct,
            'deployed_quote' => bcdiv(bcmul((string) $run->getBudgetQuote(), (string) $deployPct, 8), '100', 2),
            'levels' => $levels,
            'spacing_pct' => $levels ? GridMath::spacingPct($pLow, $pHigh, $n, $spacing) : null,
            'current_price' => $price,
        ];

        if ($errors) {
            return $this->ok(['applied' => false, 'errors' => $errors, 'preview' => $preview]);
        }
        if (!empty($args['dry_run'])) {
            $out = ['applied' => false, 'preview' => $preview, 'note' => 'dry run — nothing written'];
            if ($gate['capped'] || $overridden) {
                $out['regime_gate'] = $gate['why'];
            }
            return $this->ok($out);
        }

        // Write geometry → daemon re-anchors within a tick (legacy exits carried).
        $run->setPLow($pLow);
        $run->setPHigh($pHigh);
        $run->setNLevels($n);
        $run->setSpacing($spacing);
        $run->setDeployPct($deployPct);
        if (in_array((string) ($args['allocation'] ?? ''), ['EqualQuote', 'EqualBase', 'BottomWeighted'], true)) {
            $run->setAllocation((string) $args['allocation']);
        }
        $run->save();

        // Decision journal: scored ~6h later so the routine can consult its
        // own track record (gtbot_market → track_record) before deciding again.
        \App\Domains\Bot\DecisionScorer::record($run, 'Claude', $pLow, $pHigh, $n, $reason, $price, $deployPct);

        $msg = sprintf('grid set to [%s, %s] × %d (%s) @ %d%% deployed — %s', $pLow, $pHigh, $n, $spacing, $deployPct, $reason);
        if ($gate['capped'] || $overridden) {
            $msg .= ' [' . $gate['why'] . ']';
        }
        $e = new \App\BotEvent();
        $e->setIdGridRun((int) $run->getIdGridRun());
        $e->setLevel('Info');
        $e->setKind('refit_by_claude');
        $e->setMessage(mb_substr($msg, 0, 500));
        $e->setPayload(json_encode(['p_low' => $pLow, 'p_high' => $pHigh, 'n_levels' => $n, 'spacing' => $spacing, 'deploy_pct' => $deployPct, 'reason' => $reason], JSON_UNESCAPED_SLASHES));
        $e->save();
        if ($n2 = TelegramNotifier::fromEnv()) {
            $n2->send('🧠 ' . $msg);
        }

        $out = [
            'applied' => true,
            'preview' => $preview,
            'note' => 'geometry written; the daemon re-anchors within a tick — open buys re-ladder, working sells are carried as legacy exits.',
        ];
        if ($gate['capped'] || $overridden) {
            $out['regime_gate'] = $gate['why'];
        }
        return $this->ok($out);
    }

    private function price(string $symbol): ?string
    {
        if ($this->priceOverride !== null) {
            return $this->priceOverride;
        }
        try {
            $base = env('GTBOT_USE_TESTNET', '1') !== '0' ? 'https://testnet.binance.vision' : 'https://api.binance.com';
            return (new BinanceGateway($base, '', ''))->tickerPrice($symbol);
        } catch (\Throwable) {
            return null;
        }
    }
}
