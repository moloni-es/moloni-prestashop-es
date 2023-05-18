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
 * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
 */


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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
            'label_attr' => [
                'popover' => $this->trans(
                    'Choose to synchronize the product when a Prestashop product is updated.<br><br>
                            Ex.: Update a product in Prestashop from 0 stock to 20, and a stock movement will be create in Moloni for that product.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'required' => false,
            'choices' => $this->options->getYesNo(),
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
            'label' => $this->trans('Warehouse', "Modules.Molonies.Settings"),
            'label_attr' => [
                'popover' => $this->trans(
                    'Select which warehouse will be used during the product insert process or during the product stock synchronization process.
                            This warehouse will be used when a product is inserted or updated <b>in Prestashop.</b>',
                    "Modules.Molonies.Settings"
                ),
            ],
            'choices' => [
                $this->trans('Default warehouse', "Modules.Molonies.Settings") => 1,
                $this->trans('Warehouses', "Modules.Molonies.Settings") => $this->options->getWarehouses(),
            ],
            'placeholder' => $this->trans('Please select an option', "Modules.Molonies.Settings"),
            'required' => false
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
            'label_attr' => [
                'popover' => $this->trans(
                    'Choose if a product should be created in Moloni when it is <b>created</b> in Prestashop.<br><br>
                            Ex.: Insert a new product in Prestashop and that same product will be automaticaly created in Moloni.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'choices' => $this->options->getYesNo(),
            'required' => false,
            'placeholder' => false
        ]);

        return $this;
    }

    private function updateProductsToMoloni(): SettingsFormType
    {
        $this->builder->add('updateProductsToMoloni', ChoiceType::class, [
            'label' => $this->trans('Update products', "Modules.Molonies.Settings"),
            'label_attr' => [
                'popover' => $this->trans(
                    'Choose if a product should be updated in Moloni when it is <b>updated</b> in Prestashop.<br><br>
                            Ex.: Update a product in Prestashop and that same product will be automaticaly created or updated in Moloni.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'choices' => $this->options->getYesNo(),
            'required' => false,
            'placeholder' => false
        ]);

        return $this;
    }

    private function syncStockToPrestashop(): SettingsFormType
    {
        $this->builder->add('syncStockToPrestashop', ChoiceType::class, [
            'label' => $this->trans('Synchronize stocks', "Modules.Molonies.Settings"),
            'label_attr' => [
                'popover' => $this->trans(
                    'Choose to synchronize the product when a Moloni product is updated.<br><br>
                            Ex.: Update a product in Moloni from 0 stock to 20 and the product stock in Prestashop will be updated.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'required' => false,
            'placeholder' => false,
            'choices' => $this->options->getYesNo(),
        ]);

        return $this;
    }

    private function syncStockToPrestashopWarehouse(): SettingsFormType
    {
        $this->builder
            ->add('syncStockToPrestashopWarehouse', ChoiceType::class, [
                'label' => $this->trans('Warehouse', "Modules.Molonies.Settings"),
                'label_attr' => [
                    'popover' => $this->trans(
                        'Select which warehouse will be used during the product insert process or during the product stock synchronization process.<br><br>
                                This warehouse will be used when a product is inserted or updated <b>in Moloni.</b>',
                        "Modules.Molonies.Settings"
                    ),
                ],
                'choices' => [
                    $this->trans('Accumulated stock', "Modules.Molonies.Settings") => 1,
                    $this->trans('Warehouses', "Modules.Molonies.Settings") => $this->options->getWarehouses(),
                ],
                'required' => false,
                'placeholder' => false,
            ]);

        return $this;
    }

    private function addProductsToPrestashop(): SettingsFormType
    {
        $this->builder->add('addProductsToPrestashop', ChoiceType::class, [
            'label' => $this->trans('Create products', "Modules.Molonies.Settings"),
            'label_attr' => [
                'popover' => $this->trans(
                    'Choose if a product should be created in Prestashop when it is <b>created</b> in Moloni.<br><br>
                            Ex.: Insert a new product in Moloni and that same product will be automaticaly created in Prestashop.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'choices' => $this->options->getYesNo(),
            'required' => false,
            'placeholder' => false,
        ]);

        return $this;
    }

    private function updateProductsToPrestashop(): SettingsFormType
    {
        $this->builder->add('updateProductsToPrestashop', ChoiceType::class, [
            'label' => $this->trans('Update products', "Modules.Molonies.Settings"),
            'label_attr' => [
                'popover' => $this->trans(
                    'Choose if a product should be updated in Prestashop when it is <b>updated</b> in Moloni.<br><br>
                            Ex.: Update a product in Moloni and that same product will be automaticaly created or updated in Prestashop.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'required' => false,
            'placeholder' => false,
            'choices' => $this->options->getYesNo(),
        ]);

        return $this;
    }

    private function productSyncFields(): SettingsFormType
    {
        $this->builder->add('productSyncFields', ChoiceType::class, [
            'label' => $this->trans('Product fields', "Modules.Molonies.Settings"),
            'label_attr' => [
                'popover' => $this->trans(
                    'You can select which fields should be updated when a product update occurs.<br><br>
                            This is useful to have for example different prices on your online store and in your Moloni account, or different names.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'multiple' => true,
            'expanded' => true,
            'empty_data' => '',
            'required' => false,
            'choices' => $this->options->getSyncFields(),
        ]);

        return $this;
    }

    private function orderDateCreated(): SettingsFormType
    {
        $this->builder->add('orderDateCreated', DateType::class, [
            'widget' => 'single_text',
            'label' => $this->trans('Orders since', "Modules.Molonies.Settings"),
            'label_attr' => [
                'popover' => $this->trans(
                    'By default, we will list all orders that were not converted into Moloni documents.<br><br>
                            If you are migrating from an old invoicing software to Moloni, you may choose to list only orders after a selected date.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'placeholder' => false,
            'required' => false,
        ]);

        return $this;
    }

    private function orderStatusToShow(): SettingsFormType
    {
        $this->builder->add('orderStatusToShow', ChoiceType::class, [
            'label' => $this->trans('Order status', "Modules.Molonies.Settings"),
            'label_attr' => [
                'popover' => $this->trans(
                    'When an order is in one of the following selected status, it will become available to be converted into a document.<br><br>
                            If you have the option <b>"Auto create documents"</b> activated, when an order passes in one of the selected states, we will try to automatically create a document for it.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'multiple' => true,
            'expanded' => true,
            'required' => false,
            'choices' => $this->options->getOrderStatus()
        ]);

        return $this;
    }

    private function automaticDocuments(): SettingsFormType
    {
        $this->builder->add('automaticDocuments', ChoiceType::class, [
            'label' => $this->trans('Auto create documents', "Modules.Molonies.Settings"),
            'label_attr' => [
                'popover' => $this->trans(
                    'When a Prestashop order passes on a status that is select in the "orders" configuration tab, the module will try to automaticaly create a document for that order.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'choices' => $this->options->getYesNo(),
            'help' => $this->trans(
                'Choose this to automaticaly create documents in Moloni.',
                "Modules.Molonies.Settings"
            ),
            'placeholder' => false,
            'required' => false,
        ]);

        return $this;
    }

    private function clientPrefix(): SettingsFormType
    {
        $this->builder->add('clientPrefix', TextType::class, [
            'label' => $this->trans('Customer code prefix', "Modules.Molonies.Settings"),
            'label_attr' => [
                'popover' => $this->trans(
                    'A customer needs to have a unique identifier in Moloni, and you can use this option to add a prefix to that identifier.<br><br>
                            If you set a prefix of "PS_", your customer numbers will be incremented for example as PS_1, PS_2, PS_3, etc.<br><br>
                            This is useful to easily identify which customers were created by this module.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'required' => false
        ]);

        return $this;
    }

    private function clientUpdate(): SettingsFormType
    {
        $this->builder->add('clientUpdate', ChoiceType::class, [
            'label' => $this->trans('Update customer', "Modules.Molonies.Settings"),
            'label_attr' => [
                'popover' => $this->trans(
                    'Update client when it already exists in Moloni account.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'choices' => $this->options->getYesNo(),
            'placeholder' => false,
            'required' => false,
        ]);

        return $this;
    }

    private function documentSet(): SettingsFormType
    {
        $this->builder
            ->add('documentSet', ChoiceType::class, [
                'label' => $this->trans('Document set', "Modules.Molonies.Settings"),
                'label_attr' => [
                    'popover' => $this->trans(
                        'Select which Moloni document set you want to use for your documents.<br><br>
                                You can manage your company document sets directly in your Moloni account.',
                        "Modules.Molonies.Settings"
                    ),
                ],
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
                    'popover' => $this->trans(
                        'Select which type of document you want to issue automatically.',
                        "Modules.Molonies.Settings"
                    ),
                ],
                'required' => true,
                'choices' => $this->options->getDocumentTypes(),
                'placeholder' => $this->trans('Please select an option', "Modules.Molonies.Settings"),

            ]);

        return $this;
    }

    private function documentReference(): SettingsFormType
    {
        $this->builder
            ->add('documentReference', ChoiceType::class, [
                'label' => $this->trans('Document reference', "Modules.Molonies.Settings"),
                'label_attr' => [
                    'popover' => $this->trans(
                        'Value used for document reference.',
                        "Modules.Molonies.Settings"
                    ),
                ],
                'choices' => $this->options->getDocumentReference(),
                'placeholder' => false,
                'required' => true,
            ]);

        return $this;
    }

    private function documentStatus(): SettingsFormType
    {
        $this->builder
            ->add('documentStatus', ChoiceType::class, [
                'label' => $this->trans('Document status', "Modules.Molonies.Settings"),
                'label_attr' => [
                    'popover' => $this->trans(
                        'You can issue your documents as draft and close them later, or you can insert them directly as closed.
                            <br><br>Before closing the document we will first confirm that the total value of the order matches with total value of the document.',
                        "Modules.Molonies.Settings"
                    ),
                ],
                'choices' => $this->options->getStatus(),
                'placeholder' => false,
                'required' => false,
            ]);

        return $this;
    }

    private function fiscalZoneBasedOn(): SettingsFormType
    {
        $this->builder
            ->add('fiscalZoneBasedOn', ChoiceType::class, [
                'label' => $this->trans('Taxes Fiscal zone', "Modules.Molonies.Settings"),
                'label_attr' => [
                    'popover' => $this->trans(
                        'Select which fiscal zone should be used for new taxes that need to be created.',
                        "Modules.Molonies.Settings"
                    ),
                ],
                'choices' => $this->options->getFiscalZoneBasedOn(),
                'placeholder' => false,
                'required' => false,
            ]);

        return $this;
    }

    private function shippingInformation(): SettingsFormType
    {
        $this->builder->add('shippingInformation', ChoiceType::class, [
            'label' => $this->trans('Show shipping information', "Modules.Molonies.Settings"),
            'label_attr' => [
                'popover' => $this->trans(
                    'If the seleceted document type shipping information is optional, you can use this option to choose if you want to include it or not.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'choices' => $this->options->getYesNo(),
            'placeholder' => false,
            'required' => false,
        ]);

        return $this;
    }

    private function billOfLading(): SettingsFormType
    {
        $this->builder->add('billOfLading', ChoiceType::class, [
            'label' => $this->trans('Create bill of lading', "Modules.Molonies.Settings"),
            'label_attr' => [
                'popover' => $this->trans(
                    'Choose if you want to create a Bill of Lading associated with the main document.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'choices' => $this->options->getYesNo(),
            'placeholder' => false,
            'required' => false,
        ]);

        return $this;
    }

    private function loadAddress(): SettingsFormType
    {
        $this->builder
            ->add('loadAddress', ChoiceType::class, [
                'label' => $this->trans('Loading address', "Modules.Molonies.Settings"),
                'label_attr' => [
                    'popover' => $this->trans(
                        'Select which shipping address should be used for your shipping documents<br>
                                You can select between your company address, your store addresses or set a custom one.',
                        "Modules.Molonies.Settings"
                    ),
                ],
                'choices' => $this->options->getAddresses(),
                'placeholder' => false,
                'required' => false,
            ]);

        return $this;
    }

    private function customloadAddressAddress(): SettingsFormType
    {
        $this->builder->add('customloadAddressAddress', TextType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => $this->trans('Address', "Modules.Molonies.Settings"),
            ],
            'required' => false
        ]);

        return $this;
    }

    private function customloadAddressZipCode(): SettingsFormType
    {
        $this->builder->add('customloadAddressZipCode', TextType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => $this->trans('Zip-code', "Modules.Molonies.Settings"),
            ],
            'required' => false
        ]);

        return $this;
    }

    private function customloadAddressCity(): SettingsFormType
    {
        $this->builder->add('customloadAddressCity', TextType::class, [
            'label' => false,
            'required' => false,
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
                'label' => false,
                'choices' => $this->options->getCountries(),
                'placeholder' => $this->trans('Please select a country', "Modules.Molonies.Settings"),
                'required' => false
            ]);

        return $this;
    }

    private function sendByEmail(): SettingsFormType
    {
        $this->builder
            ->add('sendByEmail', ChoiceType::class, [
                'label' => $this->trans('Send e-mail', "Modules.Molonies.Settings"),
                'label_attr' => [
                    'popover' => $this->trans(
                        'When a document is inserted and correctly closed in Moloni an e-mail with the document will be sent to the customer.',
                        "Modules.Molonies.Settings"
                    ),
                ],
                'choices' => $this->options->getYesNo(),
                'placeholder' => false,
                'required' => false
            ]);

        return $this;
    }

    private function useProductNameAndSummaryFrom(): SettingsFormType
    {
        $this->builder
            ->add('useProductNameAndSummaryFrom', ChoiceType::class, [
                'label' => $this->trans('Product details from', "Modules.Molonies.Settings"),
                'label_attr' => [
                    'popover' => $this->trans(
                        'Choose if the product should use the name and description set in Moloni or in your store.',
                        "Modules.Molonies.Settings"
                    ),
                ],
                'choices' => $this->options->getProductInformation(),
                'placeholder' => false,
                'required' => false
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
                        'This exemption reason will be used when a <b>product</b> does not have a defined tax on the order that you are trying to issue.',
                        "Modules.Molonies.Settings"
                    ),
                ],
                'required' => false
            ]);

        return $this;
    }

    private function exemptionReasonShipping(): SettingsFormType
    {
        $this->builder
            ->add('exemptionReasonShipping', TextType::class, [
                'label' => $this->trans('Shipping exemption reason', "Modules.Molonies.Settings"),
                'label_attr' => [
                    'popover' => $this->trans(
                        'This exemption reason will be used when your order <b>shipping</b> does not have a defined tax on the order that you are trying to issue.',
                        "Modules.Molonies.Settings"
                    ),
                ],
                'required' => false
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
                'placeholder' => $this->trans('Please select an option', "Modules.Molonies.Settings"),
                'label_attr' => [
                    'popover' => $this->trans(
                        'Choose which measurement unit should be used by default on your Products.<br><br>
                               You can manage your measurement units in your Moloni account.',
                        "Modules.Molonies.Settings"
                    ),
                ],
            ]);

        return $this;
    }

    private function documentWarehouse(): SettingsFormType
    {
        $this->builder->add('documentWarehouse', ChoiceType::class, [
            'label' => $this->trans('Document warehouse', "Modules.Molonies.Settings"),
            'label_attr' => [
                'popover' => $this->trans(
                    'Choose which warehouse should be used when issuing documents.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'choices' => $this->options->getWarehouses(),
            'required' => true,
            'placeholder' => false,
        ]);

        return $this;
    }

    private function companyName(): SettingsFormType
    {
        $this->builder->add('companyName', HiddenType::class, [
            'label' => $this->trans('Company name', "Modules.Molonies.Settings"),
            'required' => false,
        ]);

        return $this;
    }

    private function alertEmail(): SettingsFormType
    {
        $this->builder->add('alertEmail', EmailType::class, [
            'label' => $this->trans('Alert e-mail', "Modules.Molonies.Settings"),
            'help' => $this->trans(
                'Receive alerts for when an error occurs during a document creation process.',
                "Modules.Molonies.Settings"
            ),
            'attr' => [
                'placeholder' => $this->trans('example@email.com', "Modules.Molonies.Settings"),
            ],
            'required' => false,
        ]);

        return $this;
    }

    private function productReferenceFallback(): SettingsFormType
    {
        $this->builder->add('productReferenceFallback', ChoiceType::class, [
            'label' => $this->trans('Enable reference fallback', "Modules.Molonies.Settings"),
            'label_attr' => [
                'popover' => $this->trans(
                    'When synchronizing products from Moloni if the reference is numeric and no products are found in Prestashop, the plugin will try and find an Prestashop product by an ID that matches the numeric reference. This is useful if products do not have a reference set in Prestashop.',
                    "Modules.Molonies.Settings"
                ),
            ],
            'choices' => $this->options->getYesNo(),
            'placeholder' => false,
            'required' => false
        ]);

        return $this;
    }

    private function saveButton(): SettingsFormType
    {
        $this->builder->add('saveChanges', SubmitType::class, [
            'attr' => ['class' => 'btn-primary'],
            'label' =>$this->trans('Save', "Modules.Molonies.Settings"),
        ]);

        return $this;
    }

    private function setDocumentsTab(): SettingsFormType
    {
        $this
            ->documentSet()
            ->documentType()
            ->documentReference()
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
            ->clientUpdate()
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
        $this
            ->companyName()
            ->alertEmail()
            ->productReferenceFallback();

        return $this;
    }
}
