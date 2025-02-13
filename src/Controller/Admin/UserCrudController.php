<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Types\UserRoles;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Translation\TranslatableMessage;

class UserCrudController extends AbstractBaseCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->showEntityActionsInlined()
            ->setPageTitle('edit', new TranslatableMessage('admin.user.edit.title'))
            ->setPageTitle('index', new TranslatableMessage('admin.user.index.title'))
            ->setPageTitle('detail', new TranslatableMessage('admin.user.edit.title'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        if (!$this->isGranted(UserRoles::ROLE_ADMIN->value)) {
            $actions->remove(Crud::PAGE_INDEX, Action::NEW);
            $actions->remove(Crud::PAGE_DETAIL, Action::DELETE);
        }

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        $userRolesChoices = [
            'ROLE_ADMIN' => new TranslatableMessage('admin.user.role.role_admin'),
            'ROLE_EDITOR' => new TranslatableMessage('admin.user.role.role_editor'),
            'ROLE_ORGANIZATION_ADMIN' => new TranslatableMessage('admin.user.role.role_organization_admin'),
            'ROLE_ORGANIZATION_EDITOR' => new TranslatableMessage('admin.user.role.role_organization_editor'),
            'ROLE_API_USER' => new TranslatableMessage('admin.user.role.role_api_user'),
            'ROLE_USER' => new TranslatableMessage('admin.user.role.role_user'),
        ];

        if ($this->isGranted(UserRoles::ROLE_SUPER_ADMIN->value)) {
            $superAdminRole = ['ROLE_SUPER_ADMIN' => new TranslatableMessage('admin.user.role.role_super_admin')];
            $userRolesChoices = array_merge($superAdminRole, $userRolesChoices);
        }

        return [
            IdField::new('id')
                ->setLabel(new TranslatableMessage('admin.user.id'))
                ->setDisabled()
                ->hideWhenCreating(),

            TextField::new('name')
                ->setLabel(new TranslatableMessage('admin.user.name')),
            EmailField::new('mail')
                ->setLabel(new TranslatableMessage('admin.user.mail')),
            AssociationField::new('organizations')
                ->setLabel(new TranslatableMessage('admin.user.organizers'))
                ->setFormTypeOption('by_reference', false)
                ->setPermission(UserRoles::ROLE_ADMIN->value),
            TextareaField::new('registrationNotes')
                ->setLabel(new TranslatableMessage('admin.user.registrationNotes'))
                ->setPermission(UserRoles::ROLE_ADMIN->value)
                ->setDisabled()
                ->hideOnIndex()
                ->hideWhenCreating(),
            ChoiceField::new('roles')
                ->setTranslatableChoices($userRolesChoices)
                ->allowMultipleChoices()
                ->renderExpanded()
                ->setLabel(new TranslatableMessage('admin.user.roles'))
                ->setPermission(UserRoles::ROLE_ADMIN->value),
            TextField::new('password')
                ->setFormType(RepeatedType::class)
                ->setFormTypeOptions([
                    'type' => PasswordType::class,
                    'first_options' => ['label' => new TranslatableMessage('admin.user.password')],
                    'second_options' => ['label' => new TranslatableMessage('admin.user.password2')],
                    'mapped' => false,
                ])
                ->setRequired(Crud::PAGE_NEW === $pageName)
                ->onlyOnForms(),
            BooleanField::new('enabled')
                ->setLabel(new TranslatableMessage('admin.user.enabled'))
                ->setPermission(UserRoles::ROLE_ADMIN->value),
            DateTimeField::new('emailVerifiedAt')
                ->setLabel(new TranslatableMessage('admin.user.email_verified'))
                ->setDisabled()
                ->hideOnIndex(),
            DateTimeField::new('termsAcceptedAt')
                ->setLabel(new TranslatableMessage('admin.user.terms_accepted_at'))
                ->setDisabled()
                ->hideOnIndex(),

            FormField::addFieldset(new TranslatableMessage('admin.user.edited.headline'))
                ->hideWhenCreating(),
            DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.user.edited.updated'))
                ->setDisabled()
                ->hideWhenCreating()
                ->hideOnIndex()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if (!$this->isGranted(UserRoles::ROLE_ADMIN->value)) {
            $qb->andWhere('entity.id = :userId')->setParameter('userId', $this->getUser()->getId());
        }

        return $qb;
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);

        return $this->addPasswordEventListener($formBuilder);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);

        return $this->addPasswordEventListener($formBuilder);
    }

    private function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());
    }

    /**
     * @psalm-return \Closure(mixed):void
     */
    private function hashPassword(): \Closure
    {
        return function ($event) {
            $form = $event->getForm();
            if (!$form->isValid()) {
                return;
            }
            $password = $form->get('password')->getData();
            if (null === $password) {
                return;
            }

            $user = $this->getUser();
            $hash = $this->userPasswordHasher->hashPassword($user, $password);
            $form->getData()->setPassword($hash);
        };
    }
}
