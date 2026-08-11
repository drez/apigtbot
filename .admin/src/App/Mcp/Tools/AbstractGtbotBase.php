<?php
namespace App\Mcp\Tools;

use ApiGoat\Mcp\McpTool;

/**
 * Base for apigtbot curated (gtbot_) MCP tools. Auto-discovered by the
 * runtime ToolRegistry from src/App/Mcp/Tools/*Tool.php.
 */
abstract class AbstractGtbotBase implements McpTool
{
    /**
     * Template method: every gtbot_* call is audited to api_log, then
     * dispatched to run(). Tools implement run(), not handle(), so logging
     * can never be forgotten. (The MCP route bypasses the REST RbacMiddleware
     * that logs api_log for HTTP APIs, so we record the access here.)
     */
    final public function handle(array $args, \ApiGoat\Sessions\AuthySession $session): array
    {
        $t0 = microtime(true);
        try {
            return $this->run($args, $session);
        } finally {
            // audited after the run so the row carries server-side duration
            $args['_ms'] = (int) round((microtime(true) - $t0) * 1000);
            $this->audit($session, $args);
        }
    }

    abstract protected function run(array $args, \ApiGoat\Sessions\AuthySession $session): array;

    /** Record this MCP tool call in api_log (never throws — audit must not break a tool). */
    private function audit(\ApiGoat\Sessions\AuthySession $session, array $args): void
    {
        try {
            $rbac = \App\ApiRbacQuery::create()
                ->filterByScope(\App\ApiRbacPeer::SCOPE_PRIVATE)
                ->filterByModel($this->name())
                ->filterByAction('mcp')
                ->findOne();
            if (!$rbac) {
                $rbac = new \App\ApiRbac();
                $rbac->setModel($this->name());
                $rbac->setAction('mcp');
                $rbac->setScope(\App\ApiRbacPeer::SCOPE_PRIVATE);
                $rbac->setMethod(\App\ApiRbacPeer::METHOD_POST);
                $rbac->setRule(\App\ApiRbacPeer::RULE_ALLOW);
                $rbac->setDescription('MCP tool call (audit)');
                $rbac->setDateCreation(time()); // NOT NULL, no default
                $rbac->save();
            }
            $log = new \App\ApiLog();
            $log->setIdApiRbac($rbac->getIdApiRbac());
            $log->setIdAuthy(method_exists($session, 'getIdAuthy') ? $session->getIdAuthy() : null);
            $log->setTime(time());
            $log->setRawParameters(mb_substr(json_encode($args, JSON_UNESCAPED_SLASHES) ?: '', 0, 4000));
            $log->save();
        } catch (\Throwable $e) {
            error_log('gtbot mcp audit failed: ' . $e->getMessage());
        }
    }

    /**
     * Resolve the target run: by id, by label, or (no arg) the most recent
     * non-Done run. Throws ToolError with the available runs when ambiguous.
     */
    protected function resolveRun(array $args): \App\GridRun
    {
        $ref = $args['run'] ?? null;
        $q = \App\GridRunQuery::create();
        if ($ref !== null && $ref !== '') {
            $run = is_numeric($ref)
                ? $q->findPk((int) $ref)
                : $q->findOneByLabel((string) $ref);
            if (!$run) {
                throw new \ApiGoat\Mcp\ToolError("grid run '$ref' not found (pass id_grid_run or exact label)");
            }
            return $run;
        }
        // Prefer a RUNNING ladder over a more-recent Draft/Halted — otherwise a
        // leftover refit Draft (higher id) shadows the live run and status
        // wrongly reads "stale". Mirrors DashboardData::resolveActiveRun.
        $run = (clone $q)
            ->filterByStatus(['Live', 'Testnet', 'DryRun'], \Criteria::IN)
            ->orderByIdGridRun(\Criteria::DESC)
            ->findOne();
        if (!$run) {
            $run = $q->filterByStatus('Done', \Criteria::NOT_EQUAL)
                ->orderByIdGridRun(\Criteria::DESC)
                ->findOne();
        }
        if (!$run) {
            throw new \ApiGoat\Mcp\ToolError('no grid runs exist yet — create one in the GUI or via crm_create entity=GridRun');
        }
        return $run;
    }

    /** Enqueue a bot_command row (the only way tools affect the daemon). */
    protected function enqueue(\App\GridRun $run, string $command, string $note = ''): int
    {
        $c = new \App\BotCommand();
        $c->setIdGridRun((int) $run->getIdGridRun());
        $c->setCommand($command);
        $c->setCmdStatus('Pending');
        if ($note !== '') {
            $c->setNote(mb_substr($note, 0, 255));
        }
        $c->save();
        return (int) $c->getIdBotCommand();
    }

    /** Heartbeat freshness — a silent daemon holding inventory is an incident. */
    protected function heartbeat(\App\GridRun $run): array
    {
        $last = $run->getLastTickAt('Y-m-d H:i:s');
        $age = $last ? (time() - strtotime($last)) : null;
        return [
            'last_tick_at' => $last,
            'age_seconds' => $age,
            'stale' => $age === null || $age > 60,
        ];
    }

    /** Success result. */
    protected function ok(array $payload): array
    {
        return [
            'content' => [['type' => 'text', 'text' => json_encode($payload, JSON_UNESCAPED_SLASHES)]],
            'isError' => false,
        ];
    }

    /**
     * Grid-config args shared by preview/backtest: everything is passed as a
     * string into the bcmath money path (numbers are cast, never floated
     * through arithmetic).
     */
    protected function configSchema(): array
    {
        return [
            'p_low' => ['type' => 'string', 'description' => 'Bottom of the grid range (quote price)'],
            'p_high' => ['type' => 'string', 'description' => 'Top of the grid range'],
            'n_levels' => ['type' => 'integer', 'description' => 'Number of grid intervals (>= 2)'],
            'spacing' => ['type' => 'string', 'enum' => ['Geometric', 'Arithmetic'], 'description' => 'Level spacing (default Geometric)'],
            'allocation' => ['type' => 'string', 'enum' => ['EqualQuote', 'EqualBase'], 'description' => 'Capital allocation (default EqualQuote)'],
            'budget_quote' => ['type' => 'string', 'description' => 'Total quote budget (e.g. USDT)'],
            'fee_pct' => ['type' => 'string', 'description' => 'Per-side fee fraction, default 0.001'],
            'min_notional' => ['type' => 'string', 'description' => 'Exchange minimum order notional, default 5'],
        ];
    }

    /** @param array $args raw tool args → grid_run-shaped config array */
    protected function configFromArgs(array $args): array
    {
        return [
            'p_low' => (string) ($args['p_low'] ?? '0'),
            'p_high' => (string) ($args['p_high'] ?? '0'),
            'n_levels' => (int) ($args['n_levels'] ?? 0),
            'spacing' => (string) ($args['spacing'] ?? 'Geometric'),
            'allocation' => (string) ($args['allocation'] ?? 'EqualQuote'),
            'budget_quote' => (string) ($args['budget_quote'] ?? '0'),
            'fee_pct' => (string) ($args['fee_pct'] ?? '0.001'),
            'min_notional' => (string) ($args['min_notional'] ?? '5'),
            // risk fields with permissive defaults — preview/backtest scope
            'max_position_quote' => (string) ($args['max_position_quote'] ?? ($args['budget_quote'] ?? '0')),
            'max_order_quote' => (string) ($args['max_order_quote'] ?? ($args['budget_quote'] ?? '0')),
            'daily_loss_limit_quote' => (string) ($args['daily_loss_limit_quote'] ?? ($args['budget_quote'] ?? '0')),
            'breakout_buffer_pct' => (string) ($args['breakout_buffer_pct'] ?? '0.02'),
            'breakout_policy' => (string) ($args['breakout_policy'] ?? 'HaltAndHold'),
            'max_open_orders' => (int) ($args['max_open_orders'] ?? 60),
        ];
    }
}
