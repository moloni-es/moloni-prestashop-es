<?php

namespace Moloni\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return $builder
            ->add('clientID', TextType::class, [
                'label' => 'Client ID',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('clientSecret', TextType::class, [
                'label' => 'Client Secret',
                'required' => true,
                'constraints' => [
                    new Length(['min' => 10]),
                    new NotBlank(),
                ],
                'translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('connect', SubmitType::class, [
                'attr' => ['class' => 'btn-primary'],
                'label' => 'Connect',
                'translation_domain' => 'Modules.Molonies.Common',
            ])
            ->setAction($options['url'])
            ->setMethod('POST');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'url' => ''
        ));
    }
}
