<?php

namespace App\Controller\Admin;

use App\Entity\Tag;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class TagCrudController extends AbstractBaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['name'])
            ->setDefaultSort(['name' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('slug')
                ->setLabel(new TranslatableMessage('admin.tag.tag'))
                ->setDisabled(),
            TextField::new('name')
                ->setLabel(new TranslatableMessage('admin.tag.name')),
            AssociationField::new('vocabularies')
                ->setLabel(new TranslatableMessage('admin.tag.vocabularies'))
                ->setPermission(UserRoles::ROLE_ADMIN->value),

            FormField::addFieldset(new TranslatableMessage('admin.tag.edited.headline'))
                ->hideWhenCreating(),
            DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.tag.edited.updated'))
                ->setDisabled()
                ->hideWhenCreating()
                ->hideOnIndex()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        $filters
            ->add('name')
            ->add('slug');

        if ($this->isGranted(UserRoles::ROLE_EDITOR->value)) {
            $filters->add('vocabularies');
        }

        return $filters;
    }
}
