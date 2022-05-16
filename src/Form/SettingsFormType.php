<?php

namespace Moloni\Form;

use Moloni\Enums\Boolean;
use Moloni\Enums\DocumentStatus;
use Moloni\Enums\LoadAddress;
use Moloni\Enums\ProductInformation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $yesNoOptions = [
            'No' => Boolean::NO,
            'Yes' => Boolean::YES,
        ];
        $productInformation = [
            'Prestashop' => ProductInformation::PRESTASHOP,
            'Moloni' => ProductInformation::MOLONI,
        ];
        $addresses = [
            'Prestashop' => LoadAddress::SHOP,
            'Moloni' => LoadAddress::MOLONI,
            'Custom' => LoadAddress::CUSTOM,
        ];
        $syncFields = [
            'Name' => 'name',
            'Price' => 'price',
            'Description' => 'description',
            'Visibility' => 'visibility',
            'Stock' => 'stock',
            'Categories' => 'category',
        ];
        $status = [
            'Draft' => DocumentStatus::DRAFT,
            'Closed' => DocumentStatus::CLOSED,
        ];
        $documentTypes = [
            'Invoice' => 'invoices',
            'Invoice + Receipt' => 'receipts',
            'Purchase Order' => 'purchaseOrders',
            'Pro Forma Invoice' => 'proFormaInvoices',
            'Simplified invoice' => 'simplifiedInvoices',
        ];

        $measurementUnits = $options['api_data']['measurementUnits'] ?? [];
        $warehouses = $options['api_data']['warehouses'] ?? [];
        $documentSets = $options['api_data']['documentSets'] ?? [];
        $countries = $options['api_data']['countries'] ?? [];

        return $builder
            // automations
            ->add('syncStockToMoloni', ChoiceType::class, [
                'label' => 'Synchronize stocks',
                'required' => false,
                'choices' => $yesNoOptions,
                'help' => 'Automatic stock synchronization to Moloni',
                'placeholder' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('syncStockToMoloniWarehouse', ChoiceType::class, [
                'label' => 'Stock destination',
                'required' => false,
                'choices' => [
                    'Default warehouse' => 1,
                    'Warehouses' => $warehouses ?? []
                ],
                'help' => 'Stock destination when updating stock and creating products in Moloni',
                'placeholder' => 'Please select an option',
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('addProductsToMoloni', ChoiceType::class, [
                'label' => 'Create products',
                'required' => false,
                'choices' => $yesNoOptions,
                'placeholder' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('updateProductsToMoloni', ChoiceType::class, [
                'label' => 'Update products',
                'required' => false,
                'choices' => $yesNoOptions,
                'placeholder' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('syncStockToPrestashop', ChoiceType::class, [
                'label' => 'Synchronize stocks',
                'required' => false,
                'choices' => $yesNoOptions,
                'help' => 'Automatic stock synchronization to Prestashop',
                'placeholder' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('syncStockToPrestashopWarehouse', ChoiceType::class, [
                'label' => 'Stock source',
                'required' => false,
                'choices' => [
                    'Accumulated stock' => 1,
                    'Warehouses' => $warehouses?? []
                ],
                'help' => 'Stock source used when synchronizing stock and creating products in Prestashop',
                'placeholder' => 'Please select an option',
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('addProductsToPrestashop', ChoiceType::class, [
                'label' => 'Create products',
                'required' => false,
                'choices' => $yesNoOptions,
                'placeholder' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('updateProductsToPrestashop', ChoiceType::class, [
                'label' => 'Update products',
                'required' => false,
                'choices' => $yesNoOptions,
                'placeholder' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('productSyncFields', ChoiceType::class, [
                'label' => 'Fields to sync',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => $syncFields,
                'placeholder' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            // documents
            ->add('automaticDocuments', ChoiceType::class, [
                'label' => 'Create paid documents on Moloni',
                'required' => false,
                'choices' => $yesNoOptions,
                'help' => 'Automatically create document when an order is paid',
                'placeholder' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('clientPrefix', TextType::class, [
                'label' => 'Client Prefix',
                'required' => false,
                'help' => 'If set, created customers will have this prefix in their code (Example: PS)',
                'translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('documentSet', ChoiceType::class, [
                'label' => 'Document set',
                'required' => true,
                'choices' => $documentSets ?? [],
                'placeholder' => 'Please select an option',
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('documentType', ChoiceType::class, [
                'label' => 'Document type',
                'label_attr' => [
                    'required' => true
                ],
                'required' => true,
                'choices' => $documentTypes,
                'placeholder' => 'Please select an option',
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('documentStatus', ChoiceType::class, [
                'label' => 'Document status',
                'required' => true,
                'choices' => $status,
                'placeholder' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('shippingInformation', ChoiceType::class, [
                'label' => 'Shipping information',
                'required' => false,
                'choices' => $yesNoOptions,
                'help' =>'Show shipping information',
                'placeholder' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('billOfLanding', ChoiceType::class, [
                'label' => 'Bill of lading',
                'choices' => $yesNoOptions,
                'help' => 'Create order document bill of lading',
                'placeholder' => false,
                'required' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('loadAddress', ChoiceType::class, [
                'label' => 'Loading address',
                'choices' => $addresses,
                'help' => 'Load address used',
                'placeholder' => false,
                'required' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('customloadAddressAddress', TextType::class, [
                'label' => false,
                'required' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'attr' => [
                    'placeholder' => 'Address',
                ],
            ])
            ->add('customloadAddressZipCode', TextType::class, [
                'label' => false,
                'required' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'attr' => [
                    'placeholder' => 'Zip-code',
                ],
            ])
            ->add('customloadAddressCity', TextType::class, [
                'label' => false,
                'required' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'attr' => [
                    'placeholder' => 'City',
                ],
            ])
            ->add('customloadAddressCountry', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'choices' => $countries,
                'placeholder' => 'Please select country',
                'translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('sendByEmail', ChoiceType::class, [
                'label' => 'Send e-mail',
                'required' => false,
                'choices' => $yesNoOptions,
                'help' => 'Sends document to customer via email',
                'placeholder' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('useProductNameAndSummaryFrom', ChoiceType::class, [
                'label' => 'Use product name and summary from',
                'required' => true,
                'choices' => $productInformation,
                'help' => 'The product in the document will use the name and summary from the selected source',
                'placeholder' => false,
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('exemptionReasonProduct', TextType::class, [
                'label' => 'Product exemption reason',
                'required' => false,
                'help' => 'Will be used if the product has no taxes',
            ])
            ->add('exemptionReasonShipping', TextType::class, [
                'label' => 'Shipping exemption reason',
                'required' => false,
                'help' => 'Will be used if the shipping method has no taxes',
                'translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('measurementUnit', ChoiceType::class, [
                'label' => 'Measure unit',
                'required' => true,
                'choices' => $measurementUnits ?? [],
                'help' => 'Created products will use this measurement unit',
                'placeholder' => 'Please select an option',
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('documentWarehouse', ChoiceType::class, [
                'label' => 'Document warehouse',
                'required' => false,
                'choices' => $warehouses ?? [],
                'help' => 'Warehouse used in documents',
                'placeholder' => 'Please select an option',
                'translation_domain' => 'Modules.Molonies.Common',
                'choice_translation_domain' => 'Modules.Molonies.Common',
            ])
            // Advanced
            ->add('alertEmail', EmailType::class, [
                'label' => 'Email address',
                'required' => false,
                'help' => 'Email used to send notifications in case of plugin failures',
                'attr' => [
                    'placeholder' => 'example@email.com',
                ],
                'translation_domain' => 'Modules.Molonies.Common',
            ])
            ->add('dateCreated', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Orders since',
                'help' => 'Date used to limit fetch pending orders',
                'placeholder' => false,
                'translation_domain' => 'Modules.Molonies.Common',
            ])
            // save
            ->add('saveChanges', SubmitType::class, [
                'attr' => ['class' => 'btn-primary'],
                'label' => 'Save',
                'translation_domain' => 'Modules.Molonies.Common',
            ])
            ->setAction($options['url'])
            ->setMethod('POST');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'url' => '',
            'api_data' => []
        ));
    }
}
