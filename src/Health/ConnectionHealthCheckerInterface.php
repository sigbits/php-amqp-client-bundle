<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Health;

interface ConnectionHealthCheckerInterface
{
    public function check(): ConnectionHealthCheckResult;
}
