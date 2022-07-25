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

use Moloni\Actions\Registration\CreateNewMoloniAccount;
use Moloni\Exceptions\MoloniException;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RegistrationFormHandler implements FormHandlerInterface
{
    /**
     * @var FormFactoryInterface the form factory
     */
    protected $formFactory;

    /**
     * @var FormDataProviderInterface the form data provider
     */
    protected $formDataProvider;

    /**
     * @var HookDispatcherInterface the event dispatcher
     */
    protected $hookDispatcher;


    public function __construct(
        FormFactoryInterface         $formFactory,
        HookDispatcherInterface      $hookDispatcher,
        RegistrationFormDataProvider $formDataProvider
    ) {
        $this->formFactory = $formFactory;
        $this->hookDispatcher = $hookDispatcher;
        $this->formDataProvider = $formDataProvider;
    }

    public function getForm(): FormInterface
    {
        $formBuilder = $this->formFactory->createNamedBuilder(
            "MoloniRegistration",
            RegistrationFormType::class
        );

        $formBuilder->setData($this->formDataProvider->getData());

        return $formBuilder->getForm();
    }

    public function save(array $data): array
    {
        $this->formDataProvider->setData($data);
        return [];
    }

    /**
     * Submit account
     *
     * @throws MoloniException
     */
    public function submit(array $data): void
    {
        new CreateNewMoloniAccount($data);
    }
}
