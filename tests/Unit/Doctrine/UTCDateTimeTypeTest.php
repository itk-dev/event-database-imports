<?php

declare(strict_types=1);

namespace App\Tests\Unit\Doctrine;

use App\Doctrine\Extensions\DBAL\Types\UTCDateTimeType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

/**
 * The write side of the model timezone boundary: values must be persisted as
 * UTC without mutating the caller's object.
 */
final class UTCDateTimeTypeTest extends TestCase
{
    private UTCDateTimeType $type;

    protected function setUp(): void
    {
        $this->type = new UTCDateTimeType();
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
        $value = new \DateTime('2026-07-01 14:00:00', new \DateTimeZone('Europe/Copenhagen'));

        $db = $this->type->convertToDatabaseValue($value, $this->platform());

        self::assertSame('2026-07-01 12:00:00', $db);
    }

    /**
     * Converting for storage must not mutate the caller's DateTime — the entity
     * still owns this object and must not have its timezone silently rewritten.
     */
    public function testDoesNotMutateTheGivenObject(): void
    {
        $value = new \DateTime('2026-07-01 14:00:00', new \DateTimeZone('Europe/Copenhagen'));

        $this->type->convertToDatabaseValue($value, $this->platform());

        self::assertSame('Europe/Copenhagen', $value->getTimezone()->getName());
        self::assertSame('2026-07-01 14:00:00', $value->format('Y-m-d H:i:s'));
    }

    /**
     * An already-UTC value round-trips unchanged.
     */
    public function testUtcValueIsStoredUnchanged(): void
    {
        $value = new \DateTime('2026-07-01 12:00:00', new \DateTimeZone('UTC'));

        $db = $this->type->convertToDatabaseValue($value, $this->platform());

        self::assertSame('2026-07-01 12:00:00', $db);
        self::assertSame('UTC', $value->getTimezone()->getName());
    }

    /**
     * Null passes through.
     */
    public function testNullIsPreserved(): void
    {
        self::assertNull($this->type->convertToDatabaseValue(null, $this->platform()));
    }
}
