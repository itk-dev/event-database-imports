<?php

declare(strict_types=1);

namespace App\Tests;

use App\Service\Time;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Time::class)]
final class TimeServiceTest extends KernelTestCase
{
    /**
     * @throws \Exception
     */
    public function testCreatePath()
    {
        $time = $this->getTimeService();

        $start = new \DateTimeImmutable('2023-09-23T10:00:00+02:00');
        $end = new \DateTimeImmutable('2023-09-30T12:30:00+02:00');

        $times = $time->getInterval($start, $end);

        $this->assertCount(8, $times);
        $this->assertEquals($start, reset($times));
        $this->assertEquals($end, end($times));
    }

    /**
     * Helper function to bootstrap the service tested.
     *
     * @return Time
     *   The service
     *
     * @throws \Exception
     */
    private function getTimeService(): Time
    {
        self::bootKernel();
        $container = TimeServiceTest::getContainer();

        return $container->get(Time::class);
    }
}
