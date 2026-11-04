<?php

namespace App\Doctrine\Extensions\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\Exception\InvalidFormat;

class UTCDateTimeImmutableType extends DateTimeImmutableType
{
    private static ?\DateTimeZone $utc = null;

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof \DateTimeImmutable && $this->getUtc()->getName() !== $value->getTimezone()->getName()) {
            $value = $value->setTimezone($this->getUtc());
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?\DateTimeImmutable
    {
        if (null === $value || $value instanceof \DateTimeImmutable) {
            return $value;
        }

        $converted = \DateTimeImmutable::createFromFormat(
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
