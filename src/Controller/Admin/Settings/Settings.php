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

namespace Moloni\Controller\Admin\Settings;

use Shop;
use DateTime;
use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Form\SettingsFormType;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Entity\MoloniSettings;
use Moloni\Helpers\Settings as helperSettings;
use Moloni\Repository\MoloniSettingsRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Settings extends MoloniController
{
    public function home(): Response
    {
        try {
            $apiData = $this->getRequiredFormData();
        } catch (MoloniApiException $e) {
            $apiData = [];
        }

        $form = $this->createForm(SettingsFormType::class, null, [
            'url' => $this->generateUrl('moloni_es_settings_save'),
            'api_data' => $apiData
        ]);
        $setting = helperSettings::getAll();

        if (isset($setting['dateCreated'])) {
            $setting['dateCreated'] = new DateTime($setting['dateCreated']);
        }

        $form->setData($setting);

        return $this->render(
            '@Modules/molonies/views/templates/admin/settings/Settings.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function save(Request $request): RedirectResponse
    {
        try {
            $apiData = $this->getRequiredFormData();
        } catch (MoloniApiException $e) {
            $apiData = [];
        }

        $form = $this->createForm(SettingsFormType::class, null, ['api_data' => $apiData]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submitData = $form->getData();
            $shopId = (int)Shop::getContextShopID();

            /** @var MoloniSettingsRepository $settingsRepository */
            $settingsRepository = $this
                ->getDoctrine()
                ->getRepository(MoloniSettings::class);

            $settingsRepository->saveSettings($submitData, $shopId);

            $msg = $this->trans('Settings saved.', 'Modules.Molonies.Success');
            $this->addSuccessMessage($msg);
        } else {
            $msg = $this->trans('Form not valid!', 'Modules.Molonies.Success');
            $this->addErrorMessage($msg);
        }

        return $this->redirectToSettings();
    }

    /**
     * Fetch required data for settings form
     *
     * @throws MoloniApiException
     */
    private function getRequiredFormData(): array
    {
        $measurementUnits = $warehouses = $documentSets = [];

        $measurementUnitsQuery = MoloniApiClient::measurementUnits()->queryMeasurementUnits();
        $warehousesQuery = MoloniApiClient::warehouses()->queryWarehouses();
        $documentSetsQuery = MoloniApiClient::documentSets()->queryDocumentSets();

        foreach ($measurementUnitsQuery as $measurementUnit) {
            $measurementUnits[$measurementUnit['name']] = $measurementUnit['measurementUnitId'];
        }

        foreach ($warehousesQuery as $warehouse) {
            $warehouses[$warehouse['name']] = $warehouse['warehouseId'];
        }

        foreach ($documentSetsQuery as $documentSet) {
            $documentSets[$documentSet['name']] = $documentSet['documentSetId'];
        }

        return [
            'measurementUnits' => $measurementUnits,
            'warehouses' => $warehouses,
            'documentSets' => $documentSets
        ];
    }
}
