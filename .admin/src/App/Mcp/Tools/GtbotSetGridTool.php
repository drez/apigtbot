<?php
namespace App\Mcp\Tools;

use ApiGoat\Mcp\ToolError;
use ApiGoat\Sessions\AuthySession;
use App\Domains\Bot\ClampTrail;
use App\Domains\Bot\GridMath;
use App\Domains\Bot\MarketStore;
use App\Domains\Bot\MechanicalRefit;
use App\Domains\Bot\RegimeGate;
use App\Domains\Bot\RiskManager;
use App\Domains\Bot\RoutineBrief;
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
        return 'Re-fit the run\'s grid (p_low, p_high, n_levels, spacing, deploy_pct) — applies within a tick, '
            . 'held inventory rides out as legacy exits. Requires reason (journaled + Telegrammed). Rejects a range '
            . 'that does not bracket price or clear the fee floor; caps deploy_pct at 25 in a hostile 4h regime '
            . '(override_regime_gate:true + thesis). dry_run:true previews. '
            . 'MECHANICAL MODE (config gtbot_grid_refit_mode=mechanical): the cron owns p_low/p_high/n_levels/spacing — '
            . 'pass the run\'s CURRENT geometry unchanged and set deploy_pct only; a geometry change is refused.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'p_low' => ['type' => 'string', 'description' => 'Grid floor (must be below current price). In mechanical mode (config gtbot_grid_refit_mode=mechanical) the cron owns the range: pass the run\'s current p_low unchanged.'],
                'p_high' => ['type' => 'string', 'description' => 'Grid ceiling (must be above current price). In mechanical mode pass the run\'s current p_high unchanged.'],
                'n_levels' => ['type' => 'integer', 'description' => 'Number of grid intervals (≥ 2). In mechanical mode pass the run\'s current n_levels unchanged.'],
                'spacing' => ['type' => 'string', 'enum' => ['Geometric', 'Arithmetic'], 'description' => 'default Geometric'],
                'allocation' => ['type' => 'string', 'enum' => ['EqualQuote', 'EqualBase', 'BottomWeighted'], 'description' => 'ladder shape; BottomWeighted = 2x at the floor tapering to 1x (confident support). Omit to keep current'],
                'deploy_pct' => ['type' => 'integer', 'description' => 'Percent of the slice the ladder commits: 0 (FLAT — no buys, sells/legacy exits keep working) or 10-100. In a hostile 4h regime (down-family OR ADX>=30) values above 25 are capped to 25 (reported as regime_gate). Omit to keep current'],
                'override_regime_gate' => ['type' => 'boolean', 'description' => 'Bypass the hostile-regime cap for THIS write — only with a specific thesis stated in reason (journaled + Telegrammed)'],
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
        // Mechanical geometry (gtbot_grid_refit_mode): the cron owns
        // p_low/p_high/n_levels/spacing — the routine may only move
        // deploy_pct. A call that changes the ladder is refused so the
        // prompt cannot fight the machine; a deploy-only call passes with
        // the current geometry, even when price has left the range (the
        // cron's next pass re-anchors it).
        $mechanicalDeployOnly = false;
        if (MechanicalRefit::isMechanical()) {
            $same = static fn (string $a, string $b): bool => bccomp($a, $b, 8) === 0;
            $unchanged = $same($pLow, (string) $run->getPLow()) && $same($pHigh, (string) $run->getPHigh())
                && $n === (int) $run->getNLevels() && $spacing === (string) $run->getSpacing();
            if (!$unchanged) {
                throw new ToolError(sprintf(
                    'grid geometry is MECHANICAL (config %s): the cron re-anchors ±%s%% × %d every %dh or when price leaves the range — pass the current geometry unchanged ([%s, %s] × %d %s) and set deploy_pct only',
                    MechanicalRefit::CONFIG_MODE,
                    bcmul(MechanicalRefit::halfWidth(), '100', 1),
                    MechanicalRefit::levels(),
                    MechanicalRefit::refitHours(),
                    rtrim(rtrim((string) $run->getPLow(), '0'), '.'),
                    rtrim(rtrim((string) $run->getPHigh(), '0'), '.'),
                    (int) $run->getNLevels(),
                    (string) $run->getSpacing()
                ));
            }
            $mechanicalDeployOnly = true;
        }
        // Sweep-validated hostile-regime cap (see RegimeGate): reads the
        // stored 4h summary; a stale/missing row fails OPEN. An explicit
        // override keeps the requested size but is reported, not silent —
        // the hostile read still lands in the journal/Telegram.
        // Decision receipt (2026-08-23): what was asked, what each gate did
        // to it, and the brief the caller saw — persisted on bot_decision so
        // requested-vs-applied and "did the routine follow the candidate"
        // are reconstructible from the row alone.
        $requested = [
            'p_low' => $pLow, 'p_high' => $pHigh, 'n_levels' => $n, 'spacing' => $spacing,
            'deploy_pct' => $deployPct, 'override_regime_gate' => !empty($args['override_regime_gate']),
        ];
        $trail = new ClampTrail();
        $gate = ['pct' => $deployPct, 'capped' => false, 'why' => null];
        $overridden = false;
        $summaries = MarketStore::summaries((string) $run->getSymbol(), 3600);
        $s4 = $summaries['4h'] ?? null;
        if ($s4 !== null && empty($s4['stale'])) {
            $gate = RegimeGate::capDeployPct($deployPct, $s4['trend'] ?? null, $s4['adx14'] ?? null);
            if ($gate['capped'] && !empty($args['override_regime_gate'])) {
                $overridden = true;
                $gate['why'] = 'regime gate OVERRIDDEN by explicit request — ' . $gate['why'];
            } else {
                $trail->add('regime_gate', $deployPct, (int) $gate['pct'], $gate['why']);
                $deployPct = $gate['pct'];
            }
        }
        $price = $this->price((string) $run->getSymbol(), (bool) $run->getSimulated());
        $brief = $this->briefSnapshot($run, $summaries, $price, $deployPct);
        $candidateDelta = ClampTrail::candidateDelta($brief['candidate'] ?? null, $pLow, $pHigh, $n);

        $config = [
            'p_low' => $pLow, 'p_high' => $pHigh, 'n_levels' => $n, 'spacing' => $spacing,
            'budget_quote' => (string) $run->getBudgetQuote(), 'fee_pct' => (string) $run->getFeePct(),
            'deploy_pct' => $deployPct,
            'min_notional' => '5',
        ];
        $errors = RiskManager::validateRunConfig($config);
        // must bracket the current price, or the grid sits idle / underwater
        if ($price !== null && !$mechanicalDeployOnly) {
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
        // Deploy-only mechanical calls are journaled too — deploy is the
        // routine's one remaining lever there, and the cron's 72h clock
        // filters by source=Cron so a stamped Claude row cannot restart it.
        \App\Domains\Bot\DecisionScorer::record($run, 'Claude', $pLow, $pHigh, $n, $reason, $price, $deployPct, [
            'requested' => $requested, 'clamps' => $trail, 'brief' => $brief, 'candidate_delta' => $candidateDelta,
        ]);

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
        $out['receipt'] = ['clamps' => $trail->all(), 'candidate_delta' => $candidateDelta];
        return $this->ok($out);
    }

    /**
     * The per-run slice of gtbot_routine_brief as of this write (digest,
     * regime, deploy band, deterministic candidate) — the CycleRecord-style
     * "what the decider saw". Best-effort: any failure yields a partial
     * snapshot rather than blocking the write.
     */
    private function briefSnapshot(\App\GridRun $run, array $summaries, ?string $price, int $deployPct): array
    {
        $pf = $price !== null ? (float) $price : null;
        $profile = (string) ($run->getProfile() ?: 'Balanced');
        $out = ['at' => gmdate('Y-m-d H:i') . 'Z', 'profile' => $profile];
        try {
            $out['signal'] = RoutineBrief::digest($summaries, $price);
            $regime = RoutineBrief::regime($summaries, $pf);
            $out['regime'] = $regime;
            $out['deploy_band'] = RoutineBrief::deployBand($profile, $regime, $deployPct);
            $out['candidate'] = RoutineBrief::candidate(
                MarketStore::candles((string) $run->getSymbol(), '4h'),
                (string) $run->getFeePct(), (string) $run->getBudgetQuote(), $pf, $profile, $regime, $deployPct
            );
        } catch (\Throwable $e) {
            $out['error'] = $e->getMessage();
        }
        return $out;
    }

    private function price(string $symbol, bool $simulated): ?string
    {
        // the tape THIS run trades (RunFactory::marketBase), not whatever
        // GTBOT_USE_TESTNET defaults to on this host
        return $this->priceOverride ?? \App\Domains\Bot\RunFactory::ticker($symbol, $simulated);
    }
}
