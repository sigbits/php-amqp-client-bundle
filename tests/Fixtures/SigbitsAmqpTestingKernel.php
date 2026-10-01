<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Tests\Fixtures;

use Sigbits\AmqpBundle\SigbitsAmqpBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

final class SigbitsAmqpTestingKernel extends Kernel
{
    /**
     * @param array<string, mixed> $bundleConfig
     */
    public function __construct(
        private readonly array $bundleConfig,
    ) {
        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        yield new SigbitsAmqpBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container): void {
            $container->loadFromExtension('sigbits_amqp', $this->bundleConfig);

            $container
                ->register(DefaultConnectionFactoryConsumer::class)
                ->setAutowired(true)
                ->setPublic(true);

            $container
                ->register(DefaultConnectionHealthCheckerConsumer::class)
                ->setAutowired(true)
                ->setPublic(true);
        });
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/sigbits-amqp-bundle/cache/' . $this->cacheKey();
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/sigbits-amqp-bundle/log/' . $this->cacheKey();
    }

    private function cacheKey(): string
    {
        return substr(sha1(serialize($this->bundleConfig)), 0, 12);
    }
}
