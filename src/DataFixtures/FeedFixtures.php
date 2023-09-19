<?php

namespace App\DataFixtures;

use App\Entity\Feed;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class FeedFixtures extends Fixture implements DependentFixtureInterface
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
                'Teaser' => 'description',
                'Url' => 'url',
                'Image' => 'image',
                'BuyTicketsLink' => 'ticketUrl',
                'Tags' => 'tags.[,]',
                'DateFrom' => 'occurrences.*.start',
                'DateTo' => 'occurrences.*.end',
            ],
            'defaults' => [
                'title' => 'missing',
                'public' => true,
                'tags' => [
                    'Aros',
                    'Aarhus',
                ],
                'location' => [
                    'name' => 'Aros',
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
                ],
            ],
        ];

        $feed->setName('Test feed - Aros')
            ->setEnabled(true)
            ->setConfiguration($config)
            ->setUser($this->getReference(UserFixtures::USER))
            ->setOrganization($this->getReference(OrganizationFixtures::ITK));
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
                'date.start' => 'occurrences.*.start',
                'date.stop' => 'occurrences.*.end',
                'price' => 'occurrences.*.price',
            ],
            'defaults' => [
                'location' => [
                    'name' => 'Aarhus Kommunea biblioteker',
                ],
                'public' => true,
            ],
        ];

        $feed->setName('Test feed - Aakb')
            ->setEnabled(true)
            ->setConfiguration($config)
            ->setUser($this->getReference(UserFixtures::USER))
            ->setOrganization($this->getReference(OrganizationFixtures::AAKB));
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
                'occurrences.*.startDate' => 'occurrences.*.start',
                'occurrences.*.endDate' => 'occurrences.*.end',
                'price' => 'occurrences.*.price',
            ],
            'defaults' => [
                'public' => false,
                'location' => [
                    'name' => 'Aros',
                    'country' => 'Danmark',
                    'city' => 'Aarhus C',
                    'postalCode' => 8000,
                    'street' => 'Valdemarsgade 1',
                    'region' => 'Jylland',
                    'suite' => '',
                    'coordinates' => [
                        'latitude' => '56.15221473835729',
                        'longitude' => '10.19933009834337',
                    ],
                    'url' => 'https://www.bora-bora.dk/',
                    'telephone' => '86190079',
                    'mail' => 'info@bora-bora.dk',
                ],
            ],
        ];

        $feed->setName('Test feed - Bora-bora')
            ->setEnabled(true)
            ->setConfiguration($config)
            ->setUser($this->getReference(UserFixtures::USER))
            ->setOrganization($this->getReference(OrganizationFixtures::ITK));
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
                'starttime' => 'occurrences.*.start',
                'endtime' => 'occurrences.*.end',
            ],
            'defaults' => [
                'location' => [
                    'name' => 'Train Music',
                    'country' => 'Danmark',
                    'city' => 'Aarhus C',
                    'postalCode' => 8000,
                    'street' => 'Toldbodgade 6',
                    'region' => 'Jylland',
                ],
                'public' => false,
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
                'location' => [
                    'name' => 'HeadQuarters',
                ],
            ],
        ];

        $feed->setName('Test feed - HeadQuarters')
            ->setEnabled(false)
            ->setConfiguration($config)
            ->setUser($this->getReference(UserFixtures::USER))
            ->setOrganization($this->getReference(OrganizationFixtures::ITK));
        $manager->persist($feed);

        // Make it stick.
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            OrganizationFixtures::class,
        ];
    }
}
