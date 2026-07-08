<?php

declare(strict_types=1);

namespace App\Tests\Unit\Doctrine;

use App\Doctrine\Extensions\DBAL\Types\UTCDateTimeImmutableType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

/**
 * Characterizes the UTC immutable datetime type on both the write (store as
 * UTC) and read (hydrate as UTC) paths. These conversions are the parts most
 * affected by the Doctrine DBAL 4 upgrade, so this pins current behavior first.
 */
final class UTCDateTimeImmutableTypeTest extends TestCase
{
    private UTCDateTimeImmutableType $type;

    protected function setUp(): void
    {
        $this->type = new UTCDateTimeImmutableType();
    }

    private function platform(): AbstractPlatform
    {
        $platform = $this->createStub(AbstractPlatform::class);
        $platform->method('getDateTimeFormatString')->willReturn('Y-m-d H:i:s');

        return $platform;
    }

    /**
     * A non-UTC value is stored as its UTC equivalent.
     */
    public function testConvertsNonUtcValueToUtcForStorage(): void
    {
        $value = new \DateTimeImmutable('2026-07-01 14:00:00', new \DateTimeZone('Europe/Copenhagen'));

        $db = $this->type->convertToDatabaseValue($value, $this->platform());

        self::assertSame('2026-07-01 12:00:00', $db);
    }

    /**
     * Immutable values are never mutated by the conversion — the caller keeps
     * its original object and timezone.
     */
    public function testDoesNotMutateTheGivenObject(): void
    {
        $value = new \DateTimeImmutable('2026-07-01 14:00:00', new \DateTimeZone('Europe/Copenhagen'));

        $this->type->convertToDatabaseValue($value, $this->platform());

        self::assertSame('Europe/Copenhagen', $value->getTimezone()->getName());
        self::assertSame('2026-07-01 14:00:00', $value->format('Y-m-d H:i:s'));
    }

    /**
     * An already-UTC value round-trips unchanged.
     */
    public function testUtcValueIsStoredUnchanged(): void
    {
        $value = new \DateTimeImmutable('2026-07-01 12:00:00', new \DateTimeZone('UTC'));

        self::assertSame('2026-07-01 12:00:00', $this->type->convertToDatabaseValue($value, $this->platform()));
    }

    /**
     * Null passes through on both paths.
     */
    public function testNullIsPreserved(): void
    {
        self::assertNull($this->type->convertToDatabaseValue(null, $this->platform()));
        self::assertNull($this->type->convertToPHPValue(null, $this->platform()));
    }

    /**
     * A stored string is hydrated as a UTC-zoned DateTimeImmutable.
     */
    public function testConvertToPhpValueHydratesAsUtc(): void
    {
        $value = $this->type->convertToPHPValue('2026-07-01 12:00:00', $this->platform());

        self::assertInstanceOf(\DateTimeImmutable::class, $value);
        self::assertSame('UTC', $value->getTimezone()->getName());
        self::assertSame('2026-07-01 12:00:00', $value->format('Y-m-d H:i:s'));
    }

    /**
     * An existing DateTimeImmutable passes through unchanged on read.
     */
    public function testConvertToPhpValuePassesThroughExistingObject(): void
    {
        $existing = new \DateTimeImmutable('2026-07-01 12:00:00', new \DateTimeZone('UTC'));

        self::assertSame($existing, $this->type->convertToPHPValue($existing, $this->platform()));
    }
}
