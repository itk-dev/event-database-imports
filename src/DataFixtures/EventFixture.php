<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Feed;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Build event fixtures that do not come from feeds to be able to filter and edit events in the administrative UI, as
 * event from feed fixtures should not be edible in the UI.
 */
final class EventFixture extends Fixture implements DependentFixtureInterface
{
    public const EVENT1 = 'event1-itkdev';
    public const EVENT2 = 'event2-itkdev';

    public function load(ObjectManager $manager): void
    {
        $event = new Event();
        $event->setOrganization($this->getReference(OrganizationFixtures::ITK))
            ->addPartner($this->getReference(OrganizationFixtures::AAKB))
            ->addPartner($this->getReference(OrganizationFixtures::DOKK1))
            ->setTitle('ITKDev test event 1')
            ->setExcerpt('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.')
            ->setDescription('<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Quis blandit turpis cursus in. Nisl suscipit adipiscing bibendum est ultricies integer quis auctor. Diam donec adipiscing tristique risus nec feugiat. Tincidunt eget nullam non nisi est. Consectetur a erat nam at lectus urna. Vulputate sapien nec sagittis aliquam. Luctus venenatis lectus magna fringilla. Sit amet consectetur adipiscing elit duis tristique. Bibendum enim facilisis gravida neque convallis a.</p><p>Cursus eget nunc scelerisque viverra mauris in aliquam sem. Euismod elementum nisi quis eleifend quam adipiscing vitae proin sagittis.<br />Sodales ut eu sem integer vitae justo eget. Lacus sed viverra tellus in.</p>')
            ->setUrl('https://itk.aarhus.dk/nyheder/projektnyheder/robotternes-bidrag-til-den-groenne-omstilling/')
            ->setTicketUrl('https://www.aakb.dk/arrangementer/boern/skak-nu-eller-aldrig-18')
            ->setPublic(true)
            ->setLocation($this->getReference(LocationFixture::ITKDEV))
            ->addTag($this->getReference(TagsFixtures::AROS))
            ->addTag($this->getReference(TagsFixtures::RACE))
            ->addTag($this->getReference(TagsFixtures::ITKDEV))
            ->setImage($this->getReference(ImagesFixtures::ITK))
            ->setEditable(true)
            ->setHash('4936efebda146f6775fb7e429d884fef');
        $manager->persist($event);
        $this->addReference(self::EVENT2, $event);

        $event = new Event();
        $event->setOrganization($this->getReference(OrganizationFixtures::ITK))
            ->setTitle('ITKDev test event 2')
            ->setExcerpt('Quis vel eros donec ac odio tempor orci dapibus ultrices. Velit dignissim sodales ut eu sem integer. Massa tincidunt dui ut ornare lectus sit amet est placerat.')
            ->setDescription('<p>Quam vulputate dignissim suspendisse in est ante. Libero enim sed faucibus turpis in eu mi bibendum. Gravida rutrum quisque non tellus orci. Eget nunc lobortis mattis aliquam faucibus purus in massa. Tortor posuere ac ut consequat semper viverra nam. Sapien et ligula ullamcorper malesuada. Et molestie ac feugiat sed lectus vestibulum mattis ullamcorper. At consectetur lorem donec massa sapien faucibus et. Ut consequat semper viverra nam libero. Hendrerit gravida rutrum quisque non tellus orci ac.</p>')
            ->setUrl('https://itk.aarhus.dk/nyheder/')
            ->setTicketUrl('https://www.aakb.dk/arrangementer/born/kreavaerksted-monsterboger-0')
            ->setPublic(true)
            ->setLocation($this->getReference(LocationFixture::ITKDEV))
            ->addTag($this->getReference(TagsFixtures::CONCERT))
            ->addTag($this->getReference(TagsFixtures::AROS))
            ->setEditable(true)
            ->setImage($this->getReference(ImagesFixtures::AAK))
            ->setHash('16d48c26d38f6d59b3d081e596b4d0e8');
        $manager->persist($event);
        $this->addReference(self::EVENT1, $event);

        // Make it stick.
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            OrganizationFixtures::class,
            LocationFixture::class,
            TagsFixtures::class,
            ImagesFixtures::class,
        ];
    }
}
