<?php
declare(strict_types=1);

namespace App\Form\CommandCenter\Resume;

use App\DataTransferObject\Form\Contact\LocationDto;
use App\DataTransferObject\Form\EmploymentHistory\EmployerDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $builder->getData();

        $builder
            ->add('title',
                TextType::class,
                [
                    'label' => 'Employer title',
                    'required' => false,
                ]
            )
            ->add('aboutPage',
                UrlType::class,
                [
                    'required' => false,
                ]
            )
            ->add('contacts',
                ContactsFormType::class,
                [
                    'required' => false,
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EmployerDto::class,
        ]);
    }
}
