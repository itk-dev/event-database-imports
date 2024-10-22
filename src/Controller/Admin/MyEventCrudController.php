<?php

namespace App\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class MyEventCrudController extends EventCrudController
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $qb->andWhere('entity.organization IN (:organizations)')
            ->setParameter('organizations', $this->getUser()->getOrganizations());

        return $qb;
    }

    public function configureFilters(Filters $filters): Filters
    {
        $choices = $this->getOrganizationChoices();

        if (0 < count($choices)) {
            $filters->add(ChoiceFilter::new('organization')->setChoices($choices));
        }

        return parent::configureFilters($filters);
    }
}
