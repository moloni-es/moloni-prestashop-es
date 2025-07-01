<?php

/**
 * 2025 - Moloni.com
 *
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Moloni
 * @copyright Moloni
 * @license   https://creativecommons.org/licenses/by-nd/4.0/
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

declare(strict_types=1);

namespace Moloni\Form\Login;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

if (!defined('_PS_VERSION_')) {
    exit;
}

class LoginFormType extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return $builder
            ->add('clientID', TextType::class, [
                'label' => $this->trans('Client ID', 'Modules.Molonies.Common'),
                'required' => true,
                'translation_domain' => 'Modules.Molonies.Common',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('clientSecret', TextType::class, [
                'label' => $this->trans('Client Secret', 'Modules.Molonies.Common'),
                'required' => true,
                'constraints' => [
                    new Length(['min' => 10]),
                    new NotBlank(),
                ],
            ])
            ->add('connect', SubmitType::class, [
                'attr' => ['class' => 'btn-primary moloni-login--button'],
                'label' => $this->trans('Connect', 'Modules.Molonies.Common'),
            ])
            ->setAction($options['url'])
            ->setMethod('POST');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'url' => '',
        ]);
    }
}
