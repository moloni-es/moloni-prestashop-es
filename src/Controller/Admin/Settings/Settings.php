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
use Exception;
use Moloni\Enums\Languages;
use Moloni\Exceptions\MoloniException;
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
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class Settings extends MoloniController
{
    /**
     * Settings form page
     *
     * @return Response
     *
     * @throws Exception
     */
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

    /**
     * Save plugin settings
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function save(Request $request): RedirectResponse
    {
        try {
            $apiData = $this->getRequiredFormData();
        } catch (MoloniApiException $e) {
            $apiData = [];
        }

        $form = $this->createForm(SettingsFormType::class, null, ['api_data' => $apiData]);
        $form->handleRequest($request);

        try {
            if (!$form->isSubmitted() || !$form->isValid()) {
                throw new MoloniException('Form not valid!');
            }

            $submitData = $form->getData();
            $shopId = (int)Shop::getContextShopID();

            /** @var MoloniSettingsRepository $settingsRepository */
            $settingsRepository = $this
                ->getDoctrine()
                ->getRepository(MoloniSettings::class);

            $settingsRepository->saveSettings($submitData, $shopId);

            $this->addSuccessMessage($this->trans('Settings saved.', 'Modules.Molonies.Success'));
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);
        } catch (OptimisticLockException|ORMException $e) {
            $this->addErrorMessage($this->trans('Error saving settings', 'Modules.Molonies.Errors'));
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
        $measurementUnits = $warehouses = $documentSets = $countries = [];

        $measurementUnitsQuery = MoloniApiClient::measurementUnits()->queryMeasurementUnits();
        $warehousesQuery = MoloniApiClient::warehouses()->queryWarehouses();
        $documentSetsQuery = MoloniApiClient::documentSets()->queryDocumentSets();
        $countriesQuery = MoloniApiClient::countries()->queryCountries([
            'options' => [
                'defaultLanguageId' => Languages::ES
            ]
        ])['data']['countries']['data'] ?? [];

        foreach ($countriesQuery as $country) {
            $countries[$country['title']] = $country['countryId'];
        }

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
            'documentSets' => $documentSets,
            'countries' => $countries
        ];
    }
}
