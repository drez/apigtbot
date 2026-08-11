<?php

namespace Tests\Custom\Bot;

use App\Domains\Dashboard\DashboardData;
use App\Domains\Dashboard\TradeChart;
use PHPUnit\Framework\TestCase;

/**
 * I3a (final-fix review): the five new Trend-seam bot_event kinds landed in
 * DashboardData::MARKER_KINDS but TradeChart::MARKER_CLASS/MARKER_LABEL had
 * no entries for four of them — TradeChart::markers() silently drops any
 * marker whose kind isn't in MARKER_CLASS (`if (!isset(self::MARKER_CLASS
 * [$kind]) ...) continue;`), so those events never rendered even though
 * DashboardData considered them chart-worthy. Pins the seam directly: every
 * kind DashboardData selects for the chart must have a TradeChart marker
 * class, so a future kind can't half-land again.
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
    private static function markerClasses(): array
    {
        return (new \ReflectionClassConstant(TradeChart::class, 'MARKER_CLASS'))->getValue();
    }

    /** @return array<string, string> */
    private static function markerLabels(): array
    {
        return (new \ReflectionClassConstant(TradeChart::class, 'MARKER_LABEL'))->getValue();
    }

    public function testEveryDashboardMarkerKindHasATradeChartClass(): void
    {
        $kinds = self::markerKinds();
        $classes = self::markerClasses();

        // 'reloading' is DashboardData's own legacy alias for 'algo_update'
        // (chartMarkers() rewrites it before the kind ever reaches
        // TradeChart) — the effective kind set drops it in favor of the
        // rewritten target.
        $effective = array_unique(array_map(
            static fn (string $k): string => $k === 'reloading' ? 'algo_update' : $k,
            $kinds
        ));

        $missing = array_diff($effective, array_keys($classes));
        $this->assertSame([], $missing,
            'every DashboardData::MARKER_KINDS kind must have a TradeChart::MARKER_CLASS entry — otherwise markers() silently drops it');
    }

    public function testEveryMarkerClassEntryHasALabel(): void
    {
        // the legend renderer indexes MARKER_LABEL by the same kind
        // (`self::MARKER_LABEL[$kind]`) with no isset guard — a class entry
        // without a label would fatal the legend, not just silently drop.
        $missing = array_diff(array_keys(self::markerClasses()), array_keys(self::markerLabels()));
        $this->assertSame([], $missing, 'every TradeChart::MARKER_CLASS kind must have a MARKER_LABEL entry');
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
