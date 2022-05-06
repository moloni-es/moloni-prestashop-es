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

use Doctrine\Persistence\ManagerRegistry;
use Moloni\Api\MoloniApi;
use Moloni\Api\MoloniApiClient;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Enums\Boolean;
use Moloni\Enums\DocumentStatus;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Helpers\Moloni;
use Moloni\Helpers\Settings as helperSettings;
use Moloni\Repository\MoloniSettingsRepository;
use Moloni\Traits\DocumentTypesTrait;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Settings extends MoloniController
{
    use DocumentTypesTrait;

    public function home(Request $request): Response
    {
        $form = $this->getSettingsFormBuilder(['skip_values' => true])
            ->getForm();

        return $this->render(
            '@Modules/molonies/views/templates/admin/settings/Settings.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function save(
        Request $request,
        MoloniSettingsRepository $settingsRepository,
        ManagerRegistry $doctrine
    ): RedirectResponse {
        $form = $this->getSettingsFormBuilder(['skip_values' => true])
            ->getForm()
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submitData = $form->getData();

            foreach ($submitData as $label => $value) {
                if (is_array($value)) {
                    $value = serialize($value);
                }

                // todo: actually save settings
            }

            $msg = $this->trans('Settings saved.', 'Modules.Molonies.Success');
            $this->addSuccessMessage($msg);
        } else {
            $msg = $this->trans('Form not valid!', 'Modules.Molonies.Success');
            $this->addErrorMessage($msg);
        }

        return $this->redirectToSettings();
    }

    /**
     * Settings form builder
     *
     * @param array|null $options
     *
     * @return FormBuilderInterface
     */
    private function getSettingsFormBuilder(?array $options = []): FormBuilderInterface
    {

        $taxes = $addresses = $maturityDates = $paymentMethods = $documentSets = $measurementUnits = $warehouses = [];

        if (!$options['skip_values']) {
            try {
                $variables = ['companyId' => (int)Moloni::get('company_id'), 'options' => null];

                $taxesQuery = MoloniApiClient::taxes()->queryTaxes($variables);
                $maturityDatesQuery = MoloniApiClient::measurementUnits()->queryMeasurementUnits($variables);
                $measurementUnitsQuery = MoloniApiClient::maturityDates()->queryMaturityDates($variables);
                $warehousesQuery = MoloniApiClient::warehouses()->queryWarehouses($variables);
                $paymentMethodsQuery = MoloniApiClient::paymentMethods()->queryPaymentMethods($variables);
                $documentSetsQuery = MoloniApiClient::documentSets()->queryDocumentSets($variables);

                foreach ($taxesQuery as $tax) {
                    $taxes[$tax['name'] . ' - (' . $tax['value'] . ')'] = $tax['taxId'];
                }

                foreach ($maturityDatesQuery as $maturityDate) {
                    $maturityDate[$maturityDate['name']] = $maturityDate['maturityDateId'];
                }

                foreach ($measurementUnitsQuery as $measurementUnit) {
                    $measurementUnit[$measurementUnit['name']] = $measurementUnit['measurementUnitId'];
                }

                foreach ($warehousesQuery as $warehouse) {
                    $warehouse[$warehouse['name']] = $warehouse['warehouseId'];
                }

                foreach ($paymentMethodsQuery as $paymentMethod) {
                    $paymentMethod[$paymentMethod['name']] = $paymentMethod['paymentMethodId'];
                }

                foreach ($documentSetsQuery as $documentSet) {
                    $documentSet[$documentSet['name']] = $documentSet['documentSetId'];
                }
            } catch (MoloniApiException $e) {
                return $this->createFormBuilder();
            }
        }

        $yesNoOptions = [
            $this->trans('Yes', 'Modules.Molonies.Settings') => Boolean::YES,
            $this->trans('No', 'Modules.Molonies.Settings') => Boolean::NO,
        ];

        return $this->createFormBuilder()
            // automations
            ->add('CreateAuto', ChoiceType::class, [
                'label' => $this->trans('Create paid documents on Moloni', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $yesNoOptions,
                'data' => helperSettings::get('CreateAuto'),
            ])
            ->add('Stocks', ChoiceType::class, [
                'label' => $this->trans('Synchronize stocks on Moloni', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $yesNoOptions,
                'data' => helperSettings::get('Stocks'),
            ])
            ->add('AddProducts', ChoiceType::class, [
                'label' => $this->trans('Create products on Moloni', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $yesNoOptions,
                'data' => helperSettings::get('AddProducts'),
            ])
            ->add('UpdateArtigos', ChoiceType::class, [
                'label' => $this->trans('Update products on Moloni', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $yesNoOptions,
                'data' => helperSettings::get('UpdateArtigos'),
            ])
            ->add('HooksVariantsUpdate', ChoiceType::class, [
                'label' => $this->trans('Update products with variants', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $yesNoOptions,
                'data' => helperSettings::get('HooksVariantsUpdate'),
            ])
            ->add('HooksAddProducts', ChoiceType::class, [
                'label' => $this->trans('Add products', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $yesNoOptions,
                'data' => helperSettings::get('HooksAddProducts'),
            ])
            ->add('HooksUpdateProducts', ChoiceType::class, [
                'label' => $this->trans('Update products', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $yesNoOptions,
                'data' => helperSettings::get('HooksUpdateProducts'),
            ])
            ->add('HooksUpdateStock', ChoiceType::class, [
                'label' => $this->trans('Update stock', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $yesNoOptions,
                'data' => helperSettings::get('HooksUpdateStock'),
            ])
            ->add('SyncFields', ChoiceType::class, [
                'label' => $this->trans('Fields to sync', 'Modules.Molonies.Settings'),
                'label_attr' => ['class' => 'form-control-label'],
                'multiple' => true,
                'expanded' => true,
                'choices' => [
                    $this->trans('Name', 'Modules.Molonies.Settings') => 'Name',
                    $this->trans('Price', 'Modules.Molonies.Settings') => 'Price',
                    $this->trans('Description', 'Modules.Molonies.Settings') => 'Description',
                    $this->trans('Visibility', 'Modules.Molonies.Settings') => 'Visibility',
                    $this->trans('Stock', 'Modules.Molonies.Settings') => 'Stock',
                    $this->trans('Categories', 'Modules.Molonies.Settings') => 'Categories',
                ],
                'data' => helperSettings::get('SyncFields') ? unserialize(helperSettings::get('SyncFields')) : [],
            ])
            // products
            ->add('Exemption', TextType::class, [
                'label' => $this->trans('Exemption reason', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'required' => false,
                'data' => helperSettings::get('Exemption'),
            ])
            ->add('Shipping', TextType::class, [
                'label' => $this->trans('Shipping exemption reason', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'required' => false,
                'data' => helperSettings::get('Shipping'),
            ])
            ->add('Tax', ChoiceType::class, [
                'label' => $this->trans('Default Tax', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $taxes,
                'data' => helperSettings::get('Tax'),
            ])
            ->add('TaxShipping', ChoiceType::class, [
                'label' => $this->trans('Default Tax Shipping', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $taxes,
                'data' => helperSettings::get('TaxShipping'),
            ])
            ->add('Measure', ChoiceType::class, [
                'label' => $this->trans('Measure unit', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $measurementUnits,
                'data' => helperSettings::get('Measure'),
            ])
            ->add('Maturity', ChoiceType::class, [
                'label' => $this->trans('Maturity date', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $maturityDates,
                'data' => helperSettings::get('Maturity'),
            ])
            ->add('Warehouse', ChoiceType::class, [
                'label' => $this->trans('Default warehouse', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $warehouses,
                'data' => helperSettings::get('Warehouse'),
            ])
            ->add('Payment', ChoiceType::class, [
                'label' => $this->trans('Payment method', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $paymentMethods,
                'data' => helperSettings::get('Payment'),
            ])
            ->add('ClientPrefix', TextType::class, [
                'label' => $this->trans('Client Prefix', 'Modules.Molonies.Settings'),
                'label_attr' => ['class' => 'form-control-label'],
                'attr' => ['onchange' => 'clientPrefixChange()'],
                'required' => false,
                'data' => helperSettings::get('ClientPrefix'),
            ])
            // documents
            ->add('Set', ChoiceType::class, [
                'label' => $this->trans('Document set', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $documentSets,
                'data' => helperSettings::get('Set'),
            ])
            ->add('Type', ChoiceType::class, [
                'label' => $this->trans('Document type', 'Modules.Molonies.Settings'),
                'choices' => $this->getDocumentsTypes(),
                'label_attr' => ['class' => 'form-control-label'],
                'attr' => ['onchange' => 'onStatusChange()'],
                'data' => helperSettings::get('Type'),
            ])
            ->add('Status', ChoiceType::class, [
                'label' => $this->trans('Document status', 'Modules.Molonies.Settings'),
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Draft', 'Modules.Molonies.Settings') => DocumentStatus::DRAFT,
                    $this->trans('Closed', 'Modules.Molonies.Settings') => DocumentStatus::CLOSED,
                ],
                'attr' => ['onchange' => 'onStatusChange()'],
                'data' => helperSettings::get('Status'),
            ])
            ->add('Send', ChoiceType::class, [
                'label' => $this->trans('Shipping information', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $yesNoOptions,
                'data' => helperSettings::get('Send'),
            ])
            ->add('Transport', ChoiceType::class, [
                'label' => $this->trans('Document transport', 'Modules.Molonies.Settings'),
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $yesNoOptions,
                'attr' => ['onchange' => 'onStatusChange2()'],
                'data' => helperSettings::get('Transport'),
            ])
            ->add('Address', ChoiceType::class, [
                'label' => $this->trans('Loading address', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $addresses,
                'data' => helperSettings::get('Address'),
            ])
            ->add('SendEmail', ChoiceType::class, [
                'label' => $this->trans('Send e-mail', 'Modules.Molonies.Settings'),
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $yesNoOptions,
                'attr' => ['onchange' => 'onStatusChange2()'],
                'data' => helperSettings::get('SendEmail'),
            ])
            // save
            ->add('SaveChanges', SubmitType::class, [
                'attr' => ['class' => 'btn-outline-success'],
                'label' => $this->trans('Save changes', 'Modules.Molonies.Settings'),
            ])
            ->setAction($this->generateUrl('moloni_es_settings_save'))
            ->setMethod('POST');
    }
}
