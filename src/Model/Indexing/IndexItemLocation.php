<?php

namespace App\Model\Indexing;

use App\Service\Indexing\IndexItemInterface;

final class IndexItemLocation implements IndexItemInterface
{
    public function __construct(
        private readonly int $id,
        public string $name,
        public string $mail,
        public string $telephone,
        public string $url,
        public string $disabilityAccess,
        public string $image,
        public string $city,
        public string $street,
        public string $suite,
        public string $region,
        public string $postalCode,
        public string $country,
        public string $latitude,
        public string $longitude,

        public \DateTimeImmutable $created,
        public \DateTimeImmutable $updated,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function toArray(): array
    {
        // TODO: Implement toArray() method.

        return [];
    }
}
