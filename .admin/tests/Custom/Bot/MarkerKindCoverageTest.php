<?php

namespace Tests\Custom\Bot;

use App\Domains\Dashboard\DashboardData;
use PHPUnit\Framework\TestCase;

/**
 * I3a (final-fix review): the five new Trend-seam bot_event kinds landed in
 * DashboardData::MARKER_KINDS but the chart's label map had no entries for
 * four of them — chartModel() drops any marker whose kind has no
 * MARKER_LABEL, so those events never rendered even though DashboardData
 * considered them chart-worthy. Pins the seam directly: every kind
 * DashboardData selects for the chart must have a label, so a future kind
 * can't half-land again.
 */
class MarkerKindCoverageTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
    }

    /** @return list<string> */
    private static function markerKinds(): array
    {
        return (new \ReflectionClassConstant(DashboardData::class, 'MARKER_KINDS'))->getValue();
    }

    /** @return array<string, string> */
    private static function markerLabels(): array
    {
        return (new \ReflectionClassConstant(DashboardData::class, 'MARKER_LABEL'))->getValue();
    }

    public function testEveryDashboardMarkerKindHasALabel(): void
    {
        // 'reloading' is DashboardData's own legacy alias for 'algo_update'
        // (chartMarkers() rewrites it before the kind reaches the chart) —
        // the effective kind set drops it in favor of the rewritten target.
        $effective = array_unique(array_map(
            static fn (string $k): string => $k === 'reloading' ? 'algo_update' : $k,
            self::markerKinds()
        ));
        $missing = array_diff($effective, array_keys(self::markerLabels()));
        $this->assertSame([], $missing,
            'every DashboardData::MARKER_KINDS kind must have a MARKER_LABEL entry — otherwise chartModel() silently drops it');
    }

    public function testEveryLabelIsAChartKind(): void
    {
        $effective = array_map(static fn (string $k): string => $k === 'reloading' ? 'algo_update' : $k, self::markerKinds());
        $orphans = array_diff(array_keys(self::markerLabels()), $effective);
        $this->assertSame([], $orphans, 'a label for a kind chartMarkers() never selects is dead weight');
    }

    public function testTrendSignalIsNotAChartMarkerKind(): void
    {
        // I3a: trend_signal fires up to ~6x/cycle and would flood the
        // newest-20 chartMarkers() window, evicting the lifecycle markers —
        // it stays in the event feed/Telegram (see EventLog::EMOJI) but must
        // never be chart-selected again.
        $this->assertNotContains('trend_signal', self::markerKinds());
    }
}
