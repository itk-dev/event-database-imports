<?php

namespace App\DataFixtures;

use App\Entity\Feed;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FeedFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @{@inheritdoc }
     */
    public function load(ObjectManager $manager): void
    {
        $feed = new Feed();
        $config = [
            'type' => 'json',
            'url' => 'https://www.aros.dk/umbraco/api/events/feed/?culture=da',
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
                'mail' => 'info@aros.dk',
            ],
        ];

        $feed->setName('Test feed - Aros')
            ->setEnabled(true)
            ->setLastRead(new \DateTimeImmutable())
            ->setConfiguration($config)
            ->setUser($this->getReference(UserFixtures::USER_REFERENCE));

        $manager->persist($feed);
        $manager->flush();

        $feed = new Feed();
        $config = [
            'type' => 'json',
            'url' => 'https://www.aakb.dk/feeds/eventdb',
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
                'location.coordinates.lat' => 'location.coordinates.lat',
                'location.coordinates.lon' => 'location.coordinates.long',
                'location.mail' => 'location.mail',
            ],
            'defaults' => [

            ],
        ];

        $feed->setName('Test feed - Aakb')
            ->setEnabled(true)
            ->setLastRead(new \DateTimeImmutable())
            ->setConfiguration($config)
            ->setUser($this->getReference(UserFixtures::USER_REFERENCE));

        $manager->persist($feed);
        $manager->flush();
    }

    /**
     * @{@inheritdoc }
     */
    public function getDependencies(): array
    {
        return [
          UserFixtures::class,
        ];
    }
}
