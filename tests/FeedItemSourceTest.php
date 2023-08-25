<?php

namespace App\Tests;

use App\Model\Feed\FeedConfiguration;
use App\Services\Feeds\FeedDefaultsMapperService;
use App\Services\Feeds\Mapper\Source\FeedItemSource;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class FeedItemSourceTest extends KernelTestCase
{
    // This test data is not live data, but combined data from different input sources.
    private const FEED_ITEM_EXAMPLE_DATA = [
        'nid' => '30506',
        'url' => 'https://www.aakb.dk/arrangementer/teknologi/aabent-lab-60',
        'title' => 'Åbent Lab',
        'category' => 'Teknologi',
        'tags' => [
            'laserskæring',
            'lasercut',
            '3D print',
        ],
        'tags_string' => 'laserskæring,lasercut , 3D print',
        'lead' => 'Vi holder åbent i labbet - kig forbi, hvis du er nysgerrig, har en ide eller en fil du gerne vil have skåret/printet.',
        'body' => "<p><strong>Vi holder åbent i labbet - kig forbi, hvis du er nysgerrig, har en ide eller en fil du gerne vil have skåret/printet.</strong></p> <p>På Dokk1 har vi et Maker Lab, hvor der er laserskærer og 3D printer. Vi har også forskellig hobby elektronik, som Micro:bit, Arduino og Little Bits.</p>\n<p>Åbent Lab er ikke en workshop eller undervisning; det er dig, din ide og motivation, der sætter rammen!</p>\n<p>Vi har åbent de fleste onsdage og enkelte lørdage, se mere på vores <a href='http://www.aakb.dk/makerlab'> temaside</a>.</p>\n<p>Alle er velkomne fra 12 år.</p>\n<h3> </h3>\n<h3><strong>Covid-19</strong></h3>\n<p>Deltagelse kræver gyldigt coronapas</p>\n<p><br />Vi forbeholder os ret til at foretage ændringer i de enkelte arrangementer, hvis corona-situationen skulle ændre sig.</p>\n<p>Aarhus Bibliotekerne følger sundhedsmyndighedernes anvisninger, så det er trygt at deltage i bibliotekernes arrangementer både for brugere og for ansatte.</p>",
        'date' => [
            'start' => '2021-06-09T13:30:00+00:00',
            'stop' => '2021-06-09T15:30:00+00:00',
        ],
        'images' => [
            'list' => 'https://www.aakb.dk/sites/www.aakb.dk/files/list_image/event/lampeprototyper.jpg',
            'title' => 'https://www.aakb.dk/sites/www.aakb.dk/files/title_image/event/makerlab_1.jpg',
        ],
        'location' => [
            'hint' => '',
            'thoroughfare' => 'Hack Kampmanns Plads 2',
            'postal_code' => '8000',
            'locality' => 'Aarhus',
            'mail' => 'dokk1-hovedbiblioteket@aarhus.dk',
            'phone' => '89 40 92 00 Borgerservice og Bibliotekers hovednummer',
            'name' => 'Hovedbiblioteket',
            'coordinates' => [
                'lat' => '56.1535',
                'lon' => '10.2142',
            ],
        ],
        'price' => '0',
        'tickets' => [
            'url' => 'https://tickets.online.dk/id=235123451',
        ],
        'occurrences' => [
            [
                'startDate' => '2023-08-30T19:30:00+02:00',
                'endDate' => '2023-08-30T20:45:00+02:00',
            ], [
                'startDate' => '2023-08-31T20:30:00+02:00',
                'endDate' => '2023-08-31T21:45:00+02:00',
            ],
        ],
    ];

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
                timezone: 'Europe/Copenhagen',
                rootPointer: '/-',
                dateFormat: 'Y-m-d\TH:i:sP'
            )),
            'getValue',
            [self::FEED_ITEM_EXAMPLE_DATA, 'images.list']
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
            timezone: 'Europe/Copenhagen',
            rootPointer: '/-',
            dateFormat: 'Y-m-d\TH:i:sP'
        );

        // First get value.
        $value = PhpUnitUtils::callPrivateMethod(
            $this->getFeedItemSource($config),
            'getValue',
            [self::FEED_ITEM_EXAMPLE_DATA, $key]
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
                timezone: 'Europe/Copenhagen',
                rootPointer: '/-',
                dateFormat: 'Y-m-d\TH:i:sP'
            )),
            'getValues',
            [
                self::FEED_ITEM_EXAMPLE_DATA,
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
            timezone: 'Europe/Copenhagen',
            rootPointer: '/-',
            dateFormat: 'Y-m-d\TH:i:sP'
        );

        $values = PhpUnitUtils::callPrivateMethod(
            $this->getFeedItemSource($config),
            'getValues',
            [
                self::FEED_ITEM_EXAMPLE_DATA,
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
                'location.coordinates.lat' => 'location.coordinates.lat',
                'location.coordinates.lon' => 'location.coordinates.long',
                'location.mail' => 'location.mail',
                'tags_string' => 'tags.[]',
                'occurrences.*.startDate' => 'occurrences.*.start',
                'occurrences.*.endDate' => 'occurrences.*.end',
            ]
        );

        $feedItemSource = $this->getFeedItemSource($feedConfig);

        $source = $feedItemSource->normalize(self::FEED_ITEM_EXAMPLE_DATA);

        $this->assertCount(12, $source);
        $this->assertEquals([
            'id' => self::FEED_ITEM_EXAMPLE_DATA['nid'],
            'title' => self::FEED_ITEM_EXAMPLE_DATA['title'],
            'excerpt' => self::FEED_ITEM_EXAMPLE_DATA['lead'],
            'description' => self::FEED_ITEM_EXAMPLE_DATA['body'],
            'start' => self::FEED_ITEM_EXAMPLE_DATA['date']['start'],
            'end' => self::FEED_ITEM_EXAMPLE_DATA['date']['stop'],
            'url' => self::FEED_ITEM_EXAMPLE_DATA['url'],
            'image' => self::FEED_ITEM_EXAMPLE_DATA['images']['list'],
            'ticketUrl' => self::FEED_ITEM_EXAMPLE_DATA['tickets']['url'],
            'location' => [
                'name' => self::FEED_ITEM_EXAMPLE_DATA['location']['name'],
                'mail' => self::FEED_ITEM_EXAMPLE_DATA['location']['mail'],
                'coordinates' => [
                    'lat' => self::FEED_ITEM_EXAMPLE_DATA['location']['coordinates']['lat'],
                    'long' => self::FEED_ITEM_EXAMPLE_DATA['location']['coordinates']['lon'],
                ],
            ],
            'tags' => [self::FEED_ITEM_EXAMPLE_DATA['tags_string']],
            'occurrences' => [
                [
                    'start' => self::FEED_ITEM_EXAMPLE_DATA['occurrences'][0]['startDate'],
                    'end' => self::FEED_ITEM_EXAMPLE_DATA['occurrences'][0]['endDate'],
                ],
                [
                    'start' => self::FEED_ITEM_EXAMPLE_DATA['occurrences'][1]['startDate'],
                    'end' => self::FEED_ITEM_EXAMPLE_DATA['occurrences'][1]['endDate'],
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
        $mapperService = $container->get(FeedDefaultsMapperService::class);

        return new FeedItemSource($configuration, $mapperService);
    }
}
