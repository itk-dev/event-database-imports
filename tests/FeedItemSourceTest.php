<?php

declare(strict_types=1);

namespace App\Tests;

use App\Model\Feed\FeedConfiguration;
use App\Services\Feeds\FeedDefaultsMapper;
use App\Services\Feeds\Mapper\Source\FeedItemSource;
use App\Tests\Utils\FeedItemInput;
use App\Tests\Utils\PhpUnitUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(FeedItemSource::class)]
final class FeedItemSourceTest extends KernelTestCase
{
    /**
     * Test that dot operated keys are transformed into property accessor keys.
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testTransformKey(): void
    {
        $key = PhpUnitUtils::callPrivateMethod(
            $this->getFeedItemSource(new FeedConfiguration(
                type: 'json',
                url: 'https://aakb.dk/feeds',
                base: 'https://aakb.dk/',
                timezone: 'Europe/Copenhagen',
                rootPointer: '/-',
                dateFormat: 'Y-m-d\TH:i:sP'
            )),
            'transformKey',
            ['test.location.test']
        );
        $this->assertEquals('[test][location][test]', $key);
    }

    /**
     * Test simple getValue based on dot separated key.
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testGetValue(): void
    {
        $value = PhpUnitUtils::callPrivateMethod(
            $this->getFeedItemSource(new FeedConfiguration(
                type: 'json',
                url: 'https://aakb.dk/feeds',
                base: 'https://aakb.dk/',
                timezone: 'Europe/Copenhagen',
                rootPointer: '/-',
                dateFormat: 'Y-m-d\TH:i:sP'
            )),
            'getValue',
            [FeedItemInput::EXAMPLE_DATA, 'images.list']
        );
        $this->assertEquals('https://www.aakb.dk/sites/www.aakb.dk/files/list_image/event/lampeprototyper.jpg', $value);
    }

    /**
     * Test simple setValue based on dot separated key.
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testSetValue(): void
    {
        $key = 'images.list';
        $output = [];
        $config = new FeedConfiguration(
            type: 'json',
            url: 'https://aakb.dk/feeds',
            base: 'https://aakb.dk/',
            timezone: 'Europe/Copenhagen',
            rootPointer: '/-',
            dateFormat: 'Y-m-d\TH:i:sP'
        );

        // First get value.
        $value = PhpUnitUtils::callPrivateMethod(
            $this->getFeedItemSource($config),
            'getValue',
            [FeedItemInput::EXAMPLE_DATA, $key]
        );

        PhpUnitUtils::callPrivateMethod(
            $this->getFeedItemSource($config),
            'setValue',
            [&$output, $key, $value]
        );

        $this->assertEquals($value, $output['images']['list']);
    }

    /**
     * Test that get values return more than one value and in right order.
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testGetValues(): void
    {
        $values = PhpUnitUtils::callPrivateMethod(
            $this->getFeedItemSource(new FeedConfiguration(
                type: 'json',
                url: 'https://aakb.dk/feeds',
                base: 'https://aakb.dk/',
                timezone: 'Europe/Copenhagen',
                rootPointer: '/-',
                dateFormat: 'Y-m-d\TH:i:sP'
            )),
            'getValues',
            [
                FeedItemInput::EXAMPLE_DATA,
                'occurrences.*.startDate',
            ]
        );
        $this->assertCount(2, $values);
        $this->assertEquals('2023-08-30T19:30:00+02:00', $values[0]);
        $this->assertEquals('2023-08-31T20:30:00+02:00', $values[1]);
    }

    /**
     * Test that set values return more than one value and in right order.
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testSetValues(): void
    {
        $key = 'occurrences.*.startDate';
        $output = [];
        $config = new FeedConfiguration(
            type: 'json',
            url: 'https://aakb.dk/feeds',
            base: 'https://aakb.dk/',
            timezone: 'Europe/Copenhagen',
            rootPointer: '/-',
            dateFormat: 'Y-m-d\TH:i:sP'
        );

        $values = PhpUnitUtils::callPrivateMethod(
            $this->getFeedItemSource($config),
            'getValues',
            [
                FeedItemInput::EXAMPLE_DATA,
                'occurrences.*.startDate',
            ]
        );

        PhpUnitUtils::callPrivateMethod(
            $this->getFeedItemSource($config),
            'setValues',
            [&$output, $key, $values]
        );

        $this->assertCount(2, $output['occurrences']);
        $this->assertEquals($values[0], $output['occurrences'][0]['startDate']);
        $this->assertEquals($values[1], $output['occurrences'][1]['startDate']);
    }

    /**
     * Test that conversion of input to normalized output format base on provided config.
     *
     * @throws \Exception
     */
    public function testNormalize()
    {
        $feedConfig = new FeedConfiguration(
            type: 'json',
            url: 'https://aakb.dk/feeds',
            base: 'https://aakb.dk/',
            timezone: 'Europe/Copenhagen',
            rootPointer: '/-',
            dateFormat: 'Y-m-d\TH:i:sP',
            mapping: [
                'nid' => 'id',
                'title' => 'title',
                'lead' => 'excerpt',
                'body' => 'description',
                'date.start' => 'start',
                'date.stop' => 'end',
                'url' => 'url',
                'images.list' => 'image',
                'tickets.url' => 'ticketUrl',
                'location.name' => 'location.name',
                'location.coordinates.lat' => 'location.coordinates.latitude',
                'location.coordinates.lon' => 'location.coordinates.longitude',
                'location.mail' => 'location.mail',
                'tags_string' => 'tags.[,]',
                'occurrences.*.startDate' => 'occurrences.*.start',
                'occurrences.*.endDate' => 'occurrences.*.end',
            ]
        );

        $feedItemSource = $this->getFeedItemSource($feedConfig);

        $source = $feedItemSource->normalize(FeedItemInput::EXAMPLE_DATA);

        $this->assertCount(12, $source);
        $this->assertEquals([
            'id' => FeedItemInput::EXAMPLE_DATA['nid'],
            'title' => FeedItemInput::EXAMPLE_DATA['title'],
            'excerpt' => FeedItemInput::EXAMPLE_DATA['lead'],
            'description' => FeedItemInput::EXAMPLE_DATA['body'],
            'start' => FeedItemInput::EXAMPLE_DATA['date']['start'],
            'end' => FeedItemInput::EXAMPLE_DATA['date']['stop'],
            'url' => FeedItemInput::EXAMPLE_DATA['url'],
            'image' => FeedItemInput::EXAMPLE_DATA['images']['list'],
            'ticketUrl' => FeedItemInput::EXAMPLE_DATA['tickets']['url'],
            'location' => [
                'name' => FeedItemInput::EXAMPLE_DATA['location']['name'],
                'mail' => FeedItemInput::EXAMPLE_DATA['location']['mail'],
                'coordinates' => [
                    'latitude' => FeedItemInput::EXAMPLE_DATA['location']['coordinates']['lat'],
                    'longitude' => FeedItemInput::EXAMPLE_DATA['location']['coordinates']['lon'],
                ],
            ],
            'tags' => explode(',', FeedItemInput::EXAMPLE_DATA['tags_string']),
            'occurrences' => [
                [
                    'start' => FeedItemInput::EXAMPLE_DATA['occurrences'][0]['startDate'],
                    'end' => FeedItemInput::EXAMPLE_DATA['occurrences'][0]['endDate'],
                ],
                [
                    'start' => FeedItemInput::EXAMPLE_DATA['occurrences'][1]['startDate'],
                    'end' => FeedItemInput::EXAMPLE_DATA['occurrences'][1]['endDate'],
                ],
            ],
        ], $source);
    }

    /**
     * Helper function to bootstrap the service tested.
     *
     * @param feedConfiguration $configuration
     *   Default feed configuration
     *
     * @return FeedItemSource
     *   The service
     *
     * @throws \Exception
     */
    private function getFeedItemSource(FeedConfiguration $configuration): FeedItemSource
    {
        self::bootKernel();
        $container = FeedItemSourceTest::getContainer();
        $mapperService = $container->get(FeedDefaultsMapper::class);

        return new FeedItemSource($configuration, $mapperService);
    }
}
