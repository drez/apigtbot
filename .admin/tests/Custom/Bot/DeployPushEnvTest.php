<?php

namespace Tests\Custom\Bot;

use PHPUnit\Framework\TestCase;

/**
 * `gc deploy` pushes the DEPLOY_PUSH_ENV keys from the LOCAL .env over prod's,
 * local values winning. GTBOT_USE_TESTNET / GTBOT_DRY_RUN say which exchange
 * THAT HOST trades on — pushed from a dev box (where both are on) they stop
 * every Live daemon of a real fleet at boot, with its inventory still held.
 * They are host identity, set once on the host, never shipped.
 */
class DeployPushEnvTest extends TestCase
{
    public function testTheExchangeSwitchesAreNeverPushedByADeploy(): void
    {
        $root = dirname(__DIR__, 4);
        $checked = 0;
        foreach (['.env', '.env.example'] as $file) {
            if (!is_file("$root/$file")) {
                continue;
            }
            $checked++;
            preg_match('/^DEPLOY_PUSH_ENV="?([^"\n]*)"?/m', (string) file_get_contents("$root/$file"), $m);
            $keys = array_map('trim', explode(',', $m[1] ?? ''));
            $this->assertNotContains('GTBOT_USE_TESTNET', $keys, $file);
            $this->assertNotContains('GTBOT_DRY_RUN', $keys, $file);
        }
        if ($checked === 0) {
            $this->markTestSkipped('no .env beside the project');
        }
    }
}
