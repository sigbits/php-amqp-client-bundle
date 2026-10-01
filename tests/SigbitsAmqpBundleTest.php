<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Tests;

use PHPUnit\Framework\TestCase;
use Sigbits\AmqpBundle\SigbitsAmqpBundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SigbitsAmqpBundleTest extends TestCase
{
    public function testBundleExtendsSymfonyBundle(): void
    {
        self::assertInstanceOf(Bundle::class, new SigbitsAmqpBundle());
    }

    public function testBundleExposesExpectedContainerExtension(): void
    {
        $extension = (new SigbitsAmqpBundle())->getContainerExtension();

        self::assertInstanceOf(ExtensionInterface::class, $extension);
        self::assertSame('sigbits_amqp', $extension->getAlias());
    }
}
