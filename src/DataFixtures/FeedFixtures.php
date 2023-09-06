<?php

namespace App\DataFixtures;

use App\Entity\Feed;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FeedFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $feed = new Feed();
        $config = [
            'type' => 'json',
            'url' => 'https://www.aros.dk/umbraco/api/events/feed/?culture=da',
            'base' => 'https://www.aros.dk',
            'timezone' => 'Europe/Copenhagen',
            'rootPointer' => '/-',
            'dateFormat' => 'Y-m-d\TH:i:s',
            'mapping' => [
                'Id' => 'id',
                'Title' => 'title',
                'Teaser' => 'excerpt',
                'Description' => 'description',
                'DateFrom' => 'start',
                'DateTo' => 'end',
                'Url' => 'url',
                'Image' => 'image',
                'BuyTicketsLink' => 'ticketUrl',
                'Tags' => 'tags.[,]',
            ],
            'defaults' => [
                'title' => 'missing',
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
                        'latitude' => '56.153922',
                        'longitude' => '10.197522',
                    ],
                    'url' => 'http://www.aros.dk/',
                    'telephone' => '87306600',
                    'mail' => 'info@aros.dk',
                    'logo' => 'http://www.aros.dk/images/logo.png',
                ],
            ],
        ];

        $feed->setName('Test feed - Aros')
            ->setEnabled(true)
            ->setConfiguration($config)
            ->setUser($this->getReference(UserFixtures::USER));
        $manager->persist($feed);

        $feed = new Feed();
        $config = [
            'type' => 'json',
            'url' => 'https://www.aakb.dk/feeds/eventdb',
            'base' => 'https://www.aakb.dk/events',
            'timezone' => 'Europe/Copenhagen',
            'rootPointer' => '/-',
            'dateFormat' => 'Y-m-d\TH:i:sP',
            'mapping' => [
                'nid' => 'id',
                'title' => 'title',
                'lead' => 'excerpt',
                'body' => 'description',
                'date.start' => 'start',
                'date.stop' => 'end',
                'url' => 'url',
                'images.list' => 'image',
                'tickets.url' => 'ticketUrl',
                'location.postal_code' => 'location.postalCode',
                'location.locality' => 'location.city',
                'location.thoroughfare' => 'location.street',
                'location.name' => 'location.name',
                'location.coordinates.lat' => 'location.coordinates.latitude',
                'location.coordinates.lon' => 'location.coordinates.longitude',
                'location.mail' => 'location.mail',
                'tags' => 'tags',
            ],
            'defaults' => [
            ],
        ];

        $feed->setName('Test feed - Aakb')
            ->setEnabled(true)
            ->setConfiguration($config)
            ->setUser($this->getReference(UserFixtures::USER));
        $manager->persist($feed);

        $feed = new Feed();
        $config = [
            'type' => 'json',
            'url' => 'https://bora-bora.dk/wp-json/feed/v1/events',
            'base' => 'https://www.bora-bora.dk/',
            'timezone' => 'Europe/Copenhagen',
            'rootPointer' => '/-',
            'dateFormat' => 'Y-m-d\TH:i:sP',
            'mapping' => [
                'id' => 'id',
                'title' => 'title',
                'description' => 'description',
                'url' => 'url',
                'image_url' => 'image',
                'price' => 'price',
                'occurrences.*.startDate' => 'occurrences.*.start',
                'occurrences.*.endDate' => 'occurrences.*.end',
            ],
            'defaults' => [
            ],
        ];

        $feed->setName('Test feed - Bora-bora')
            ->setEnabled(true)
            ->setConfiguration($config)
            ->setUser($this->getReference(UserFixtures::USER));
        $manager->persist($feed);

        $feed = new Feed();
        $config = [
            'type' => 'json',
            'url' => 'https://www.train.dk/calenderjsonrewrite.php',
            'base' => 'https://www.bora-bora.dk/',
            'timezone' => 'Europe/Copenhagen',
            'rootPointer' => '/events/-',
            'dateFormat' => 'Y-m-d\TH:i:sP',
            'mapping' => [
                'id' => 'id',
                'name' => 'title',
                'description' => 'description',
                'url' => 'url',
                'image' => 'image',
                'ticketPriceRange' => 'price',
                'purchaseUrl' => 'ticketUrl',
                'tags' => 'tags.[]',
                'starttime' => 'start',
                'endtime' => 'end',
            ],
            'defaults' => [
            ],
        ];

        $feed->setName('Test feed - Train')
            ->setEnabled(true)
            ->setConfiguration($config)
            ->setUser($this->getReference(UserFixtures::USER));
        $manager->persist($feed);

        $feed = new Feed();
        $config = [
            'type' => 'json',
            'url' => 'https://www.hq.dk/events/kultur/',
            'base' => 'https://www.hq.dk/',
            'timezone' => 'Europe/Copenhagen',
            'rootPointer' => '/-',
            'dateFormat' => 'd.m.Y H:i',
            'mapping' => [
                'id' => 'id',
                'title' => 'title',
                'description' => 'description',
                'url' => 'url',
            ],
            'defaults' => [
            ],
        ];

        $feed->setName('Test feed - HeadQuarters')
            ->setEnabled(false)
            ->setConfiguration($config)
            ->setUser($this->getReference(UserFixtures::USER));
        $manager->persist($feed);

        // Make it stick.
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
