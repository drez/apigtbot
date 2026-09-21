<?php

namespace Tests\Custom\Bot;

use PHPUnit\Framework\TestCase;

/**
 * The admin's index.js overrides window.confirm with an async gcScreens modal
 * that returns undefined — a native-style `if(!confirm(...))` guard therefore
 * exits silently before sending anything (the rb1–rb4 "button does nothing"
 * bug). The restart handler must use the gcScreens Promise API instead.
 */
class DashboardRestartJsTest extends TestCase
{
    private function restartJs(): string
    {
        $view = new \App\Domains\Dashboard\View();
        $m = new \ReflectionMethod($view, 'restartButtonJs');
        return $m->invoke($view, 'https://example.test/');
    }

    public function testBudgetEditPostsThroughDashboardBudgetRoute(): void
    {
        $js = $this->restartJs();
        $this->assertStringContainsString("Dashboard/budget", $js);
        $this->assertStringContainsString('dash-budget-form', $js);
        // slices used to wait for the next refit; since the allocator landed
        // (2026-09-19) a budget change reallocates immediately, and the confirm
        // has to say so — and has to promise nothing is sold
        $this->assertStringContainsString('reallocated straight away', $js);
        $this->assertStringContainsString('nothing is ever sold', $js);
        $this->assertStringContainsString('reload', strtolower($js));
    }

    public function testConfirmGoesThroughGcScreensPromise(): void
    {
        $js = $this->restartJs();
        $this->assertStringContainsString('gcScreens.confirm', $js);
        $this->assertStringContainsString('.then(', $js);
    }

    public function testNoNativeStyleBooleanConfirmGuard(): void
    {
        // strip // comment lines — only executable code matters here
        $js = preg_replace('#^\s*//.*$#m', '', $this->restartJs());
        $this->assertDoesNotMatchRegularExpression(
            '/if\s*\(\s*!\s*confirm\(/',
            $js,
            'native-style confirm() guard is silently broken by the index.js window.confirm override'
        );
        // The only tolerated window.confirm use is the no-gcScreens fallback,
        // which must require an explicit native boolean true.
        $this->assertStringContainsString('window.confirm(msg)===true', $js);
    }
}
