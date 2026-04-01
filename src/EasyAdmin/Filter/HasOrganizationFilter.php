<?php

namespace App\EasyAdmin\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\BooleanFilterType;
use Symfony\Contracts\Translation\TranslatableInterface;

final class HasOrganizationFilter implements FilterInterface
{
    use FilterTrait;

    private string $associationProperty;

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $associationProperty, $label = null): self
    {
        $filter = (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty('hasOrganization')
            ->setLabel($label)
            ->setFormType(BooleanFilterType::class)
            ->setFormTypeOption('translation_domain', 'EasyAdminBundle');

        $filter->associationProperty = $associationProperty;

        return $filter;
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $alias = $filterDataDto->getEntityAlias();
        $assocAlias = 'ea_has_org_'.$filterDataDto->getParameterName();

        $queryBuilder->leftJoin(sprintf('%s.%s', $alias, $this->associationProperty), $assocAlias);

        if ($filterDataDto->getValue()) {
            $queryBuilder->andWhere(sprintf('%s IS NOT NULL', $assocAlias));
        } else {
            $queryBuilder->andWhere(sprintf('%s IS NULL', $assocAlias));
        }
    }
}
