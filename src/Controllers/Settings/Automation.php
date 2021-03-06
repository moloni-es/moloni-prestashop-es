<?php

/** @noinspection ALL */

namespace Moloni\ES\Controllers\Settings;

use Db;
use Moloni\ES\Controllers\General;
use Moloni\ES\Controllers\Models\Settings;
use PrestaShopDatabaseException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

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

        return $this->render('@Modules/moloniprestashopes/src/View/Templates/Admin/Settings/Automation.twig', [
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
                'label' => $this->trans('Create paid documents on Moloni', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Moloniprestashopes.Settings') => '1',
                    $this->trans('No', 'Modules.Moloniprestashopes.Settings') => '0',
                ],
                'data' => Settings::get('CreateAuto'),
            ])
            ->add('Stocks', ChoiceType::class, [
                'label' => $this->trans('Synchronize stocks on Moloni', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Moloniprestashopes.Settings') => '1',
                    $this->trans('No', 'Modules.Moloniprestashopes.Settings') => '0',
                ],
                'data' => Settings::get('Stocks'),
            ])
            ->add('AddProducts', ChoiceType::class, [
                'label' => $this->trans('Create products on Moloni', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Moloniprestashopes.Settings') => '1',
                    $this->trans('No', 'Modules.Moloniprestashopes.Settings') => '0',
                ],
                'data' => Settings::get('AddProducts'),
            ])
            ->add('UpdateArtigos', ChoiceType::class, [
                'label' => $this->trans('Update products on Moloni', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Moloniprestashopes.Settings') => '1',
                    $this->trans('No', 'Modules.Moloniprestashopes.Settings') => '0',
                ],
                'data' => Settings::get('UpdateArtigos'),
            ])
            ->add('HooksVariantsUpdate', ChoiceType::class, [
                'label' => $this->trans('Update products with variants', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Moloniprestashopes.Settings') => '1',
                    $this->trans('No', 'Modules.Moloniprestashopes.Settings') => '0',
                ],
                'data' => Settings::get('HooksVariantsUpdate'),
            ])
            ->add('HooksAddProducts', ChoiceType::class, [
                'label' => $this->trans('Add products', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Moloniprestashopes.Settings') => '1',
                    $this->trans('No', 'Modules.Moloniprestashopes.Settings') => '0',
                ],
                'data' => Settings::get('HooksAddProducts'),
            ])
            ->add('HooksUpdateProducts', ChoiceType::class, [
                'label' => $this->trans('Update products', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Moloniprestashopes.Settings') => '1',
                    $this->trans('No', 'Modules.Moloniprestashopes.Settings') => '0',
                ],
                'data' => Settings::get('HooksUpdateProducts'),
            ])
            ->add('HooksUpdateStock', ChoiceType::class, [
                'label' => $this->trans('Update stock', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Moloniprestashopes.Settings') => '1',
                    $this->trans('No', 'Modules.Moloniprestashopes.Settings') => '0',
                ],
                'data' => Settings::get('HooksUpdateStock'),
            ])
            ->add('SyncFields', ChoiceType::class, [
                'label' => $this->trans('Fields to sync', 'Modules.Moloniprestashopes.Settings'),
                'label_attr' => ['class' => 'form-control-label'],
                'multiple' => true,
                'expanded' => true,
                'choices' => [
                    $this->trans('Name', 'Modules.Moloniprestashopes.Settings') => 'Name',
                    $this->trans('Price', 'Modules.Moloniprestashopes.Settings') => 'Price',
                    $this->trans('Description', 'Modules.Moloniprestashopes.Settings') => 'Description',
                    $this->trans('Visibility', 'Modules.Moloniprestashopes.Settings') => 'Visibility',
                    $this->trans('Stock', 'Modules.Moloniprestashopes.Settings') => 'Stock',
                    $this->trans('Categories', 'Modules.Moloniprestashopes.Settings') => 'Categories',
                ],
                'data' => Settings::get('SyncFields') !== false ? unserialize(Settings::get('SyncFields')) : [],
            ])
            ->add('SaveChanges', SubmitType::class, [
                'attr' => ['class' => 'btn-outline-success'],
                'label' => $this->trans('Save changes', 'Modules.Moloniprestashopes.Settings'),
            ])
            ->setAction($this->generateUrl('moloni_es_settings_automation_submit'))
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
                    'Modules.Moloniprestashopes.Success'
                ));

                return $this->redirectSettingsAuto();
            }
            $this->addFlash('warning', $this->trans(
                'Form not valid!!',
                'Modules.Moloniprestashopes.Errors'
            ));

            return $this->redirectSettingsAuto();
        }

        $this->addFlash('warning', $this->trans(
            'Form not correctly sent!!',
            'Modules.Moloniprestashopes.Errors'
        ));

        return $this->redirectSettingsAuto();
    }
}
