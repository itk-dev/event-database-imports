<?php

namespace App\Controller\Admin;

use App\Entity\Occurrence;
use Doctrine\Common\Collections\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class EmbedOccurrenceCrudController extends AbstractBaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Occurrence::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['start' => Order::Ascending->value]);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addFieldset('Dates')
                ->setLabel(new TranslatableMessage('admin.occurrence.dates.headline')),
            DateTimeField::new('start')
                ->setLabel(new TranslatableMessage('admin.occurrence.dates.start'))
                ->setColumns(6),
            DateTimeField::new('end')
                ->setLabel(new TranslatableMessage('admin.occurrence.dates.end'))
                ->setColumns(6),

            FormField::addFieldset('Basic information')
                ->setLabel(new TranslatableMessage('admin.occurrence.basic.headline')),
            TextField::new('ticketPriceRange')
                ->setLabel(new TranslatableMessage('admin.occurrence.basic.price'))
                ->setColumns(6),
            TextField::new('room')
                ->setLabel(new TranslatableMessage('admin.occurrence.basic.room'))
                ->setColumns(6),
        ];
    }
}
