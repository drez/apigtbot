<?php

namespace Tests\Builder\Smoke;

use ApiGoat\Handlers\JwtBeforeHandler;
use JimTools\JwtAuth\Handlers\BeforeHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Smoke test.
 *
 * Confirms PHPUnit boots, the composer autoloader resolves the
 * handler class, and the JimTools BeforeHandlerInterface contract is
 * satisfied. No Propel, no session, no DB.
 */
final class JwtBeforeHandlerSmokeTest extends TestCase
{
    public function test_handler_implements_jimtools_before_interface(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $handler   = new JwtBeforeHandler($container);

        $this->assertInstanceOf(BeforeHandlerInterface::class, $handler);
    }
}
