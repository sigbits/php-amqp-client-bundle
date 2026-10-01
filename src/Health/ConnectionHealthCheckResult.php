<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Health;

final readonly class ConnectionHealthCheckResult
{
    /**
     * @param class-string<\Throwable>|null $exceptionClass
     */
    private function __construct(
        private bool $healthy,
        private ?string $errorMessage,
        private ?string $exceptionClass,
    ) {
    }

    public static function healthy(): self
    {
        return new self(true, null, null);
    }

    public static function unhealthy(\Throwable $exception): self
    {
        return new self(false, $exception->getMessage(), $exception::class);
    }

    public function isHealthy(): bool
    {
        return $this->healthy;
    }

    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * @return class-string<\Throwable>|null
     */
    public function exceptionClass(): ?string
    {
        return $this->exceptionClass;
    }
}
