<?php
/**
 * 2022 - Moloni.com
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

namespace Moloni\Form\Registration;

use Moloni\Exceptions\MoloniApiException;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class RegistrationFormType extends TranslatorAwareType
{
    /** @var FormBuilderInterface */
    private $builder;

    /** @var RegistrationFormDataProvider */
    private $options;

    public function __construct(TranslatorInterface $translator, array $locales, RegistrationFormDataProvider $dataProvider)
    {
        $this->options = $dataProvider;

        parent::__construct($translator, $locales);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->builder = $builder;

        try {
            $this->options->loadMoloniData();
        } catch (MoloniApiException $e) {
            // Do not catch
        }

        $this
            ->setEmail()
            ->setBusinessType()
            ->setCompanyName()
            ->setVat()
            ->setCountry()
            ->setSlug()
            ->setUserName()
            ->setPhone()
            ->setPassword()
            ->setServiceTerms()
            ->setNewsletter()
            ->registerButton();

        return $this->builder;
    }

    private function setEmail(): RegistrationFormType
    {
        $this->builder->add('email', EmailType::class, [
            'label' => $this->trans('E-mail', "Modules.Molonies.Common"),
            'required' => true,
        ]);

        return $this;
    }

    private function setBusinessType(): RegistrationFormType
    {
        $this->builder->add('businessType', ChoiceType::class, [
            'label' => $this->trans('Business type', "Modules.Molonies.Common"),
            'choices' => $this->options->getBusinessAreas(),
            'required' => true,
        ]);

        $this->builder->add('businessTypeName', TextType::class, [
            'label' => $this->trans('Business type name', "Modules.Molonies.Common"),
            'required' => false,
        ]);

        return $this;
    }

    private function setCompanyName(): RegistrationFormType
    {
        $this->builder->add('companyName', TextType::class, [
            'label' => $this->trans('Company name', "Modules.Molonies.Common"),
            'required' => true,
        ]);

        return $this;
    }

    private function setVat(): RegistrationFormType
    {
        $this->builder->add('vat', TextType::class, [
            'label' => $this->trans('Vat', "Modules.Molonies.Common"),
            'required' => true,
        ]);

        return $this;
    }

    private function setCountry(): RegistrationFormType
    {
        $this->builder->add('country', ChoiceType::class, [
            'label' => $this->trans('Country', "Modules.Molonies.Common"),
            'choices' => $this->options->getCountries(),
            'required' => true,
        ]);

        return $this;
    }

    private function setSlug(): RegistrationFormType
    {
        $this->builder->add('slug', TextType::class, [
            'label' => $this->trans('Slug', "Modules.Molonies.Common"),
            'required' => true,
        ]);

        return $this;
    }

    private function setUserName(): RegistrationFormType
    {
        $this->builder->add('username', TextType::class, [
            'label' => $this->trans('Name of person responsible for the account', "Modules.Molonies.Common"),
            'required' => true,
        ]);

        return $this;
    }

    private function setPhone(): RegistrationFormType
    {
        $this->builder->add('phone', TextType::class, [
            'label' => $this->trans('Phone', "Modules.Molonies.Common"),
            'label_attr' => [
                'popover' => $this->trans(
                    'Do I have to fill in this field? We use your mobile number to validate your identity and if one day we need to get in touch quickly, the mobile is the most efficient way. We recommend filling in this field.',
                    "Modules.Molonies.Common"
                ),
            ],
            'required' => false,
        ]);

        return $this;
    }

    private function setPassword(): RegistrationFormType
    {
        $this->builder->add('password', PasswordType::class, [
            'label' => $this->trans('Password', "Modules.Molonies.Common"),
            'help' => $this->trans(
                'Between 6 and 16 characters, at least one uppercase letter, one symbol and one number',
                "Modules.Molonies.Settings"
            ),
            'required' => true,
        ]);

        $this->builder->add('passwordConfirmation', PasswordType::class, [
            'label' => $this->trans('Password confirmation', "Modules.Molonies.Common"),
            'required' => true,
        ]);

        return $this;
    }

    private function setServiceTerms(): RegistrationFormType
    {
        $this->builder->add('serviceTerms', CheckboxType::class, [
            'required' => true,
            'label' => $this->trans(
                'I have read and accept the Moloni Terms of Service with the inclusion of the regulation act of the subcontractor according to article 28 of the Regulation.',
                "Modules.Molonies.Common"
            ),
            'attr' => [
                'material_design' => true,
            ],
        ]);

        return $this;
    }

    private function setNewsletter(): RegistrationFormType
    {
        $this->builder->add('newsletter', CheckboxType::class, [
            'required' => false,
            'label' => $this->trans(
                'I want to receive email updates about Moloni',
                "Modules.Molonies.Common"
            ),
            'attr' => [
                'material_design' => true,
            ],
        ]);

        return $this;
    }

    private function registerButton(): void
    {
        $this->builder->add('register', SubmitType::class, [
            'attr' => ['class' => 'btn-primary'],
            'label' => $this->trans('Register', "Modules.Molonies.Common"),
        ]);

    }
}
