<?php
/** @noinspection PhpReturnValueOfMethodIsNeverUsedInspection */

declare(strict_types=1);

namespace Moloni\Form;

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
            'label' => 'Synchronize stocks',
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => 'Automatic stock synchronization to Moloni',
            'placeholder' => false,
            'translation_domain' => $this->transDomain,
        ]);

        return $this;
    }

    /**
     * @return $this
     */
    private function syncStockToMoloniWarehouse(): SettingsFormType
    {
        $this->builder->add('syncStockToMoloniWarehouse', ChoiceType::class, [
            'label' => 'Stock destination',
            'required' => false,
            'choices' => [
                'Default warehouse' => 1,
                'Warehouses' => $this->options->getWarehouses(),
            ],
            'help' => 'Stock destination when updating stock and creating products in Moloni',
            'placeholder' => 'Please select an option',
            'translation_domain' => $this->transDomain,
        ]);

        return $this;
    }

    /**
     * @return $this
     */
    private function addProductsToMoloni(): SettingsFormType
    {
        $this->builder->add('addProductsToMoloni', ChoiceType::class, [
            'label' => 'Create products',
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => 'When creating a product in Prestashop, the product will be created in Moloni',
            'placeholder' => false,
            'translation_domain' => $this->transDomain,
        ]);

        return $this;
    }

    private function updateProductsToMoloni(): SettingsFormType
    {
        $this->builder->add('updateProductsToMoloni', ChoiceType::class, [
            'label' => 'Update products',
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => 'When updating a product in Prestashop, the product will be updated in Moloni',
            'placeholder' => false,
            'translation_domain' => $this->transDomain,
        ]);

        return $this;
    }

    private function syncStockToPrestashop(): SettingsFormType
    {
        $this->builder->add('syncStockToPrestashop', ChoiceType::class, [
            'label' => 'Synchronize stocks',
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => 'Automatic stock synchronization to Prestashop',
            'placeholder' => false,
            'translation_domain' => $this->transDomain,
        ]);

        return $this;
    }

    private function syncStockToPrestashopWarehouse(): SettingsFormType
    {
        $this->builder
            ->add('syncStockToPrestashopWarehouse', ChoiceType::class, [
                'label' => 'Stock source',
                'required' => false,
                'choices' => [
                    'Accumulated stock' => 1,
                    'Warehouses' => $this->options->getWarehouses(),
                ],
                'help' => 'Stock source used when synchronizing stock and creating products in Prestashop',
                'placeholder' => 'Please select an option',
                'translation_domain' => $this->transDomain,
            ]);

        return $this;
    }

    private function addProductsToPrestashop(): SettingsFormType
    {
        $this->builder->add('addProductsToPrestashop', ChoiceType::class, [
            'label' => 'Create products',
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => 'When creating a product in Moloni, the product will be created in Prestashop',
            'placeholder' => false,
            'translation_domain' => $this->transDomain,
        ]);

        return $this;
    }

    private function updateProductsToPrestashop(): SettingsFormType
    {
        $this->builder->add('updateProductsToPrestashop', ChoiceType::class, [
            'label' => 'Update products',
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => 'When updating a product in Moloni, the product will be updated in Prestashop',
            'placeholder' => false,
            'translation_domain' => $this->transDomain,
        ]);

        return $this;
    }

    private function productSyncFields(): SettingsFormType
    {
        $this->builder->add('productSyncFields', ChoiceType::class, [
            'label' => 'Products fields',
            'multiple' => true,
            'expanded' => true,
            'required' => false,
            'choices' => SyncFields::getSyncFields(),
            'help' => 'Choose which fields will be synced when a product is updated.',
            'placeholder' => false,
            'translation_domain' => $this->transDomain,
        ]);

        return $this;
    }

    private function orderDateCreated(): SettingsFormType
    {
        $this->builder->add('orderDateCreated', DateType::class, [
            'widget' => 'single_text',
            'required' => false,
            'label' => 'Orders since',
            'help' => 'Date used to limit fetch pending orders',
            'placeholder' => false,
            'translation_domain' => $this->transDomain,
        ]);

        return $this;
    }

    private function orderStatusToShow(): SettingsFormType
    {
        $this->builder->add('orderStatusToShow', ChoiceType::class, [
            'label' => 'Order status',
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'choices' => $this->options->getOrderStatus(),
            'help' => 'Allowed order status to list pending orders and automatic document creation. (if at least one is selected)',
            'translation_domain' => $this->transDomain,
        ]);

        return $this;
    }

    private function automaticDocuments(): SettingsFormType
    {
        $this->builder->add('automaticDocuments', ChoiceType::class, [
            'label' => $this->trans('Create paid documents on Moloni', 'Modules.Molonies.Settings'),
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans(
                'Automatically create document when an order is paid',
                'Modules.Molonies.Settings'
            ),
            'placeholder' => false,
            'translation_domain' => 'Modules.Molonies.Settings',
        ]);

        return $this;
    }

    private function clientPrefix(): SettingsFormType
    {
        $this->builder->add('clientPrefix', TextType::class, [
            'label' => 'Client Prefix',
            'required' => false,
            'help' => 'If set, created customers will have this prefix in their code (Example: PS)',
            'translation_domain' => $this->transDomain,
        ]);

        return $this;
    }

    private function documentSet(): SettingsFormType
    {
        $this->builder
            ->add('documentSet', ChoiceType::class, [
                'label' => 'Document set',
                'required' => true,
                'choices' => $this->options->getDocumentSets(),
                'placeholder' => 'Please select an option',
                'translation_domain' => $this->transDomain,
            ]);

        return $this;
    }

    private function documentType(): SettingsFormType
    {
        $this->builder
            ->add('documentType', ChoiceType::class, [
                'label' => 'Document type',
                'label_attr' => [
                    'required' => true,
                ],
                'required' => true,
                'choices' => $this->options->getDocumentTypes(),
                'placeholder' => 'Please select an option',
                'translation_domain' => $this->transDomain,
            ]);

        return $this;
    }

    private function documentStatus(): SettingsFormType
    {
        $this->builder
            ->add('documentStatus', ChoiceType::class, [
                'label' => 'Document status',
                'required' => true,
                'choices' => $this->options->getStatus(),
                'placeholder' => false,
                'translation_domain' => $this->transDomain,
            ]);

        return $this;
    }

    private function fiscalZoneBasedOn(): SettingsFormType
    {
        $this->builder
            ->add('fiscalZoneBasedOn', ChoiceType::class, [
                'label' => 'Fiscal zone',
                'required' => false,
                'choices' => $this->options->getFiscalZoneBasedOn(),
                'help' => 'Address used to set document fiscal zone',
                'placeholder' => false,
                'translation_domain' => $this->transDomain,
            ]);

        return $this;
    }

    private function shippingInformation(): SettingsFormType
    {
        $this->builder->add('shippingInformation', ChoiceType::class, [
            'label' => 'Shipping information',
            'required' => false,
            'choices' => $this->options->getYesNo(),
            'help' => 'Show shipping information',
            'placeholder' => false,
            'translation_domain' => $this->transDomain,
        ]);

        return $this;
    }

    private function billOfLading(): SettingsFormType
    {
        $this->builder->add('billOfLading', ChoiceType::class, [
            'label' => 'Bill of lading',
            'choices' => $this->options->getYesNo(),
            'help' => 'Create order document bill of lading',
            'placeholder' => false,
            'required' => false,
            'translation_domain' => $this->transDomain,
        ]);

        return $this;
    }

    private function loadAddress(): SettingsFormType
    {
        $this->builder
            ->add('loadAddress', ChoiceType::class, [
                'label' => 'Loading address',
                'choices' => $this->options->getAddresses(),
                'help' => 'Load address used',
                'placeholder' => false,
                'required' => false,
                'choice_translation_domain' => $this->transDomain,
            ]);

        return $this;
    }

    private function customloadAddressAddress(): SettingsFormType
    {
        $this->builder->add('customloadAddressAddress', TextType::class, [
            'label' => false,
            'required' => false,
            'translation_domain' => $this->transDomain,
            'attr' => [
                'placeholder' => 'Address',
            ],
        ]);

        return $this;
    }

    private function customloadAddressZipCode(): SettingsFormType
    {
        $this->builder->add('customloadAddressZipCode', TextType::class, [
            'label' => false,
            'required' => false,
            'translation_domain' => $this->transDomain,
            'attr' => [
                'placeholder' => 'Zip-code',
            ],
        ]);

        return $this;
    }

    private function customloadAddressCity(): SettingsFormType
    {
        $this->builder->add('customloadAddressCity', TextType::class, [
            'label' => false,
            'required' => false,
            'translation_domain' => $this->transDomain,
            'attr' => [
                'placeholder' => 'City',
            ],
        ]);

        return $this;
    }

    private function customloadAddressCountry(): SettingsFormType
    {
        $this->builder
            ->add('customloadAddressCountry', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'choices' => $this->options->getCountries(),
                'placeholder' => 'Please select country',
                'translation_domain' => $this->transDomain,
            ]);

        return $this;
    }

    private function sendByEmail(): SettingsFormType
    {
        $this->builder
            ->add('sendByEmail', ChoiceType::class, [
                'label' => 'Send e-mail',
                'required' => false,
                'choices' => $this->options->getYesNo(),
                'help' => 'Sends document to customer via e-mail',
                'placeholder' => false,
                'translation_domain' => $this->transDomain,
            ]);

        return $this;
    }

    private function useProductNameAndSummaryFrom(): SettingsFormType
    {
        $this->builder
            ->add('useProductNameAndSummaryFrom', ChoiceType::class, [
                'label' => 'Use product name and summary from',
                'required' => true,
                'choices' => $this->options->getProductInformation(),
                'help' => 'The product in the document will use the name and summary from the selected source',
                'placeholder' => false,
                'translation_domain' => $this->transDomain,
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
                'help' => "Fuck this",
                'label_attr' => [
                    'popover' => 'Tooltip me I\'m famous !',
                ],
                'label_help_box' => $this->trans('Product exemption reason4', $this->transDomain),
                'required' => false,
            ]);

        return $this;
    }

    private function exemptionReasonShipping(): SettingsFormType
    {
        $this->builder
            ->add('exemptionReasonShipping', TextType::class, [
                'label' => 'Shipping exemption reason',
                'required' => false,
                'help' => 'Will be used if the shipping method has no taxes',
                'translation_domain' => $this->transDomain,
            ]);

        return $this;
    }

    private function measurementUnit(): SettingsFormType
    {
        $this->builder
            ->add('measurementUnit', ChoiceType::class, [
                'label' => 'Measure unit',
                'required' => true,
                'choices' => $this->options->getMeasurementUnits(),
                'help' => 'Created products will use this measurement unit',
                'placeholder' => 'Please select an option',
                'translation_domain' => $this->transDomain,
            ]);

        return $this;
    }

    private function documentWarehouse(): SettingsFormType
    {
        $this->builder->add('documentWarehouse', ChoiceType::class, [
            'label' => 'Document warehouse',
            'required' => false,
            'choices' => $this->options->getWarehouses(),
            'help' => 'Warehouse used in documents',
            'placeholder' => 'Please select an option',
            'translation_domain' => $this->transDomain,
        ]);

        return $this;
    }

    private function alertEmail(): SettingsFormType
    {
        $this->builder->add('alertEmail', EmailType::class, [
            'label' => 'E-mail address',
            'required' => false,
            'help' => 'E-mail used to send notifications in case of plugin failures',
            'attr' => [
                'placeholder' => 'example@email.com',
            ],
            'translation_domain' => $this->transDomain,
        ]);

        return $this;
    }

    private function saveButton(): SettingsFormType
    {
        $this->builder->add('saveChanges', SubmitType::class, [
            'attr' => ['class' => 'btn-primary'],
            'label' => 'Save',
            'translation_domain' => $this->transDomain,
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
