<?php

namespace App\EasyAdmin;

use App\Controller\Admin\DashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

class DateTimeFilterConfigurator implements FilterConfiguratorInterface
{
    public function supports(FilterDto $filterDto, ?FieldDto $fieldDto, EntityDto $entityDto, AdminContext $context): bool
    {
        return DateTimeFilter::class === $filterDto->getFqcn();
    }

    public function configure(FilterDto $filterDto, ?FieldDto $fieldDto, EntityDto $entityDto, AdminContext $context): void
    {
        $filterDto->setFormTypeOptions([
            'value_type_options' => [
                // @see https://symfony.com/doc/current/reference/forms/types/date.html#field-options
                'model_timezone' => DashboardController::MODEL_TIMEZONE,
                'view_timezone' => DashboardController::VIEW_TIMEZONE,
            ],
        ]);
    }
}
