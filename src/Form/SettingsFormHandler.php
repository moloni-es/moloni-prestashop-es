<?php

namespace Moloni\Form;

use Moloni\Actions\Tools\WebhookCreate;
use Moloni\Actions\Tools\WebhookDeleteAll;
use Moloni\Enums\Boolean;
use Moloni\Exceptions\MoloniException;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class SettingsFormHandler implements FormHandlerInterface
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
        FormFactoryInterface $formFactory,
        HookDispatcherInterface $hookDispatcher,
        SettingsFormDataProvider $formDataProvider
    ) {
        $this->formFactory = $formFactory;
        $this->hookDispatcher = $hookDispatcher;
        $this->formDataProvider = $formDataProvider;
    }

    public function getForm(): FormInterface
    {
        $formBuilder = $this->formFactory->createNamedBuilder(
            "MoloniSettings",
            SettingsFormType::class
        );

        $formBuilder->setData($this->formDataProvider->getData());

        return $formBuilder->getForm();
    }

    public function save(array $data): array
    {
        $this->formDataProvider->setData($data);
        $this->createWebHooks($data);
        return [];
    }

    private function createWebHooks($submitData): void
    {
        try {
            (new WebhookDeleteAll())->handle();
            $action = new WebhookCreate();

            if ($submitData['syncStockToPrestashop'] === Boolean::YES) {
                $action->handle('Product', 'stockChanged');
            }

            if ($submitData['addProductsToPrestashop'] === Boolean::YES) {
                $action->handle('Product', 'create');
            }

            if ($submitData['updateProductsToPrestashop'] === Boolean::YES) {
                $action->handle('Product', 'update');
            }
        } catch (MoloniException $e) {
            // no need to catch anything
        }
    }
}
