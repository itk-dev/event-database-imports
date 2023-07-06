<?php

namespace App\DataFixtures;

use App\Entity\Feed;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $feed = new Feed();

        $config = [
            'url' => 'https://www.aros.dk/umbraco/api/events/feed/?culture=da',
            'timeZone' => 'Europe/Copenhagen',
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
            ],
            'defaults' => [
                // @todo: defaults dont make sens yet, but are here for idea presentation.
                'name' => 'Aros',
                'url' => 'http://www.aros.dk/',
                'telephone' => '87306600',
                'logo' => 'http://www.aros.dk/images/logo.png',
                'address' => [
                    'country' => 'Danmark',
                    'city' => 'Aarhus C',
                    'postalCode' => 8000,
                    'street' => 'Aros Allé 2',
                    'region' => 'Jylland',
                    'suite' => '',
                    'latitude' => 56.153922,
                    'longitude' => 10.197522,
                ],
                'mail' => 'info@aros.dk'
            ]
        ];

        $feed->setName('Test feed - Aros')
            ->setEnabled(true)
            ->setLastRead(new \DateTimeImmutable())
            ->setConfiguration($config);

        $manager->persist($feed);
        $manager->flush();
    }
}
