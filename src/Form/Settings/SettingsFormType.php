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

    private $transDomain = 'Modules.Molonies.Settings';

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
            'label' => $this->trans('Synchronize stocks', $this->transDomain),
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans('Automatic stock synchronization to Moloni', $this->transDomain),
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
            'label' => $this->trans('Stock destination', $this->transDomain),
            'choices' => [
                $this->trans('Default warehouse', $this->transDomain) => 1,
                $this->trans('Warehouses', $this->transDomain) => $this->options->getWarehouses(),
            ],
            'help' => $this->trans(
                'Stock destination when updating stock and creating products in Moloni',
                $this->transDomain
            ),
            'placeholder' => $this->trans('Please select an option', $this->transDomain),
        ]);

        return $this;
    }

    /**
     * @return $this
     */
    private function addProductsToMoloni(): SettingsFormType
    {
        $this->builder->add('addProductsToMoloni', ChoiceType::class, [
            'label' => $this->trans('Create products', $this->transDomain),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans(
                'When creating a product in Prestashop, the product will be created in Moloni',
                $this->transDomain
            ),
        ]);

        return $this;
    }

    private function updateProductsToMoloni(): SettingsFormType
    {
        $this->builder->add('updateProductsToMoloni', ChoiceType::class, [
            'label' => $this->trans('Update products', $this->transDomain),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans(
                'When updating a product in Prestashop, the product will be updated in Moloni',
                $this->transDomain
            ),
        ]);

        return $this;
    }

    private function syncStockToPrestashop(): SettingsFormType
    {
        $this->builder->add('syncStockToPrestashop', ChoiceType::class, [
            'label' => $this->trans('Synchronize stocks', $this->transDomain),
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans('Automatic stock synchronization to Prestashop', $this->transDomain),
        ]);

        return $this;
    }

    private function syncStockToPrestashopWarehouse(): SettingsFormType
    {
        $this->builder
            ->add('syncStockToPrestashopWarehouse', ChoiceType::class, [
                'label' => $this->trans('Stock source', $this->transDomain),
                'choices' => [
                    $this->trans('Accumulated stock', $this->transDomain) => 1,
                    $this->trans('Warehouses', $this->transDomain) => $this->options->getWarehouses(),
                ],
                'help' => $this->trans(
                    'Stock source used when synchronizing stock and creating products in Prestashop',
                    $this->transDomain
                ),
                'placeholder' => $this->trans('Please select an option', $this->transDomain),
            ]);

        return $this;
    }

    private function addProductsToPrestashop(): SettingsFormType
    {
        $this->builder->add('addProductsToPrestashop', ChoiceType::class, [
            'label' => $this->trans('Create products', $this->transDomain),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans(
                'When creating a product in Moloni, the product will be created in Prestashop',
                $this->transDomain
            ),
        ]);

        return $this;
    }

    private function updateProductsToPrestashop(): SettingsFormType
    {
        $this->builder->add('updateProductsToPrestashop', ChoiceType::class, [
            'label' => $this->trans('Update products', $this->transDomain),
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans(
                'When updating a product in Moloni, the product will be updated in Prestashop',
                $this->transDomain
            ),
            'placeholder' => false,

        ]);

        return $this;
    }

    private function productSyncFields(): SettingsFormType
    {
        $this->builder->add('productSyncFields', ChoiceType::class, [
            'label' => $this->trans('Products fields', $this->transDomain),
            'multiple' => true,
            'expanded' => true,
            'choices' => SyncFields::getSyncFields(),
            'help' => $this->trans('Choose which fields will be synced when a product is updated.', $this->transDomain),
        ]);

        return $this;
    }

    private function orderDateCreated(): SettingsFormType
    {
        $this->builder->add('orderDateCreated', DateType::class, [
            'widget' => 'single_text',
            'required' => false,
            'label' => $this->trans('Orders since', $this->transDomain),
            'help' => $this->trans('Date used to limit fetch pending orders', $this->transDomain),
            'placeholder' => false,
        ]);

        return $this;
    }

    private function orderStatusToShow(): SettingsFormType
    {
        $this->builder->add('orderStatusToShow', ChoiceType::class, [
            'label' => $this->trans('Order status', $this->transDomain),
            'multiple' => true,
            'expanded' => true,
            'choices' => $this->options->getOrderStatus(),
            'help' => $this->trans(
                'Allowed order status to list pending orders and automatic document creation. (if at least one is selected)',
                $this->transDomain
            ),
        ]);

        return $this;
    }

    private function automaticDocuments(): SettingsFormType
    {
        $this->builder->add('automaticDocuments', ChoiceType::class, [
            'label' => $this->trans('Create paid documents on Moloni', $this->transDomain),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans('Automatically create document when an order is paid', $this->transDomain),
        ]);

        return $this;
    }

    private function clientPrefix(): SettingsFormType
    {
        $this->builder->add('clientPrefix', TextType::class, [
            'label' => $this->trans('Client Prefix', $this->transDomain),
            'help' => $this->trans(
                'If set, created customers will have this prefix in their code (Example: PS)',
                $this->transDomain
            ),

        ]);

        return $this;
    }

    private function documentSet(): SettingsFormType
    {
        $this->builder
            ->add('documentSet', ChoiceType::class, [
                'label' => $this->trans('Document set', $this->transDomain),
                'required' => true,
                'choices' => $this->options->getDocumentSets(),
                'placeholder' => $this->trans('Please select an option', $this->transDomain),

            ]);

        return $this;
    }

    private function documentType(): SettingsFormType
    {
        $this->builder
            ->add('documentType', ChoiceType::class, [
                'label' => $this->trans('Document type', $this->transDomain),
                'label_attr' => [
                    'required' => true,
                ],
                'required' => true,
                'choices' => $this->options->getDocumentTypes(),
                'placeholder' => $this->trans('Please select an option', $this->transDomain),

            ]);

        return $this;
    }

    private function documentStatus(): SettingsFormType
    {
        $this->builder
            ->add('documentStatus', ChoiceType::class, [
                'label' => $this->trans('Document status', $this->transDomain),
                'required' => true,
                'choices' => $this->options->getStatus(),
            ]);

        return $this;
    }

    private function fiscalZoneBasedOn(): SettingsFormType
    {
        $this->builder
            ->add('fiscalZoneBasedOn', ChoiceType::class, [
                'label' => $this->trans('Fiscal zone', $this->transDomain),
                'choices' => $this->options->getFiscalZoneBasedOn(),
                'help' => $this->trans('Address used to set document fiscal zone', $this->transDomain),
            ]);

        return $this;
    }

    private function shippingInformation(): SettingsFormType
    {
        $this->builder->add('shippingInformation', ChoiceType::class, [
            'label' => $this->trans('Shipping information', $this->transDomain),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans('Show shipping information', $this->transDomain),
        ]);

        return $this;
    }

    private function billOfLading(): SettingsFormType
    {
        $this->builder->add('billOfLading', ChoiceType::class, [
            'label' => $this->trans('Bill of lading', $this->transDomain),
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans('Create order document bill of lading', $this->transDomain),
        ]);

        return $this;
    }

    private function loadAddress(): SettingsFormType
    {
        $this->builder
            ->add('loadAddress', ChoiceType::class, [
                'label' => $this->trans('Loading address', $this->transDomain),
                'choices' => $this->options->getAddresses(),
                'help' => $this->trans('Load address used', $this->transDomain),
            ]);

        return $this;
    }

    private function customloadAddressAddress(): SettingsFormType
    {
        $this->builder->add('customloadAddressAddress', TextType::class, [
            'attr' => [
                'placeholder' => $this->trans('Address', $this->transDomain),
            ],
        ]);

        return $this;
    }

    private function customloadAddressZipCode(): SettingsFormType
    {
        $this->builder->add('customloadAddressZipCode', TextType::class, [
            'attr' => [
                'placeholder' => $this->trans('Zip-code', $this->transDomain),
            ],
        ]);

        return $this;
    }

    private function customloadAddressCity(): SettingsFormType
    {
        $this->builder->add('customloadAddressCity', TextType::class, [
            'attr' => [
                'placeholder' => $this->trans('City', $this->transDomain),
            ],
        ]);

        return $this;
    }

    private function customloadAddressCountry(): SettingsFormType
    {
        $this->builder
            ->add('customloadAddressCountry', ChoiceType::class, [
                'choices' => $this->options->getCountries(),
                'placeholder' => $this->trans('Please select country', $this->transDomain),
            ]);

        return $this;
    }

    private function sendByEmail(): SettingsFormType
    {
        $this->builder
            ->add('sendByEmail', ChoiceType::class, [
                'label' => $this->trans('Send e-mail', $this->transDomain),
                'choices' => $this->options->getYesNo(),
                'help' => $this->trans('Sends document to customer via e-mail', $this->transDomain),
            ]);

        return $this;
    }

    private function useProductNameAndSummaryFrom(): SettingsFormType
    {
        $this->builder
            ->add('useProductNameAndSummaryFrom', ChoiceType::class, [
                'label' => $this->trans('Use product name and summary from', $this->transDomain),
                'required' => true,
                'choices' => $this->options->getProductInformation(),
                'help' => $this->trans(
                    'The product in the document will use the name and summary from the selected source',
                    $this->transDomain
                ),
            ]);

        return $this;
    }

    private function exemptionReasonProduct(): SettingsFormType
    {
        $this->builder
            ->add('exemptionReasonProduct', TextType::class, [
                'label' => $this->trans('Product exemption reason', $this->transDomain),
                'label_subtitle' => $this->trans('Product exemption reason1', $this->transDomain),
                'label_tag_name' => $this->trans('Product exemption reason2', $this->transDomain),
                'help' => $this->trans("Fuck this", $this->transDomain),
                'label_attr' => [
                    'popover' => $this->trans('Tooltip me I\'m famous !', $this->transDomain),
                ],
                'label_help_box' => $this->trans('Product exemption reason4', $this->transDomain),
            ]);

        return $this;
    }

    private function exemptionReasonShipping(): SettingsFormType
    {
        $this->builder
            ->add('exemptionReasonShipping', TextType::class, [
                'label' => $this->trans('Shipping exemption reason', $this->transDomain),
                'help' => $this->trans('Will be used if the shipping method has no taxes', $this->transDomain),
            ]);

        return $this;
    }

    private function measurementUnit(): SettingsFormType
    {
        $this->builder
            ->add('measurementUnit', ChoiceType::class, [
                'label' => $this->trans('Measure unit', $this->transDomain),
                'required' => true,
                'choices' => $this->options->getMeasurementUnits(),
                'help' => $this->trans('Created products will use this measurement unit', $this->transDomain),
                'placeholder' => $this->trans('Please select an option', $this->transDomain),
            ]);

        return $this;
    }

    private function documentWarehouse(): SettingsFormType
    {
        $this->builder->add('documentWarehouse', ChoiceType::class, [
            'label' => $this->trans('Document warehouse', $this->transDomain),
            'choices' => $this->options->getWarehouses(),
            'help' => $this->trans('Warehouse used in documents', $this->transDomain),
            'placeholder' => $this->trans('Please select an option', $this->transDomain),
        ]);

        return $this;
    }

    private function alertEmail(): SettingsFormType
    {
        $this->builder->add('alertEmail', EmailType::class, [
            'label' => $this->trans('E-mail address', $this->transDomain),
            'help' => $this->trans('E-mail used to send notifications in case of plugin failures', $this->transDomain),
            'attr' => [
                'placeholder' => $this->trans('example@email.com', $this->transDomain),
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
