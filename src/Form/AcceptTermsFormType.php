<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\IsTrue;

/** @extends AbstractType<mixed> */
class AcceptTermsFormType extends AbstractType
{
    public function __construct()
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('agreeTerms', CheckboxType::class, [
                'label' => new TranslatableMessage('registration.form.agree_terms'),
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => new TranslatableMessage('registration.form.agree_terms'),
                    ]),
                ],
            ])
        ;
    }
}
