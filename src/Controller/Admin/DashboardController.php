<?php

namespace App\Controller\Admin;

use App\Entity\Address;
use App\Entity\Event;
use App\Entity\Feed;
use App\Entity\Location;
use App\Entity\Organization;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Vocabulary;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;

class DashboardController extends AbstractDashboardController
{
    public const string MODEL_TIMEZONE = 'UTC';
    public const string VIEW_TIMEZONE = 'Europe/Copenhagen';

    // Default date time format used in the UI.
    //
    // @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
    public const string DATETIME_FORMAT = 'dd-MM-Y HH:mm:ss';
    public const string TIME_FORMAT = 'HH:mm:ss';
    public const string DATE_FORMAT = 'dd-MM-Y';

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
        // My Content
        yield MenuItem::section('admin.label.my_content');
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.events'), 'fa fa-calendar', Event::class)
            ->setController(MyEventCrudController::class)
            ->setPermission(UserRoles::ROLE_ORGANIZATION_EDITOR->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.organizations'), 'fa fa-sitemap', Organization::class)
            ->setController(MyOrganizationCrudController::class)
            ->setPermission(UserRoles::ROLE_ORGANIZATION_EDITOR->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.location'), 'fa fa-location-dot', Location::class)
            ->setPermission(UserRoles::ROLE_ORGANIZATION_EDITOR->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.address'), 'fa fa-address-book', Address::class)
            ->setPermission(UserRoles::ROLE_ORGANIZATION_EDITOR->value);

        // All Content
        yield MenuItem::section('admin.label.all_content');
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.events'), 'fa fa-calendar', Event::class)
            ->setController(EventCrudController::class)
            ->setPermission(UserRoles::ROLE_USER->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.organizations'), 'fa fa-sitemap', Organization::class)
            ->setPermission(UserRoles::ROLE_USER->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.location'), 'fa fa-location-dot', Location::class)
            ->setPermission(UserRoles::ROLE_USER->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.address'), 'fa fa-address-book', Address::class)
            ->setPermission(UserRoles::ROLE_USER->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.feeds'), 'fa fa-rss', Feed::class)
            ->setPermission(UserRoles::ROLE_USER->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.tags'), 'fa fa-tags', Tag::class)
            ->setPermission(UserRoles::ROLE_USER->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.vocabularies'), 'fa fa-book', Vocabulary::class)
            ->setPermission(UserRoles::ROLE_ADMIN->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.users'), 'fa fa-user', User::class)
            ->setPermission(UserRoles::ROLE_ADMIN->value);
    }

    public function configureCrud(): Crud
    {
        // Default config for all cruds in this controller.
        // Only impact index and detail actions.
        // For Forms, use ->setFormTypeOption('view_timezone', '...') on all fields
        // Done globally in App\EasyAdmin\DateTimeFieldConfigurator
        return Crud::new()
            ->setTimezone(self::VIEW_TIMEZONE)
            ->setDateTimeFormat(self::DATETIME_FORMAT)
            ->setDateFormat(self::DATE_FORMAT)
            ->setTimeFormat(self::TIME_FORMAT);
    }
}
