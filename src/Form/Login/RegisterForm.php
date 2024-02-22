<?php

namespace App\Form\Login;

use App\Form\Component\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class RegisterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setData(['type' => 1]);
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['placeholder' => 'Email']
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'help' => 'The password must contain at least 8 characters and include at least one uppercase letter, one lowercase letter and one number.',
                'required' => true,
                'first_options'  => ['attr' => ['autocomplete' => 'off', 'placeholder' => 'Password']],
                'second_options' => ['attr' => ['autocomplete' => 'off', 'placeholder' => 'Confirm Password'], 'label' => false],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Individual' => 'individual',
                    'Company' => 'company'
                ],
                'choice_attr' => fn() => ['label_attr' => ['class' => 'mb-0']],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('submit', SubmitType::class);

        $companyFields = function (FormInterface $form, ?string $type) use ($options) {

            switch ($type) {
                case 0:
                    $form
                        ->add('firstName', TextType::class, [
                            'attr' => ['placeholder' => 'First Name'],
                            'help' => 'First Name',
                            'label' => false
                        ])
                        ->add('lastName', TextType::class, [
                            'help' => 'Last Name',
                            'label' => false
                        ])
                        ->add('idCard', FileType::class, [
                            'help' => "Personal identification card.",
                            'label' => false
                        ]);
                    break;
                case 1:
                    $form
                        ->add('companyName', TextType::class, [
                            'help' => 'Legal company name',
                            'label' => false
                        ])
                        ->add('companyNo', TextType::class, [
                            'help' => 'Business Registration Number',
                            'label' => false
                        ])
                        ->add('companyPhone', TelType::class, [
                            'help' => 'Company Contact Number',
                            'label' => false
                        ])
                        /*->add('companyAddress', AddressType::class, [
                            'help' => 'Company Address',
                            'label' => false
                        ])*/
                        ->add('companyDocuments', FileType::class, [
                            'help' => "Company registration documents.",
                            'label' => false
                        ]);
                    break;
            }
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            fn (FormEvent $event) =>  $companyFields($event->getForm(), $event->getData()['type'] ?? null)
        );

        $builder->get('type')->addEventListener(
            FormEvents::POST_SUBMIT,
            fn (FormEvent $event) => $companyFields($event->getForm()->getParent(), $event->getData())
        );
    }
}

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Legal company name'
            ])
            ->add('companyNo', TextType::class, [
                'label' => 'Business Registration Number'
            ])
            ->add('companyPhone', TelType::class, [
                'label' => 'Company Contact Number'
            ])
            ->add('companyAddress', AddressType::class, [
                'label' => 'Company Address'
            ])
            ->add('companyDocuments', FileType::class, [
                'help' => "Company registration documents."
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'company';
    }
}
