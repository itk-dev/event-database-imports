<?php

namespace App\Controller\Admin;

use App\Entity\Address;
use App\Entity\Event;
use App\Entity\Feed;
use App\Entity\Image;
use App\Entity\Location;
use App\Entity\Occurrence;
use App\Entity\Organization;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Vocabulary;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;

class DashboardController extends AbstractDashboardController
{
    // Default date time format used in the UI.
    //
    // @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
    public const DATETIME_FORMAT = 'dd-MM-Y HH:mm:ss';

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($adminUrlGenerator->setController(EventCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Event database');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.events'), 'fa fa-calendar', Event::class);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.occurrences'), 'fa fa-repeat', Occurrence::class);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.location'), 'fa fa-location-dot', Location::class);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.address'), 'fa fa-address-book', Address::class);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.image'), 'fa fa-image', Image::class);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.feeds'), 'fa fa-rss', Feed::class);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.tags'), 'fa fa-tags', Tag::class);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.vocabularies'), 'fa fa-book', Vocabulary::class);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.organizations'), 'fa fa-sitemap', Organization::class);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.users'), 'fa fa-user', User::class);
    }
}
