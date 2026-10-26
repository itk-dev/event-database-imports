<?php

namespace App\Doctrine\Extensions\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\Exception\InvalidFormat;

class UTCDateTimeType extends DateTimeType
{
    private static ?\DateTimeZone $utc = null;

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof \DateTime && $this->getUtc()->getName() !== $value->getTimezone()->getName()) {
            // Clone before converting: \DateTime is mutable and the caller
            // (the entity) still owns this object — its timezone must not be
            // silently rewritten as a side effect of persisting.
            $value = (clone $value)->setTimezone($this->getUtc());
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?\DateTime
    {
        if (null === $value || $value instanceof \DateTime) {
            return $value;
        }

        $converted = \DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            $this->getUtc()
        );

        if (false === $converted) {
            throw InvalidFormat::new((string) $value, static::class, $platform->getDateTimeFormatString());
        }

        return $converted;
    }

    private function getUtc(): \DateTimeZone
    {
        return self::$utc ??= new \DateTimeZone('UTC');
    }
}
