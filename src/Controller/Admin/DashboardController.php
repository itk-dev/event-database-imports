<?php

namespace App\Controller\Admin;

use App\Entity\Address;
use App\Entity\Event;
use App\Entity\Feed;
use App\Entity\FeedItem;
use App\Entity\Location;
use App\Entity\Organization;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Vocabulary;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
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

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        if ($this->isGranted(UserRoles::ROLE_EDITOR->value)) {
            return $this->redirect($adminUrlGenerator->setController(EventCrudController::class)->generateUrl());
        }

        if ($this->isGranted(UserRoles::ROLE_ORGANIZATION_EDITOR->value)) {
            return $this->redirect($adminUrlGenerator->setController(MyEventCrudController::class)->generateUrl());
        }

        return $this->redirect($adminUrlGenerator->setController(EventCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="/admin/styles/aak-logo-1.svg" width="119px" height="60px" alt="\'Det sker i Aarhus\' Eventdatabasen">')
            ->setFaviconPath('img/favicon.ico')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        // My Content
        yield MenuItem::section(new TranslatableMessage('admin.label.my_content'))
            ->setPermission(UserRoles::ROLE_ORGANIZATION_EDITOR->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.events'), 'fa fa-calendar', Event::class)
            ->setController(MyEventCrudController::class)
            ->setPermission(UserRoles::ROLE_ORGANIZATION_EDITOR->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.organizations'), 'fa fa-sitemap', Organization::class)
            ->setController(MyOrganizationCrudController::class)
            ->setPermission(UserRoles::ROLE_ORGANIZATION_EDITOR->value);

        // All Content
        yield MenuItem::section(new TranslatableMessage('admin.label.all_content'));
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.events'), 'fa fa-calendar', Event::class)
            ->setController(EventCrudController::class)
            ->setPermission(UserRoles::ROLE_USER->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.organizations'), 'fa fa-sitemap', Organization::class)
            ->setController(OrganizationCrudController::class)
            ->setPermission(UserRoles::ROLE_USER->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.location'), 'fa fa-location-dot', Location::class)
            ->setController(LocationCrudController::class)
            ->setPermission(UserRoles::ROLE_USER->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.address'), 'fa fa-address-book', Address::class)
            ->setController(AddressCrudController::class)
            ->setPermission(UserRoles::ROLE_USER->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.tags'), 'fa fa-tags', Tag::class)
            ->setController(TagCrudController::class)
            ->setPermission(UserRoles::ROLE_USER->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.vocabularies'), 'fa fa-book', Vocabulary::class)
            ->setController(VocabularyCrudController::class)
            ->setPermission(UserRoles::ROLE_ADMIN->value);

        yield MenuItem::section(new TranslatableMessage('admin.label.feeds'))
            ->setPermission(UserRoles::ROLE_ADMIN->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.feeds'), 'fa fa-rss', Feed::class)
            ->setController(FeedCrudController::class)
            ->setPermission(UserRoles::ROLE_ADMIN->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.feedItems'), 'fa fa-list-alt', FeedItem::class)
            ->setController(FeedItemCrudController::class)
            ->setPermission(UserRoles::ROLE_ADMIN->value);

        yield MenuItem::section(new TranslatableMessage('admin.label.users'))
            ->setPermission(UserRoles::ROLE_ADMIN->value);
        yield MenuItem::linkToCrud(new TranslatableMessage('admin.link.users'), 'fa fa-user', User::class)
            ->setController(UserCrudController::class)
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

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        assert($user instanceof User);

        $profileUrl = $this->adminUrlGenerator
            ->setController(UserCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($user->getId())
            ->generateUrl();

        return parent::configureUserMenu($user)
            ->setName($user->getName())
            ->displayUserAvatar(false)
            ->addMenuItems([
                MenuItem::linkToUrl(new TranslatableMessage('admin.user_menu.my_profile'), 'fa fa-id-card', $profileUrl),
                MenuItem::linkToUrl(new TranslatableMessage('admin.user_menu.terms'), 'fa-file-signature', 'https://arrangoer.aarhus.dk/markedsfoering/det-sker-i-aarhus-eventdatabasen/brugeraftale'),
            ]);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('/admin/styles/admin.css');
    }
}
