<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Tests\Composer;

use Composer\Semver\Semver;
use PHPUnit\Framework\TestCase;

final class PhpVersionConstraintTest extends TestCase
{
    public function testRejectsPhp2shellVulnerablePatchReleases(): void
    {
        $constraint = $this->phpConstraint();

        self::assertFalse(Semver::satisfies('8.3.35', $constraint));
        self::assertFalse(Semver::satisfies('8.4.26', $constraint));
        self::assertFalse(Semver::satisfies('8.5.11', $constraint));
        self::assertFalse(Semver::satisfies('8.4.0', $constraint));
        self::assertFalse(Semver::satisfies('8.5.0', $constraint));
    }

    public function testAllowsPhp2shellFixedPatchFloors(): void
    {
        $constraint = $this->phpConstraint();

        self::assertTrue(Semver::satisfies('8.3.36', $constraint));
        self::assertTrue(Semver::satisfies('8.4.27', $constraint));
        self::assertTrue(Semver::satisfies('8.5.12', $constraint));
    }

    private function phpConstraint(): string
    {
        $composerJson = json_decode(
            (string) file_get_contents(dirname(__DIR__, 2) . '/composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        return $composerJson['require']['php'];
    }
}
