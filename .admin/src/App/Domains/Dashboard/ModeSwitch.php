<?php

namespace App\Domains\Dashboard;

use App\BotCommand;
use App\GridRunQuery;

/**
 * The system trades ONE shared wallet, so simulated-vs-real is a SYSTEM mode:
 * mixed modes would double-commit the same capital. flipAll() is the only
 * writer the dashboard uses — every non-Done run flips together and gets a
 * Reload command so its daemon reboots into the new mode within ~a minute.
 */
class ModeSwitch
{
    public static function systemMode(): string
    {
        $modes = [];
        foreach (GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->find() as $r) {
            $modes[(int) (bool) $r->getSimulated()] = true;
        }
        if ($modes === []) {
            return 'none';
        }
        if (count($modes) > 1) {
            return 'mixed';
        }
        return isset($modes[1]) ? 'simulated' : 'real';
    }

    /** @return array{runs:int, commands:int} */
    public static function flipAll(bool $simulated): array
    {
        $runs = 0;
        $commands = 0;
        foreach (GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->find() as $r) {
            $r->setSimulated($simulated);
            $r->save();
            $runs++;
            $cmd = new BotCommand();
            $cmd->setIdGridRun((int) $r->getIdGridRun());
            $cmd->setCommand('Reload');
            $cmd->setCmdStatus('Pending');
            $cmd->setNote('dashboard-mode');
            $cmd->save();
            $commands++;
        }
        return ['runs' => $runs, 'commands' => $commands];
    }
}
