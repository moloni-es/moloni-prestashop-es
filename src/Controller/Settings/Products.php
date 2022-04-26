<?php

namespace Moloni\Controller\Settings;

use Db;
use Moloni\Api\MaturityDates;
use Moloni\Api\MeasurementUnits;
use Moloni\Api\PaymentMethods;
use Moloni\Api\Taxes;
use Moloni\Api\Warehouses;
use Moloni\Controller\General;
use Moloni\Models\Moloni;
use Moloni\Models\Settings;
use PrestaShopDatabaseException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class Products extends General
{
    /**
     * Checks for valid token and login, builds form and displays template
     *
     * @return null returns the template with form
     *
     * @throws PrestaShopDatabaseException
     */
    public function display()
    {
        if (!$this->checkTokenRedirect()) {
            return $this->redirectLogin();
        }

        $form = $this->buildFormArtigo();

        $aux = 'products';

        $view = '@Modules/moloniprestashopes/src/View/Templates/Admin/Settings/Products.twig';

        return $this->render($view, [
            'settingsForm' => $form->createView(),
            'tabActive' => $aux,
        ]);
    }

    /**
     * Builds form with data from API if exists
     *
     * @return FormInterface $form form with all fields
     *
     * @throws PrestaShopDatabaseException
     */
    public function buildFormArtigo()
    {
        $variables = ['companyId' => (int) Moloni::get('company_id'), 'options' => null];
        $taxes = Taxes::queryTaxes($variables);
        $taxesData = $taxes;
        $countTaxesData = count($taxesData);

        $choicesTax[$this->trans(
            'Use prestashop value (recommended)',
            'Modules.Moloniprestashopes.Settings'
        )] = 'LetPresta';
        $choicesTax[$this->trans('Exempt', 'Modules.Moloniprestashopes.Settings')] = 'isento';

        for ($i = 0; $i < $countTaxesData; ++$i) {
            $taxKeys = '' . $taxesData[$i]['name'] . ' - ' . $taxesData[$i]['value'] . '';
            $choicesTax[$taxKeys] = $taxesData[$i]['taxId'];
        }

        $variables = ['companyId' => (int) Moloni::get('company_id'), 'options' => null];
        $units = MeasurementUnits::queryMeasurementUnits($variables);
        $unitData = $units;
        $countUnitData = count($unitData);

        for ($i = 0; $i < $countUnitData; ++$i) {
            $choicesUnit[$unitData[$i]['name']] = $unitData[$i]['measurementUnitId'];
        }
        $choicesUnit[$this->trans('Use prestashop value', 'Modules.Moloniprestashopes.Settings')] = 'LetPresta';

        $variables = ['companyId' => (int) Moloni::get('company_id'), 'options' => null];
        $maturity = MaturityDates::queryMaturityDates($variables);
        $maturityData = $maturity;
        $countMaturityData = count($maturityData);

        for ($i = 0; $i < $countMaturityData; ++$i) {
            $choicesMaturity[$maturityData[$i]['name']] = $maturityData[$i]['maturityDateId'];
        }

        $variables = ['companyId' => (int) Moloni::get('company_id'), 'options' => null];
        $warehouse = Warehouses::queryWarehouses($variables);
        $warehouseData = $warehouse;
        $countWarehouseData = count($warehouseData);

        for ($i = 0; $i < $countWarehouseData; ++$i) {
            $choicesWarehouse[$warehouseData[$i]['name']] = $warehouseData[$i]['warehouseId'];
        }

        $variables = ['companyId' => (int) Moloni::get('company_id'), 'options' => null];
        $paymentMethod = PaymentMethods::queryPaymentMethods($variables);
        $paymentMethodData = $paymentMethod;
        $countPaymentData = count($paymentMethodData);

        for ($i = 0; $i < $countPaymentData; ++$i) {
            $choicesPaymentMethod[$paymentMethodData[$i]['name']] = $paymentMethodData[$i]['paymentMethodId'];
        }

        return $this->createFormBuilder()
            ->add('Exemption', TextType::class, [
                'label' => $this->trans('Exemption reason', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'required' => false,
                'data' => Settings::get('Exemption') !== false ? Settings::get('Exemption') : null,
            ])
            ->add('Shipping', TextType::class, [
                'label' => $this->trans('Shipping exemption reason', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'required' => false,
                'data' => Settings::get('Shipping') !== false ? Settings::get('Shipping') : null,
            ])
            ->add('Tax', ChoiceType::class, [
                'label' => $this->trans('Default Tax', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $choicesTax,
                'data' => Settings::get('Tax') !== false ? Settings::get('Tax') : null,
            ])
            ->add('TaxShipping', ChoiceType::class, [
                'label' => $this->trans('Default Tax Shipping', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $choicesTax,
                'data' => Settings::get('TaxShipping') !== false ? Settings::get('TaxShipping') : null,
            ])
            ->add('Measure', ChoiceType::class, [
                'label' => $this->trans('Measure unit', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $choicesUnit,
                'data' => Settings::get('Measure') !== false ? Settings::get('Measure') : null,
            ])
            ->add('Maturity', ChoiceType::class, [
                'label' => $this->trans('Maturity date', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $choicesMaturity,
                'data' => Settings::get('Maturity') !== false ? Settings::get('Maturity') : null,
            ])
            ->add('Warehouse', ChoiceType::class, [
                'label' => $this->trans('Default warehouse', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $choicesWarehouse,
                'data' => Settings::get('Warehouse') !== false ? Settings::get('Warehouse') : null,
            ])
            ->add('Payment', ChoiceType::class, [
                'label' => $this->trans('Payment method', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $choicesPaymentMethod,
                'data' => Settings::get('Payment') !== false ? Settings::get('Payment') : null,
            ])
            ->add('ClientPrefix', TextType::class, [
                'label' => $this->trans('Client Prefix', 'Modules.Moloniprestashopes.Settings'),
                'label_attr' => ['class' => 'form-control-label'],
                'attr' => ['onchange' => 'clientPrefixChange()'],
                'required' => false,
                'data' => Settings::get('ClientPrefix') !== false ? Settings::get('ClientPrefix') : 'PS',
            ])
            ->add('SaveChanges', SubmitType::class, [
                'attr' => ['class' => 'btn-outline-success'],
                'label' => $this->trans('Save changes', 'Modules.Moloniprestashopes.Settings'),
            ])
            ->setAction($this->generateUrl('moloni_es_settings_products_submit'))
            ->setMethod('POST')
            ->getForm();
    }

    /**
     * Validates submited form, inserts on DB if no DB records, or updates if exist
     *
     * @param Request $request POST Request
     *
     * @return null returns the template with form
     *
     * @throws PrestaShopDatabaseException
     */
    public function submitted(Request $request)
    {
        $form = $this->buildFormArtigo();

        if ($request !== null && $request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $submitData = $form->getData();

                $dbPresta = Db::getInstance();

                foreach ($submitData as $label => $value) {
                    $setting = $dbPresta->getRow(
                        'SELECT * FROM ' . _DB_PREFIX_ . 'moloni_settings WHERE label = "' . $label
                        . '" AND store_id=1'
                    );

                    if (empty($setting)) {
                        $dbPresta->insert('moloni_settings', [
                            'store_id' => (int) '1',
                            'label' => pSQL($label),
                            'value' => pSQL($value),
                        ]);
                    } else {
                        $dbPresta->update('moloni_settings', [
                            'value' => pSQL($value),
                        ], 'store_id = 1 AND label="' . $label . '"');
                    }
                }

                Settings::fillCache();

                $this->addFlash('success', $this->trans(
                    'Settings updated.',
                    'Modules.Moloniprestashopes.Success'
                ));

                return $this->redirectSettingsProducts();
            }

            $this->addFlash('warning', $this->trans(
                'Form not valid!!',
                'Modules.Moloniprestashopes.Errors'
            ));

            return $this->redirectSettingsProducts();
        }

        $this->addFlash('warning', $this->trans(
            'Form not correctly sent!!',
            'Modules.Moloniprestashopes.Errors'
        ));

        return $this->redirectSettingsProducts();
    }
}
