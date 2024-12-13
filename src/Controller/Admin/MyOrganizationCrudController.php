<?php

namespace App\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Symfony\Component\Translation\TranslatableMessage;

class MyOrganizationCrudController extends OrganizationCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);

        return $crud
            ->setPageTitle('edit', new TranslatableMessage('admin.my.organizer.edit.title'))
            ->setPageTitle('index', new TranslatableMessage('admin.my.organizer.index.title'))
            ->setPageTitle('detail', new TranslatableMessage('admin.my.organizer.edit.title'));
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $qb->andWhere('entity IN (:organizations)')
            ->setParameter('organizations', $this->getUser()->getOrganizations());

        return $qb;
    }
}
