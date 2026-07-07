<?php

namespace App\Doctrine\Extensions\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

class UTCDateTimeType extends DateTimeType
{
    private static ?\DateTimeZone $utc = null;

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof \DateTime) {
            if (self::getUtc()->getName() !== $value->getTimezone()->getName()) {
                // Clone before converting: \DateTime is mutable and the caller
                // (the entity) still owns this object — its timezone must not be
                // silently rewritten as a side effect of persisting.
                $value = (clone $value)->setTimezone(self::getUtc());
            }
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): \DateTime|\DateTimeInterface|null
    {
        if (null === $value || $value instanceof \DateTime) {
            return $value;
        }

        $converted = \DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            self::getUtc()
        );

        if (false === $converted) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeFormatString());
        }

        return $converted;
    }

    private static function getUtc(): \DateTimeZone
    {
        return self::$utc ??= new \DateTimeZone('UTC');
    }
}
