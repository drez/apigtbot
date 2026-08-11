<?php

namespace Tests\Custom\Bot;

use App\ApiLog;
use App\ApiRbac;
use App\ApiRbacPeer;
use App\Domains\Bot\RoutineLiveness;
use PHPUnit\Framework\TestCase;

/**
 * Detecting a dead refit routine: "no refit lately" is the wrong signal (a
 * healthy routine holds and writes nothing), but every routine pass calls
 * gtbot_* MCP tools, and every call lands in api_log. Prolonged silence
 * there = the routine/MCP link is down.
 */
class RoutineLivenessTest extends TestCase
{
    private static bool $booted = false;

    public static function setUpBeforeClass(): void
    {
        if (self::$booted) {
            return;
        }
        $admin = dirname(__DIR__, 3);
        require_once $admin . '/vendor/autoload.php';
        (new \Ahc\Env\Loader())->load($admin . '/.env');
        if (!defined('_AUTH_VAR')) {
            require $admin . '/config/Built/config.php';
        }
        if (!\Propel::isInit()) {
            require $admin . '/config/Built/propel.php';
        }
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION[_AUTH_VAR] = new \ApiGoat\Sessions\AuthySession();
        self::$booted = true;
    }

    protected function setUp(): void
    {
        \Propel::getConnection()->beginTransaction();
        // isolate from real prod/dev traffic rows in api_log
        \Propel::getConnection()->exec('DELETE FROM api_log');
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function logCall(string $model, string $action, int $ts): void
    {
        $rbac = \App\ApiRbacQuery::create()
            ->filterByScope(ApiRbacPeer::SCOPE_PRIVATE)
            ->filterByModel($model)
            ->filterByAction($action)
            ->findOne();
        if (!$rbac) {
            $rbac = new ApiRbac();
            $rbac->setModel($model);
            $rbac->setAction($action);
            $rbac->setScope(ApiRbacPeer::SCOPE_PRIVATE);
            $rbac->setMethod(ApiRbacPeer::METHOD_POST);
            $rbac->setRule(ApiRbacPeer::RULE_ALLOW);
            $rbac->setDateCreation(time());
            $rbac->save();
        }
        $log = new ApiLog();
        $log->setIdApiRbac($rbac->getIdApiRbac());
        $log->setTime($ts);
        $log->setRawParameters('{}');
        $log->save();
    }

    public function testRecentGtbotCallMeansAlive(): void
    {
        $this->logCall('gtbot_status', 'mcp', time() - 300);
        $this->assertFalse(RoutineLiveness::isSilent(7200));
    }

    public function testOldGtbotCallMeansSilent(): void
    {
        $this->logCall('gtbot_market', 'mcp', time() - 8000);
        $this->assertTrue(RoutineLiveness::isSilent(7200));
    }

    public function testNonGtbotTrafficDoesNotCountAsRoutineActivity(): void
    {
        $this->logCall('GridRun', 'list', time() - 60); // REST/CRM noise
        $this->logCall('gtbot_market', 'mcp', time() - 8000);
        $this->assertTrue(RoutineLiveness::isSilent(7200), 'only gtbot_* MCP calls prove the routine is alive');
    }

    public function testNoActivityAtAllIsSilent(): void
    {
        $this->assertTrue(RoutineLiveness::isSilent(7200), 'an active run with no routine EVER is exactly the unsafe setup');
    }
}
