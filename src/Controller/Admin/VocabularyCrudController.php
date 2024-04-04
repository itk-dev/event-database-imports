<?php

namespace App\Controller\Admin;

use App\Entity\Vocabulary;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class VocabularyCrudController extends AbstractBaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Vocabulary::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['name'])
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name')
                ->setLabel(new TranslatableMessage('admin.vocabulary.name')),
            AssociationField::new('tags')
                ->setQueryBuilder(
                    fn (QueryBuilder $queryBuilder) => $queryBuilder->addCriteria(
                        Criteria::create()->orderBy(['name' => Order::Ascending])
                    )
                )
                ->setLabel(new TranslatableMessage('admin.vocabulary.tags')),

            FormField::addFieldset(new TranslatableMessage('admin.vocabulary.edited.headline'))
                ->hideWhenCreating(),
            DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.vocabulary.edited.updated'))
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }
}
