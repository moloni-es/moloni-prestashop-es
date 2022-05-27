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

namespace Moloni\Actions\Settings;

use Shop;
use Moloni\Actions\Tools\WebhookCreate;
use Moloni\Actions\Tools\WebhookDeleteAll;
use Moloni\Enums\Boolean;
use Moloni\Exceptions\MoloniException;
use Moloni\Repository\MoloniSettingsRepository;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Form\SettingsFormType;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

class SettingsSave extends AbstractSettingsAction
{
    protected $settingsRepository;

    public function __construct(int $languageId, FormFactory $formBuilder, MoloniSettingsRepository $settingsRepository)
    {
        parent::__construct($languageId, $formBuilder);

        $this->settingsRepository = $settingsRepository;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws MoloniException
     */
    public function handle(Request $request): void
    {
        try {
            $apiData = $this->getRequiredFormData();
        } catch (MoloniApiException $e) {
            $apiData = [];
        }

        $form = $this->formBuilder->create(SettingsFormType::class, null, ['api_data' => $apiData]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new MoloniException('Form not valid!');
        }

        $submitData = $form->getData();

        $shopId = (int)Shop::getContextShopID();

        $this->settingsRepository->saveSettings($submitData, $shopId);

        $this->createWebHooks($submitData);
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
