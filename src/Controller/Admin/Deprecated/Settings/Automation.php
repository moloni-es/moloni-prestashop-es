<?php

/** @noinspection ALL */

namespace Moloni\Controller\Admin\Settings;

use Db;
use Moloni\Controller\Admin\Deprecated\General;
use Moloni\Helpers\Settings;
use PrestaShopDatabaseException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use function Moloni\Controller\Settings\pSQL;

class Automation extends General
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

        $form = $this->buildFormAuto();

        $aux = 'auto';

        return $this->render(
            '@Modules/molonies/views/templates/admin/settings/Automation.twig',
            [
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
    public function buildFormAuto()
    {
        return $this->createFormBuilder()
            ->add('CreateAuto', ChoiceType::class, [
                'label' => $this->trans('Create paid documents on Moloni', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Molonies.Settings') => '1',
                    $this->trans('No', 'Modules.Molonies.Settings') => '0',
                ],
                'data' => Settings::get('CreateAuto'),
            ])
            ->add('Stocks', ChoiceType::class, [
                'label' => $this->trans('Synchronize stocks on Moloni', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Molonies.Settings') => '1',
                    $this->trans('No', 'Modules.Molonies.Settings') => '0',
                ],
                'data' => Settings::get('Stocks'),
            ])
            ->add('AddProducts', ChoiceType::class, [
                'label' => $this->trans('Create products on Moloni', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Molonies.Settings') => '1',
                    $this->trans('No', 'Modules.Molonies.Settings') => '0',
                ],
                'data' => Settings::get('AddProducts'),
            ])
            ->add('UpdateArtigos', ChoiceType::class, [
                'label' => $this->trans('Update products on Moloni', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Molonies.Settings') => '1',
                    $this->trans('No', 'Modules.Molonies.Settings') => '0',
                ],
                'data' => Settings::get('UpdateArtigos'),
            ])
            ->add('HooksVariantsUpdate', ChoiceType::class, [
                'label' => $this->trans('Update products with variants', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Molonies.Settings') => '1',
                    $this->trans('No', 'Modules.Molonies.Settings') => '0',
                ],
                'data' => Settings::get('HooksVariantsUpdate'),
            ])
            ->add('HooksAddProducts', ChoiceType::class, [
                'label' => $this->trans('Add products', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Molonies.Settings') => '1',
                    $this->trans('No', 'Modules.Molonies.Settings') => '0',
                ],
                'data' => Settings::get('HooksAddProducts'),
            ])
            ->add('HooksUpdateProducts', ChoiceType::class, [
                'label' => $this->trans('Update products', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Molonies.Settings') => '1',
                    $this->trans('No', 'Modules.Molonies.Settings') => '0',
                ],
                'data' => Settings::get('HooksUpdateProducts'),
            ])
            ->add('HooksUpdateStock', ChoiceType::class, [
                'label' => $this->trans('Update stock', 'Modules.Molonies.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Molonies.Settings') => '1',
                    $this->trans('No', 'Modules.Molonies.Settings') => '0',
                ],
                'data' => Settings::get('HooksUpdateStock'),
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
                'data' => Settings::get('SyncFields') !== false ? unserialize(Settings::get('SyncFields')) : [],
            ])
            ->add('SaveChanges', SubmitType::class, [
                'attr' => ['class' => 'btn-outline-success'],
                'label' => $this->trans('Save changes', 'Modules.Molonies.Settings'),
            ])
            ->setAction($this->generateUrl('moloni_es_settings_automation_save'))
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
        $form = $this->buildFormAuto();

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

                    if (is_array($value)) {
                        $value = serialize($value);
                    }

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
                    'Modules.Molonies.Success'
                ));

                return $this->redirectSettingsAuto();
            }
            $this->addFlash('warning', $this->trans(
                'Form not valid!!',
                'Modules.Molonies.Errors'
            ));

            return $this->redirectSettingsAuto();
        }

        $this->addFlash('warning', $this->trans(
            'Form not correctly sent!!',
            'Modules.Molonies.Errors'
        ));

        return $this->redirectSettingsAuto();
    }
}
