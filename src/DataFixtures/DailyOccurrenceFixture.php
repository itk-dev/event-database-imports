<?php

namespace App\DataFixtures;

use App\Factory\DailyOccurrencesFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class DailyOccurrenceFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly DailyOccurrencesFactory $dailyOccurrencesFactory,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $event = $this->getReference(EventFixture::EVENT1);
        $this->dailyOccurrencesFactory->createOrUpdate($event);

        $event = $this->getReference(EventFixture::EVENT2);
        $this->dailyOccurrencesFactory->createOrUpdate($event);
    }

    public function getDependencies(): array
    {
        return [
            EventFixture::class,
            OccurrenceFixture::class,
        ];
    }
}
