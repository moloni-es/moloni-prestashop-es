<?php
/** @noinspection PhpReturnValueOfMethodIsNeverUsedInspection */

declare(strict_types=1);

namespace Moloni\Form\Settings;

use Moloni\Enums\SyncFields;
use Moloni\Enums\TranslationDomains;
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
            'label' => $this->trans('Synchronize stocks', TranslationDomains::SETTINGS),
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans('Automatic stock synchronization to Moloni', TranslationDomains::SETTINGS),
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
            'label' => $this->trans('Stock destination', TranslationDomains::SETTINGS),
            'choices' => [
                $this->trans('Default warehouse', TranslationDomains::SETTINGS) => 1,
                $this->trans('Warehouses', TranslationDomains::SETTINGS) => $this->options->getWarehouses(),
            ],
            'help' => $this->trans(
                'Stock destination when updating stock and creating products in Moloni',
                TranslationDomains::SETTINGS
            ),
            'placeholder' => $this->trans('Please select an option', TranslationDomains::SETTINGS),
        ]);

        return $this;
    }

    /**
     * @return $this
     */
    private function addProductsToMoloni(): SettingsFormType
    {
        $this->builder->add('addProductsToMoloni', ChoiceType::class, [
            'label' => $this->trans('Create products', TranslationDomains::SETTINGS),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans(
                'When creating a product in Prestashop, the product will be created in Moloni',
                TranslationDomains::SETTINGS
            ),
        ]);

        return $this;
    }

    private function updateProductsToMoloni(): SettingsFormType
    {
        $this->builder->add('updateProductsToMoloni', ChoiceType::class, [
            'label' => $this->trans('Update products', TranslationDomains::SETTINGS),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans(
                'When updating a product in Prestashop, the product will be updated in Moloni',
                TranslationDomains::SETTINGS
            ),
        ]);

        return $this;
    }

    private function syncStockToPrestashop(): SettingsFormType
    {
        $this->builder->add('syncStockToPrestashop', ChoiceType::class, [
            'label' => $this->trans('Synchronize stocks', TranslationDomains::SETTINGS),
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans('Automatic stock synchronization to Prestashop', TranslationDomains::SETTINGS),
        ]);

        return $this;
    }

    private function syncStockToPrestashopWarehouse(): SettingsFormType
    {
        $this->builder
            ->add('syncStockToPrestashopWarehouse', ChoiceType::class, [
                'label' => $this->trans('Stock source', TranslationDomains::SETTINGS),
                'choices' => [
                    $this->trans('Accumulated stock', TranslationDomains::SETTINGS) => 1,
                    $this->trans('Warehouses', TranslationDomains::SETTINGS) => $this->options->getWarehouses(),
                ],
                'help' => $this->trans(
                    'Stock source used when synchronizing stock and creating products in Prestashop',
                    TranslationDomains::SETTINGS
                ),
                'placeholder' => $this->trans('Please select an option', TranslationDomains::SETTINGS),
            ]);

        return $this;
    }

    private function addProductsToPrestashop(): SettingsFormType
    {
        $this->builder->add('addProductsToPrestashop', ChoiceType::class, [
            'label' => $this->trans('Create products', TranslationDomains::SETTINGS),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans(
                'When creating a product in Moloni, the product will be created in Prestashop',
                TranslationDomains::SETTINGS
            ),
        ]);

        return $this;
    }

    private function updateProductsToPrestashop(): SettingsFormType
    {
        $this->builder->add('updateProductsToPrestashop', ChoiceType::class, [
            'label' => $this->trans('Update products', TranslationDomains::SETTINGS),
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans(
                'When updating a product in Moloni, the product will be updated in Prestashop',
                TranslationDomains::SETTINGS
            ),
            'placeholder' => false,

        ]);

        return $this;
    }

    private function productSyncFields(): SettingsFormType
    {
        $this->builder->add('productSyncFields', ChoiceType::class, [
            'label' => $this->trans('Product fields', TranslationDomains::SETTINGS),
            'multiple' => true,
            'expanded' => true,
            'choices' => SyncFields::getSyncFields(),
            'help' => $this->trans(
                'Choose which fields will be synced when a product is updated.',
                TranslationDomains::SETTINGS
            ),
        ]);

        return $this;
    }

    private function orderDateCreated(): SettingsFormType
    {
        $this->builder->add('orderDateCreated', DateType::class, [
            'widget' => 'single_text',
            'required' => false,
            'label' => $this->trans('Orders since', TranslationDomains::SETTINGS),
            'help' => $this->trans('Date used to limit fetch pending orders', TranslationDomains::SETTINGS),
            'placeholder' => false,
        ]);

        return $this;
    }

    private function orderStatusToShow(): SettingsFormType
    {
        $this->builder->add('orderStatusToShow', ChoiceType::class, [
            'label' => $this->trans('Order status', TranslationDomains::SETTINGS),
            'multiple' => true,
            'expanded' => true,
            'choices' => $this->options->getOrderStatus(),
            'help' => $this->trans(
                'Allowed order status to list pending orders and automatic document creation. (if at least one is selected)',
                TranslationDomains::SETTINGS
            ),
        ]);

        return $this;
    }

    private function automaticDocuments(): SettingsFormType
    {
        $this->builder->add('automaticDocuments', ChoiceType::class, [
            'label' => $this->trans('Create paid documents on Moloni', TranslationDomains::SETTINGS),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans('Automatically create document when an order is paid', TranslationDomains::SETTINGS),
        ]);

        return $this;
    }

    private function clientPrefix(): SettingsFormType
    {
        $this->builder->add('clientPrefix', TextType::class, [
            'label' => $this->trans('Client Prefix', TranslationDomains::SETTINGS),
            'help' => $this->trans(
                'If set, created customers will have this prefix in their code (Example: PS)',
                TranslationDomains::SETTINGS
            ),

        ]);

        return $this;
    }

    private function documentSet(): SettingsFormType
    {
        $this->builder
            ->add('documentSet', ChoiceType::class, [
                'label' => $this->trans('Document set', TranslationDomains::SETTINGS),
                'required' => true,
                'choices' => $this->options->getDocumentSets(),
                'placeholder' => $this->trans('Please select an option', TranslationDomains::SETTINGS),

            ]);

        return $this;
    }

    private function documentType(): SettingsFormType
    {
        $this->builder
            ->add('documentType', ChoiceType::class, [
                'label' => $this->trans('Document type', TranslationDomains::SETTINGS),
                'label_attr' => [
                    'required' => true,
                ],
                'required' => true,
                'choices' => $this->options->getDocumentTypes(),
                'placeholder' => $this->trans('Please select an option', TranslationDomains::SETTINGS),

            ]);

        return $this;
    }

    private function documentStatus(): SettingsFormType
    {
        $this->builder
            ->add('documentStatus', ChoiceType::class, [
                'label' => $this->trans('Document status', TranslationDomains::SETTINGS),
                'required' => true,
                'choices' => $this->options->getStatus(),
            ]);

        return $this;
    }

    private function fiscalZoneBasedOn(): SettingsFormType
    {
        $this->builder
            ->add('fiscalZoneBasedOn', ChoiceType::class, [
                'label' => $this->trans('Fiscal zone', TranslationDomains::SETTINGS),
                'choices' => $this->options->getFiscalZoneBasedOn(),
                'help' => $this->trans('Address used to set document fiscal zone', TranslationDomains::SETTINGS),
            ]);

        return $this;
    }

    private function shippingInformation(): SettingsFormType
    {
        $this->builder->add('shippingInformation', ChoiceType::class, [
            'label' => $this->trans('Shipping information', TranslationDomains::SETTINGS),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans('Show shipping information', TranslationDomains::SETTINGS),
        ]);

        return $this;
    }

    private function billOfLading(): SettingsFormType
    {
        $this->builder->add('billOfLading', ChoiceType::class, [
            'label' => $this->trans('Bill of lading', TranslationDomains::SETTINGS),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans('Create order document bill of lading', TranslationDomains::SETTINGS),
        ]);

        return $this;
    }

    private function loadAddress(): SettingsFormType
    {
        $this->builder
            ->add('loadAddress', ChoiceType::class, [
                'label' => $this->trans('Loading address', TranslationDomains::SETTINGS),
                'choices' => $this->options->getAddresses(),
                'help' => $this->trans('Load address used', TranslationDomains::SETTINGS),
            ]);

        return $this;
    }

    private function customloadAddressAddress(): SettingsFormType
    {
        $this->builder->add('customloadAddressAddress', TextType::class, [
            'attr' => [
                'placeholder' => $this->trans('Address', TranslationDomains::SETTINGS),
            ],
        ]);

        return $this;
    }

    private function customloadAddressZipCode(): SettingsFormType
    {
        $this->builder->add('customloadAddressZipCode', TextType::class, [
            'attr' => [
                'placeholder' => $this->trans('Zip-code', TranslationDomains::SETTINGS),
            ],
        ]);

        return $this;
    }

    private function customloadAddressCity(): SettingsFormType
    {
        $this->builder->add('customloadAddressCity', TextType::class, [
            'attr' => [
                'placeholder' => $this->trans('City', TranslationDomains::SETTINGS),
            ],
        ]);

        return $this;
    }

    private function customloadAddressCountry(): SettingsFormType
    {
        $this->builder
            ->add('customloadAddressCountry', ChoiceType::class, [
                'choices' => $this->options->getCountries(),
                'placeholder' => $this->trans('Please select country', TranslationDomains::SETTINGS),
            ]);

        return $this;
    }

    private function sendByEmail(): SettingsFormType
    {
        $this->builder
            ->add('sendByEmail', ChoiceType::class, [
                'label' => $this->trans('Send e-mail', TranslationDomains::SETTINGS),
                'choices' => $this->options->getYesNo(),
                'help' => $this->trans('Sends document to customer via e-mail', TranslationDomains::SETTINGS),
            ]);

        return $this;
    }

    private function useProductNameAndSummaryFrom(): SettingsFormType
    {
        $this->builder
            ->add('useProductNameAndSummaryFrom', ChoiceType::class, [
                'label' => $this->trans('Use product name and summary from', TranslationDomains::SETTINGS),
                'required' => true,
                'choices' => $this->options->getProductInformation(),
                'help' => $this->trans(
                    'The product in the document will use the name and summary from the selected source',
                    TranslationDomains::SETTINGS
                ),
            ]);

        return $this;
    }

    private function exemptionReasonProduct(): SettingsFormType
    {
        $this->builder
            ->add('exemptionReasonProduct', TextType::class, [
                'label' => $this->trans('Product exemption reason', TranslationDomains::SETTINGS),
                'label_attr' => [
                    'popover' => $this->trans(
                        'This exemption reason will be used when a product does not have a defined tax on the order that you are trying to issue',
                        TranslationDomains::SETTINGS
                    ),
                ],
                'label_help_box' => $this->trans('Product exemption reason4', TranslationDomains::SETTINGS),
            ]);

        return $this;
    }

    private function exemptionReasonShipping(): SettingsFormType
    {
        $this->builder
            ->add('exemptionReasonShipping', TextType::class, [
                'label' => $this->trans('Shipping exemption reason', TranslationDomains::SETTINGS),
                'help' => $this->trans(
                    'Will be used if the shipping method has no taxes',
                    TranslationDomains::SETTINGS
                ),
            ]);

        return $this;
    }

    private function measurementUnit(): SettingsFormType
    {
        $this->builder
            ->add('measurementUnit', ChoiceType::class, [
                'label' => $this->trans('Measure unit', TranslationDomains::SETTINGS),
                'required' => true,
                'choices' => $this->options->getMeasurementUnits(),
                'help' => $this->trans('Created products will use this measurement unit', TranslationDomains::SETTINGS),
                'placeholder' => $this->trans('Please select an option', TranslationDomains::SETTINGS),
            ]);

        return $this;
    }

    private function documentWarehouse(): SettingsFormType
    {
        $this->builder->add('documentWarehouse', ChoiceType::class, [
            'label' => $this->trans('Document warehouse', TranslationDomains::SETTINGS),
            'choices' => $this->options->getWarehouses(),
            'help' => $this->trans('Warehouse used in documents', TranslationDomains::SETTINGS),
            'placeholder' => $this->trans('Please select an option', TranslationDomains::SETTINGS),
        ]);

        return $this;
    }

    private function alertEmail(): SettingsFormType
    {
        $this->builder->add('alertEmail', EmailType::class, [
            'label' => $this->trans('E-mail address', TranslationDomains::SETTINGS),
            'help' => $this->trans(
                'E-mail used to send notifications in case of plugin failures',
                TranslationDomains::SETTINGS
            ),
            'attr' => [
                'placeholder' => $this->trans('example@email.com', TranslationDomains::SETTINGS),
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
