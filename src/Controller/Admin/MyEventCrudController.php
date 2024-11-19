<?php

namespace App\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Translation\TranslatableMessage;

class MyEventCrudController extends EventCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);

        return $crud
            ->setHelp('index', new TranslatableMessage('admin.my.event.index.help'))
            ->setPageTitle('edit', new TranslatableMessage('admin.my.event.edit.title'))
            ->setPageTitle('index', new TranslatableMessage('admin.my.event.index.title'))
            ->setPageTitle('detail', new TranslatableMessage('admin.my.event.edit.title'));
    }

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

        // Filter only make sense if there are more than 2 choices
        if (2 <= count($choices)) {
            $filters->add(ChoiceFilter::new('organization')->setChoices($choices));
        }

        return parent::configureBaseFilters($filters);
    }

    private function getOrganizationChoices(): array
    {
        $choices = [];
        foreach ($this->getUser()->getOrganizations() as $organization) {
            $key = $organization->getName() ?? $organization->getId();
            if (null !== $key) {
                $choices[$key] = $organization->getId();
            }
        }

        return $choices;
    }
}
