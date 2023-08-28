<?php

declare(strict_types=1);

namespace App\Tests;

use App\Model\Feed\FeedConfiguration;
use App\Services\Feeds\FeedDefaultsMapperService;
use App\Services\Feeds\Mapper\Source\FeedItemSource;
use App\Tests\Utils\FeedItemInput;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(FeedItemSource::class)]
final class FeedDefaultMapperTest extends KernelTestCase
{
    /**
     * @throws \Exception
     */
    public function testApply(): void
    {
        $config = new FeedConfiguration(
            type: 'json',
            url: 'https://aakb.dk/feeds',
            timezone: 'Europe/Copenhagen',
            rootPointer: '/-',
            dateFormat: 'Y-m-d\TH:i:sP',
            mapping: [
                'nid' => 'id',
                'lead' => 'excerpt',
                'url' => 'url',
                'images.list' => 'image',
                'tickets.url' => 'ticketUrl',
                'location.name' => 'location.name',
                'location.coordinates.lat' => 'location.coordinates.lat',
                'location.coordinates.lon' => 'location.coordinates.long',
                'location.mail' => 'location.mail',
                'tags_string' => 'tags.[]',
            ],
            defaults: [
                'title' => 'missing',
                'url' => 'https://should_not_be_overriden.dk/',
                'tags' => [
                    'Aros',
                    'Aarhus',
                ],
                'location' => [
                    'country' => 'Danmark',
                    'city' => 'Aarhus C',
                    'postalCode' => 8000,
                    'street' => 'Aros Allé 2',
                    'region' => 'Jylland',
                    'suite' => '',
                    'coordinates' => [
                        'lat' => '56.1234',
                        'long' => '10.4321',
                    ],
                    'url' => 'http://www.aros.dk/',
                    'telephone' => '87306600',
                    'mail' => 'info@itkdev2023.dk',
                    'logo' => 'http://www.aros.dk/images/logo.png',
                ],
            ],
        );

        $input = [
            'id' => FeedItemInput::EXAMPLE_DATA['nid'],
            'excerpt' => FeedItemInput::EXAMPLE_DATA['lead'],
            'url' => FeedItemInput::EXAMPLE_DATA['url'],
            'image' => FeedItemInput::EXAMPLE_DATA['images']['list'],
            'ticketUrl' => FeedItemInput::EXAMPLE_DATA['tickets']['url'],
            'location' => [
                'name' => FeedItemInput::EXAMPLE_DATA['location']['name'],
                'coordinates' => [
                    'lat' => FeedItemInput::EXAMPLE_DATA['location']['coordinates']['lat'],
                    'long' => FeedItemInput::EXAMPLE_DATA['location']['coordinates']['lon'],
                ],
            ],
            'tags' => FeedItemInput::EXAMPLE_DATA['tags'],
        ];

        $defaultMapper = $this->getFeedDefaultMapper();
        $output = $defaultMapper->apply($input, $config);

        $this->assertEquals($config->defaults['title'], $output['title']);
        $this->assertEquals($config->defaults['location']['mail'], $output['location']['mail']);
        $this->assertEquals($config->defaults['location']['telephone'], $output['location']['telephone']);

        // Defaults should now override real values.
        $this->assertNotEquals($config->defaults['url'], $output['url']);
        $this->assertNotEquals($config->defaults['location']['coordinates'], $output['location']['coordinates']);

        // Test array data merge with defaults.
        $this->assertCount(12, $output['location']);
        $this->assertCount(5, $output['tags']);
    }

    /**
     * Helper function to bootstrap the service tested.
     *
     * @return FeedDefaultsMapperService
     *   The service
     *
     * @throws \Exception
     */
    private function getFeedDefaultMapper(): FeedDefaultsMapperService
    {
        self::bootKernel();
        $container = FeedDefaultMapperTest::getContainer();

        return $container->get(FeedDefaultsMapperService::class);
    }
}
