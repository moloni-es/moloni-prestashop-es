<?php

namespace Moloni\Form\Login;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginFormType extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return $builder
            ->add('clientID', TextType::class, [
                'label' => $this->trans('Client ID', "Modules.Molonies.Common"),
                'required' => true,
                'translation_domain' => 'Modules.Molonies.Common',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('clientSecret', TextType::class, [
                'label' => $this->trans('Client Secret', "Modules.Molonies.Common"),
                'required' => true,
                'constraints' => [
                    new Length(['min' => 10]),
                    new NotBlank(),
                ],
            ])
            ->add('connect', SubmitType::class, [
                'attr' => ['class' => 'btn-primary'],
                'label' => $this->trans('Connect', "Modules.Molonies.Common"),
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
