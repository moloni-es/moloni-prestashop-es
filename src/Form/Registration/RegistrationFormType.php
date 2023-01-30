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
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RegistrationFormType extends TranslatorAwareType
{
    /** @var FormBuilderInterface */
    private $builder;

    /** @var RegistrationFormDataProvider */
    private $options;

    /**
     * Construct
     *
     * @param TranslatorInterface|\Symfony\Contracts\Translation\TranslatorInterface $translator
     * @param array $locales
     * @param RegistrationFormDataProvider $dataProvider
     */
    public function __construct($translator, array $locales, RegistrationFormDataProvider $dataProvider)
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
            'label' => $this->trans('E-mail', "Modules.Molonies.Signup"),
            'required' => true,
        ]);

        return $this;
    }

    private function setBusinessType(): RegistrationFormType
    {
        $this->builder->add('businessType', ChoiceType::class, [
            'label' => $this->trans('Business type', "Modules.Molonies.Signup"),
            'choices' => $this->options->getBusinessAreas(),
            'required' => true,
            'placeholder' => $this->trans('Please select an option', "Modules.Molonies.Signup"),
        ]);

        $this->builder->add('businessTypeName', TextType::class, [
            'label' => $this->trans('Business type name', "Modules.Molonies.Signup"),
            'required' => false,
        ]);

        return $this;
    }

    private function setCompanyName(): RegistrationFormType
    {
        $this->builder->add('companyName', TextType::class, [
            'label' => $this->trans('Company name', "Modules.Molonies.Signup"),
            'required' => true,
        ]);

        return $this;
    }

    private function setVat(): RegistrationFormType
    {
        $this->builder->add('vat', TextType::class, [
            'label' => $this->trans('Vat', "Modules.Molonies.Signup"),
            'required' => true,
        ]);

        return $this;
    }

    private function setCountry(): RegistrationFormType
    {
        $this->builder->add('country', ChoiceType::class, [
            'label' => $this->trans('Country', "Modules.Molonies.Signup"),
            'placeholder' => $this->trans('Please select an option', "Modules.Molonies.Signup"),
            'choices' => $this->options->getCountries(),
            'required' => true,
        ]);

        return $this;
    }

    private function setSlug(): RegistrationFormType
    {
        $this->builder->add('slug', TextType::class, [
            'label' => $this->trans('Slug', "Modules.Molonies.Signup"),
            'constraints' => [
                new Length(['min' => 4]),
                new NotBlank(),
            ],
            'required' => true,
        ]);

        return $this;
    }

    private function setUserName(): RegistrationFormType
    {
        $this->builder->add('username', TextType::class, [
            'label' => $this->trans('Name of person responsible for the account', "Modules.Molonies.Signup"),
            'required' => true,
        ]);

        return $this;
    }

    private function setPhone(): RegistrationFormType
    {
        $this->builder->add('phone', TelType::class, [
            'label' => $this->trans('Phone', "Modules.Molonies.Signup"),
            'label_attr' => [
                'popover' => $this->trans(
                    'Do I have to fill in this field? We use your mobile number to validate your identity and if one day we need to get in touch quickly, the mobile is the most efficient way. We recommend filling in this field.',
                    "Modules.Molonies.Signup"
                ),
            ],
            'required' => false,
        ]);

        return $this;
    }

    private function setPassword(): RegistrationFormType
    {
        $this->builder->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'label' => $this->trans('Password', "Modules.Molonies.Signup"),
            'constraints' => [
                new Length([
                    'min' => 6,
                    'minMessage' => $this->getMinLengthValidationMessage(),
                    'max' => 16,
                    'maxMessage' => $this->getMaxLengthValidationMessage(),
                ]),
                new NotBlank(),
            ],
            'first_options' => [
                'label' => $this->trans('Password', "Modules.Molonies.Signup"),
            ],
            'second_options' => [
                'label' => $this->trans('Password confirmation', "Modules.Molonies.Signup"),
            ],
            'required' => true,
        ]);

        return $this;
    }

    private function setServiceTerms(): RegistrationFormType
    {
        $label = $this->trans(
            'I have read and accept the <a href="https://www.moloni.es/termsandconditions" target="_blank">Moloni Terms of Service</a> with the inclusion of the regulation act of the subcontractor according to article 28 of the Regulation.',
            "Modules.Molonies.Signup"
        );

        $this->builder->add('serviceTerms', CheckboxType::class, [
            'required' => true,
            'label' => $label,
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
                "Modules.Molonies.Signup"
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
            'attr' => [
                'class' => 'btn-primary',
            ],
            'label' => $this->trans('Register', "Modules.Molonies.Signup"),
        ]);
    }

    private function getMinLengthValidationMessage(): string
    {
        return $this->trans('This field cannot be shorter than {0} characters', "Modules.Molonies.Signup", ['{0}' => 6]);
    }

    private function getMaxLengthValidationMessage(): string
    {
        return $this->trans('This field cannot be longer than {0} characters', "Modules.Molonies.Signup", ['{0}' => 16]);
    }
}
