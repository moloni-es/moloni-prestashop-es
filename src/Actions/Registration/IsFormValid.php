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

namespace Moloni\Actions\Registration;

use Symfony\Component\Translation\TranslatorInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class IsFormValid
{
    private $formData;

    private $translator;

    private $errors = [];

    private $requiredFields = [
        'businessType',
        'companyName',
        'country',
        'email',
        'slug',
        'username',
        'vat',
        'serviceTerms',
    ];

    /**
     * Construct
     *
     * @param array $formData
     * @param TranslatorInterface $translator
     */
    public function __construct(array $formData, TranslatorInterface $translator)
    {
        $this->formData = $formData;
        $this->translator = $translator;

        $this->handle();
    }

    //          PUBLIC          //

    /**
     * Check if form is valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Get form errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    //          PRIVATES          //

    /**
     * Handler
     */
    private function handle(): void
    {
        $this
            ->checkRequired()
            ->checkEmail()
            ->checkSlug()
            ->checkVat()
            ->checkPassword();
    }

    private function addError($field, $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }

    //          CHECKS          //

    private function checkRequired(): IsFormValid
    {
        foreach ($this->requiredFields as $requiredField) {
            if (!isset($this->formData[$requiredField]) || empty($this->formData[$requiredField])) {
                $this->addError($requiredField, $this->translator->trans('This field is required', [], 'Modules.Molonies.Errors'));
            }
        }

        return $this;
    }

    private function checkEmail(): IsFormValid
    {
        if (!filter_var($this->formData['email'], FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', $this->translator->trans('E-mail is not valid', [], 'Modules.Molonies.Errors'));
        }

        return $this;
    }

    private function checkVat(): IsFormValid
    {
        if (!(new IsVatValid($this->formData['vat']))->handle()) {
            $this->addError('vat', $this->translator->trans('VAT is not valid', [], 'Modules.Molonies.Errors'));
        }

        return $this;
    }

    private function checkSlug(): IsFormValid
    {
        if (strlen($this->formData['slug']) < 4) {
            $this->addError('slug', $this->translator->trans('Slug has to have 4 or more characters', [], 'Modules.Molonies.Errors'));
        } else {
            if (!(new IsSlugValid($this->formData['slug']))->handle()) {
                $this->addError('slug', $this->translator->trans('Slug is not valid', [], 'Modules.Molonies.Errors'));
            }
        }

        return $this;
    }

    private function checkPassword(): void
    {
        if (!isset($this->formData['password']['first']) || !isset($this->formData['password']['second'])) {
            return;
        }

        if (empty($this->formData['password']['first'])) {
            $this->addError('password_first', $this->translator->trans('Password cannot be empty', [], 'Modules.Molonies.Errors'));
        }

        if (strlen($this->formData['password']['first']) < 6) {
            $this->addError('password_first', $this->translator->trans('Password has to have 6 or more characters', [], 'Modules.Molonies.Errors'));
        }

        if (strlen($this->formData['password']['first']) > 16) {
            $this->addError('password_first', $this->translator->trans('Password cannot have more than 16 characters', [], 'Modules.Molonies.Errors'));
        }

        // As an at least one uppercase letter
        if ((bool)preg_match("/[A-Z]/", $this->formData['password']['first']) === false) {
            $this->addError('password_first', $this->translator->trans('Password needs to have at least one uppercase letter', [], 'Modules.Molonies.Errors'));
        }

        // As an at least one lowercase letter
        if ((bool)preg_match("/[a-z]/", $this->formData['password']['first']) === false) {
            $this->addError('password_first', $this->translator->trans('Password needs to have at least one lowercase letter', [], 'Modules.Molonies.Errors'));
        }

        // As at least one symbol
        if ((bool)preg_match("/[\W._]/", $this->formData['password']['first']) === false) {
            $this->addError('password_first', $this->translator->trans('Password needs to have at least one symbol', [], 'Modules.Molonies.Errors'));
        }

        // As at least one number
        if ((bool)preg_match("/\d/", $this->formData['password']['first']) === false) {
            $this->addError('password_first', $this->translator->trans('Password needs to have at least one number', [], 'Modules.Molonies.Errors'));
        }

        // No spaces
        if (preg_match("/\s/", $this->formData['password']['first'])) {
            $this->addError('password_first', $this->translator->trans("Password should not contain any white space", [], 'Modules.Molonies.Errors'));
        }

        if ($this->formData['password']['first'] !== $this->formData['password']['second']) {
            $this->addError('password_second', $this->translator->trans('Passwords do not match', [], 'Modules.Molonies.Errors'));
        }
    }
}
