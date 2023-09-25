<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Address;
use App\Service\Geocoder;
use App\Tests\Utils\PhpUnitUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Geocoder::class)]
final class GeocoderServiceTest extends KernelTestCase
{
    /**
     * @throws \Exception
     */
    public function testBuildQuery()
    {
        $address = new Address();
        $address->setStreet('Hack Kampmanns Plads 2')
            ->setPostalCode(8000)
            ->setCity('Aarhus C');

        $query = PhpUnitUtils::callPrivateMethod(
            $this->getGeocoderService(),
            'buildQuery',
            [$address]
        );

        $this->assertEquals('Hack Kampmanns Plads 2, 8000, Aarhus C', $query);
    }

    /**
     * Helper function to bootstrap the service tested.
     *
     * @return Geocoder
     *   The service
     *
     * @throws \Exception
     */
    private function getGeocoderService(): Geocoder
    {
        self::bootKernel();
        $container = GeocoderServiceTest::getContainer();

        return $container->get(Geocoder::class);
    }
}
