<?php

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
                'attr' => ['class' => 'selectPS'],
                'label_attr' => ['class' => 'labelPS col-sm-2'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Moloniprestashopes.Settings') => '1',
                    $this->trans('No', 'Modules.Moloniprestashopes.Settings') => '0',
                ],
                'data' => Settings::get('CreateAuto'),
            ])
            ->add('Stocks', ChoiceType::class, [
                'label' => $this->trans('Synchronize stocks', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => 'selectPS'],
                'label_attr' => ['class' => 'labelPS col-sm-2'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Moloniprestashopes.Settings') => '1',
                    $this->trans('No', 'Modules.Moloniprestashopes.Settings') => '0',
                ],
                'data' => Settings::get('Stocks'),
            ])
            ->add('AddProducts', ChoiceType::class, [
                'label' => $this->trans('Create products on Moloni', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => 'selectPS'],
                'label_attr' => ['class' => 'labelPS col-sm-2'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Moloniprestashopes.Settings') => '1',
                    $this->trans('No', 'Modules.Moloniprestashopes.Settings') => '0',
                ],
                'data' => Settings::get('AddProducts'),
            ])
            ->add('UpdateArtigos', ChoiceType::class, [
                'label' => $this->trans('Update products on Moloni', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => 'selectPS'],
                'label_attr' => ['class' => 'labelPS col-sm-2'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Moloniprestashopes.Settings') => '1',
                    $this->trans('No', 'Modules.Moloniprestashopes.Settings') => '0',
                ],
                'data' => Settings::get('UpdateArtigos'),
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
                $sql = 'SELECT * FROM ' . _DB_PREFIX_ . "moloni_settings WHERE label ='CreateAuto' AND store_id=1";
                $existRes = $dbPresta->executeS($sql);
                if (empty($existRes)) {
                    foreach ($submitData as $label => $value) {
                        $dbPresta->insert('moloni_settings', [
                            'store_id' => (int) '1',
                            'label' => pSQL($label),
                            'value' => pSQL($value),
                        ]);
                    }
                    Settings::fillCache();
                    $this->addFlash('success', $this->trans(
                        'Settings created.',
                        'Modules.Moloniprestashopes.Success'
                    ));

                    return $this->redirectSettingsAuto();
                } else {
                    foreach ($submitData as $label => $value) {
                        $dbPresta->update('moloni_settings', [
                            'value' => pSQL($value),
                        ], 'store_id = 1 AND label="' . $label . '"');
                    }
                    Settings::fillCache();
                    $this->addFlash('success', $this->trans(
                        'Settings updated.',
                        'Modules.Moloniprestashopes.Success'
                    ));

                    return $this->redirectSettingsAuto();
                }
            } else {
                $this->addFlash('warning', $this->trans(
                    'Form not valid!!',
                    'Modules.Moloniprestashopes.Errors'
                ));

                return $this->redirectSettingsAuto();
            }
        }
        $this->addFlash('warning', $this->trans(
            'Form not correctly sent!!',
            'Modules.Moloniprestashopes.Errors'
        ));

        return $this->redirectSettingsAuto();
    }
}
