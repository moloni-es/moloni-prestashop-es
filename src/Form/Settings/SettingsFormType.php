<?php
/** @noinspection PhpReturnValueOfMethodIsNeverUsedInspection */

declare(strict_types=1);

namespace Moloni\Form\Settings;

use Moloni\Enums\SyncFields;
use Moloni\Exceptions\MoloniApiException;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This class is used only to define the available option
 */
class SettingsFormType extends TranslatorAwareType
{
    /** @var FormBuilderInterface */
    private $builder;

    /** @var SettingsFormDataProvider */
    private $options;

    public function __construct(TranslatorInterface $translator, array $locales, SettingsFormDataProvider $dataProvider)
    {
        $this->options = $dataProvider;

        parent::__construct($translator, $locales);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->builder = $builder;

        try {
            $this->options->loadMoloniAvailableSettings();
        } catch (MoloniApiException $e) {
        }

        $this
            ->setDocumentsTab()
            ->setOrdersTab()
            ->setAutomationTab()
            ->setAdvancedTab()
            ->saveButton();

        return $this->builder;
    }

    /**
     * @return $this
     */
    private function syncStockToMoloni(): SettingsFormType
    {
        $this->builder->add('syncStockToMoloni', ChoiceType::class, [
            'label' => $this->trans('Synchronize stocks', "Modules.Molonies.Settings"),
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans('Automatic stock synchronization to Moloni', "Modules.Molonies.Settings"),
            'placeholder' => false,
        ]);

        return $this;
    }

    /**
     * @return $this
     */
    private function syncStockToMoloniWarehouse(): SettingsFormType
    {
        $this->builder->add('syncStockToMoloniWarehouse', ChoiceType::class, [
            'label' => $this->trans('Stock destination', "Modules.Molonies.Settings"),
            'choices' => [
                $this->trans('Default warehouse', "Modules.Molonies.Settings") => 1,
                $this->trans('Warehouses', "Modules.Molonies.Settings") => $this->options->getWarehouses(),
            ],
            'help' => $this->trans(
                'Stock destination when updating stock and creating products in Moloni',
                "Modules.Molonies.Settings"
            ),
            'placeholder' => $this->trans('Please select an option', "Modules.Molonies.Settings"),
        ]);

        return $this;
    }

    /**
     * @return $this
     */
    private function addProductsToMoloni(): SettingsFormType
    {
        $this->builder->add('addProductsToMoloni', ChoiceType::class, [
            'label' => $this->trans('Create products', "Modules.Molonies.Settings"),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans(
                'When creating a product in Prestashop, the product will be created in Moloni',
                "Modules.Molonies.Settings"
            ),
        ]);

        return $this;
    }

    private function updateProductsToMoloni(): SettingsFormType
    {
        $this->builder->add('updateProductsToMoloni', ChoiceType::class, [
            'label' => $this->trans('Update products', "Modules.Molonies.Settings"),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans(
                'When updating a product in Prestashop, the product will be updated in Moloni',
                "Modules.Molonies.Settings"
            ),
        ]);

        return $this;
    }

    private function syncStockToPrestashop(): SettingsFormType
    {
        $this->builder->add('syncStockToPrestashop', ChoiceType::class, [
            'label' => $this->trans('Synchronize stocks', "Modules.Molonies.Settings"),
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans('Automatic stock synchronization to Prestashop', "Modules.Molonies.Settings"),
        ]);

        return $this;
    }

    private function syncStockToPrestashopWarehouse(): SettingsFormType
    {
        $this->builder
            ->add('syncStockToPrestashopWarehouse', ChoiceType::class, [
                'label' => $this->trans('Stock source', "Modules.Molonies.Settings"),
                'choices' => [
                    $this->trans('Accumulated stock', "Modules.Molonies.Settings") => 1,
                    $this->trans('Warehouses', "Modules.Molonies.Settings") => $this->options->getWarehouses(),
                ],
                'help' => $this->trans(
                    'Stock source used when synchronizing stock and creating products in Prestashop',
                    "Modules.Molonies.Settings"
                ),
                'placeholder' => $this->trans('Please select an option', "Modules.Molonies.Settings"),
            ]);

        return $this;
    }

    private function addProductsToPrestashop(): SettingsFormType
    {
        $this->builder->add('addProductsToPrestashop', ChoiceType::class, [
            'label' => $this->trans('Create products', "Modules.Molonies.Settings"),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans(
                'When creating a product in Moloni, the product will be created in Prestashop',
                "Modules.Molonies.Settings"
            ),
        ]);

        return $this;
    }

    private function updateProductsToPrestashop(): SettingsFormType
    {
        $this->builder->add('updateProductsToPrestashop', ChoiceType::class, [
            'label' => $this->trans('Update products', "Modules.Molonies.Settings"),
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans(
                'When updating a product in Moloni, the product will be updated in Prestashop',
                "Modules.Molonies.Settings"
            ),
            'placeholder' => false,

        ]);

        return $this;
    }

    private function productSyncFields(): SettingsFormType
    {
        $this->builder->add('productSyncFields', ChoiceType::class, [
            'label' => $this->trans('Product fields', "Modules.Molonies.Settings"),
            'multiple' => true,
            'expanded' => true,
            'choices' => SyncFields::getSyncFields(),
            'help' => $this->trans(
                'Choose which fields will be synced when a product is updated.',
                "Modules.Molonies.Settings"
            ),
        ]);

        return $this;
    }

    private function orderDateCreated(): SettingsFormType
    {
        $this->builder->add('orderDateCreated', DateType::class, [
            'widget' => 'single_text',
            'required' => false,
            'label' => $this->trans('Orders since', "Modules.Molonies.Settings"),
            'help' => $this->trans('Date used to limit fetch pending orders', "Modules.Molonies.Settings"),
            'placeholder' => false,
        ]);

        return $this;
    }

    private function orderStatusToShow(): SettingsFormType
    {
        $this->builder->add('orderStatusToShow', ChoiceType::class, [
            'label' => $this->trans('Order status', "Modules.Molonies.Settings"),
            'multiple' => true,
            'expanded' => true,
            'choices' => $this->options->getOrderStatus(),
            'help' => $this->trans(
                'Allowed order status to list pending orders and automatic document creation. (if at least one is selected)',
                "Modules.Molonies.Settings"
            ),
        ]);

        return $this;
    }

    private function automaticDocuments(): SettingsFormType
    {
        $this->builder->add('automaticDocuments', ChoiceType::class, [
            'label' => $this->trans('Create paid documents on Moloni', "Modules.Molonies.Settings"),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans('Automatically create document when an order is paid', "Modules.Molonies.Settings"),
        ]);

        return $this;
    }

    private function clientPrefix(): SettingsFormType
    {
        $this->builder->add('clientPrefix', TextType::class, [
            'label' => $this->trans('Client Prefix', "Modules.Molonies.Settings"),
            'help' => $this->trans(
                'If set, created customers will have this prefix in their code (Example: PS)',
                "Modules.Molonies.Settings"
            ),

        ]);

        return $this;
    }

    private function documentSet(): SettingsFormType
    {
        $this->builder
            ->add('documentSet', ChoiceType::class, [
                'label' => $this->trans('Document set', "Modules.Molonies.Settings"),
                'required' => true,
                'choices' => $this->options->getDocumentSets(),
                'placeholder' => $this->trans('Please select an option', "Modules.Molonies.Settings"),

            ]);

        return $this;
    }

    private function documentType(): SettingsFormType
    {
        $this->builder
            ->add('documentType', ChoiceType::class, [
                'label' => $this->trans('Document type', "Modules.Molonies.Settings"),
                'label_attr' => [
                    'required' => true,
                ],
                'required' => true,
                'choices' => $this->options->getDocumentTypes(),
                'placeholder' => $this->trans('Please select an option', "Modules.Molonies.Settings"),

            ]);

        return $this;
    }

    private function documentStatus(): SettingsFormType
    {
        $this->builder
            ->add('documentStatus', ChoiceType::class, [
                'label' => $this->trans('Document status', "Modules.Molonies.Settings"),
                'required' => true,
                'choices' => $this->options->getStatus(),
            ]);

        return $this;
    }

    private function fiscalZoneBasedOn(): SettingsFormType
    {
        $this->builder
            ->add('fiscalZoneBasedOn', ChoiceType::class, [
                'label' => $this->trans('Fiscal zone', "Modules.Molonies.Settings"),
                'choices' => $this->options->getFiscalZoneBasedOn(),
                'help' => $this->trans('Address used to set document fiscal zone', "Modules.Molonies.Settings"),
            ]);

        return $this;
    }

    private function shippingInformation(): SettingsFormType
    {
        $this->builder->add('shippingInformation', ChoiceType::class, [
            'label' => $this->trans('Shipping information', "Modules.Molonies.Settings"),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans('Show shipping information', "Modules.Molonies.Settings"),
        ]);

        return $this;
    }

    private function billOfLading(): SettingsFormType
    {
        $this->builder->add('billOfLading', ChoiceType::class, [
            'label' => $this->trans('Bill of lading', "Modules.Molonies.Settings"),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans('Create order document bill of lading', "Modules.Molonies.Settings"),
        ]);

        return $this;
    }

    private function loadAddress(): SettingsFormType
    {
        $this->builder
            ->add('loadAddress', ChoiceType::class, [
                'label' => $this->trans('Loading address', "Modules.Molonies.Settings"),
                'choices' => $this->options->getAddresses(),
                'help' => $this->trans('Load address used', "Modules.Molonies.Settings"),
            ]);

        return $this;
    }

    private function customloadAddressAddress(): SettingsFormType
    {
        $this->builder->add('customloadAddressAddress', TextType::class, [
            'attr' => [
                'placeholder' => $this->trans('Address', "Modules.Molonies.Settings"),
            ],
        ]);

        return $this;
    }

    private function customloadAddressZipCode(): SettingsFormType
    {
        $this->builder->add('customloadAddressZipCode', TextType::class, [
            'attr' => [
                'placeholder' => $this->trans('Zip-code', "Modules.Molonies.Settings"),
            ],
        ]);

        return $this;
    }

    private function customloadAddressCity(): SettingsFormType
    {
        $this->builder->add('customloadAddressCity', TextType::class, [
            'attr' => [
                'placeholder' => $this->trans('City', "Modules.Molonies.Settings"),
            ],
        ]);

        return $this;
    }

    private function customloadAddressCountry(): SettingsFormType
    {
        $this->builder
            ->add('customloadAddressCountry', ChoiceType::class, [
                'choices' => $this->options->getCountries(),
                'placeholder' => $this->trans('Please select country', "Modules.Molonies.Settings"),
            ]);

        return $this;
    }

    private function sendByEmail(): SettingsFormType
    {
        $this->builder
            ->add('sendByEmail', ChoiceType::class, [
                'label' => $this->trans('Send e-mail', "Modules.Molonies.Settings"),
                'choices' => $this->options->getYesNo(),
                'help' => $this->trans('Sends document to customer via e-mail', "Modules.Molonies.Settings"),
            ]);

        return $this;
    }

    private function useProductNameAndSummaryFrom(): SettingsFormType
    {
        $this->builder
            ->add('useProductNameAndSummaryFrom', ChoiceType::class, [
                'label' => $this->trans('Use product name and summary from', "Modules.Molonies.Settings"),
                'required' => true,
                'choices' => $this->options->getProductInformation(),
                'help' => $this->trans(
                    'The product in the document will use the name and summary from the selected source',
                    "Modules.Molonies.Settings"
                ),
            ]);

        return $this;
    }

    private function exemptionReasonProduct(): SettingsFormType
    {
        $this->builder
            ->add('exemptionReasonProduct', TextType::class, [
                'label' => $this->trans('Product exemption reason', "Modules.Molonies.Settings"),
                'label_attr' => [
                    'popover' => $this->trans(
                        'This exemption reason will be used when a product does not have a defined tax on the order that you are trying to issue',
                        "Modules.Molonies.Settings"
                    ),
                ],
                'label_help_box' => $this->trans('Product exemption reason4', "Modules.Molonies.Settings"),
            ]);

        return $this;
    }

    private function exemptionReasonShipping(): SettingsFormType
    {
        $this->builder
            ->add('exemptionReasonShipping', TextType::class, [
                'label' => $this->trans('Shipping exemption reason', "Modules.Molonies.Settings"),
                'help' => $this->trans(
                    'Will be used if the shipping method has no taxes',
                    "Modules.Molonies.Settings"
                ),
            ]);

        return $this;
    }

    private function measurementUnit(): SettingsFormType
    {
        $this->builder
            ->add('measurementUnit', ChoiceType::class, [
                'label' => $this->trans('Measure unit', "Modules.Molonies.Settings"),
                'required' => true,
                'choices' => $this->options->getMeasurementUnits(),
                'help' => $this->trans('Created products will use this measurement unit', "Modules.Molonies.Settings"),
                'placeholder' => $this->trans('Please select an option', "Modules.Molonies.Settings"),
            ]);

        return $this;
    }

    private function documentWarehouse(): SettingsFormType
    {
        $this->builder->add('documentWarehouse', ChoiceType::class, [
            'label' => $this->trans('Document warehouse', "Modules.Molonies.Settings"),
            'choices' => $this->options->getWarehouses(),
            'help' => $this->trans('Warehouse used in documents', "Modules.Molonies.Settings"),
            'placeholder' => $this->trans('Please select an option', "Modules.Molonies.Settings"),
        ]);

        return $this;
    }

    private function alertEmail(): SettingsFormType
    {
        $this->builder->add('alertEmail', EmailType::class, [
            'label' => $this->trans('E-mail address', "Modules.Molonies.Settings"),
            'help' => $this->trans(
                'E-mail used to send notifications in case of plugin failures',
                "Modules.Molonies.Settings"
            ),
            'attr' => [
                'placeholder' => $this->trans('example@email.com', "Modules.Molonies.Settings"),
            ],
        ]);

        return $this;
    }

    private function saveButton(): SettingsFormType
    {
        $this->builder->add('saveChanges', SubmitType::class, [
            'attr' => ['class' => 'btn-primary'],
            'label' => 'Save',
        ]);

        return $this;
    }

    private function setDocumentsTab(): SettingsFormType
    {
        $this
            ->documentSet()
            ->documentType()
            ->documentStatus()
            ->fiscalZoneBasedOn()
            ->shippingInformation()
            ->automaticDocuments()
            ->useProductNameAndSummaryFrom()
            ->exemptionReasonProduct()
            ->exemptionReasonShipping()
            ->measurementUnit()
            ->documentWarehouse()
            ->clientPrefix()
            // When document stauts is closed
            ->billOfLading()
            ->loadAddress()
            ->customloadAddressAddress()
            ->customloadAddressZipCode()
            ->customloadAddressCity()
            ->customloadAddressCountry()
            ->sendByEmail();

        return $this;
    }

    private function setOrdersTab(): SettingsFormType
    {
        $this
            ->orderDateCreated()
            ->orderStatusToShow();

        return $this;
    }

    private function setAutomationTab(): SettingsFormType
    {
        $this
            ->syncStockToMoloni()
            ->syncStockToMoloniWarehouse()
            ->addProductsToMoloni()
            ->updateProductsToMoloni()
            ->syncStockToPrestashop()
            ->syncStockToPrestashopWarehouse()
            ->addProductsToPrestashop()
            ->updateProductsToPrestashop()
            ->productSyncFields();

        return $this;
    }

    private function setAdvancedTab(): SettingsFormType
    {
        $this->alertEmail();

        return $this;
    }
}
