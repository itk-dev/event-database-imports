<?php

namespace App\EasyAdmin;

use App\Controller\Admin\DashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class DateTimeFieldConfigurator implements FieldConfiguratorInterface
{
    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return
            DateTimeField::class === $field->getFieldFqcn()
            || DateField::class === $field->getFieldFqcn()
            || TimeField::class === $field->getFieldFqcn();
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $field->setFormTypeOptions([
            'model_timezone' => DashboardController::MODEL_TIMEZONE,
            'view_timezone' => DashboardController::VIEW_TIMEZONE,
        ]);
    }
}
