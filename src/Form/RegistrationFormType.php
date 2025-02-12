<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

/** @extends AbstractType<mixed> */
class RegistrationFormType extends AbstractType
{
    private const int MIN_LENGTH = 8;
    private const int MAX_LENGTH = 4096;

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => new TranslatableMessage('registration.form.name')])
            ->add('registrationNotes', TextareaType::class, ['label' => new TranslatableMessage('registration.form.organizations')])
            ->add('mail', EmailType::class, ['label' => new TranslatableMessage('registration.form.mail')])
            ->add('plainPassword', PasswordType::class, [
                'label' => new TranslatableMessage('registration.form.enter_password'),
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => new TranslatableMessage('registration.form.enter_password'),
                    ]),
                    new Length([
                        'min' => self::MIN_LENGTH,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => self::MAX_LENGTH,
                    ]),
                ],
            ])
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
