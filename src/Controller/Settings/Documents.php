<?php

namespace Moloni\Controller\Settings;

use Db;
use Moloni\Api\Documents as apiDocuments;
use Moloni\Controller\General;
use Moloni\Models\Moloni;
use Moloni\Models\Settings;
use PrestaShopDatabaseException;
use PrestaShopException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class Documents extends General
{
    /**
     * Checks for valid token and login, builds form and displays template
     *
     * @return null returns the template with form
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function display()
    {
        if (!$this->checkTokenRedirect()) {
            return $this->redirectLogin();
        }

        $form = $this->buildFormDocs();

        $aux = 'index';

        return $this->render('@Modules/moloniprestashopes/src/View/Templates/Admin/Settings/Documents.twig', [
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
    public function buildFormDocs()
    {
        $variables = ['companyId' => (int) Moloni::get('company_id'), 'options' => null];
        $sets = apiDocuments::queryDocumentSets($variables);
        $setsData = $sets;
        $countSetsData = count($setsData);

        for ($i = 0; $i < $countSetsData; ++$i) {
            $choicesSet[$setsData[$i]['name']] = $setsData[$i]['documentSetId'];
        }

        $addressPS = \Store::getStores(1);
        $choicesAddress['MoloniES'] = 'moloni';
        foreach ($addressPS as $key => $address) {
            $choicesAddress[$address['name']] = $address['id'];
        }

        return $this->createFormBuilder()
            ->add('Set', ChoiceType::class, [
                'label' => $this->trans('Document set', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $choicesSet,
                'data' => Settings::get('Set') !== false ? Settings::get('Set') : null,
            ])
            ->add('Type', ChoiceType::class, [
                'label' => $this->trans('Document type', 'Modules.Moloniprestashopes.Settings'),
                'choices' => $this->getDocumentsTypes(),
                'label_attr' => ['class' => 'form-control-label'],
                'attr' => ['onchange' => 'onStatusChange()'],
                'data' => Settings::get('Type') !== false ? Settings::get('Type') : null,
            ])
            ->add('Status', ChoiceType::class, [
                'label' => $this->trans('Document status', 'Modules.Moloniprestashopes.Settings'),
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Draft', 'Modules.Moloniprestashopes.Settings') => '0',
                    $this->trans('Closed', 'Modules.Moloniprestashopes.Settings') => '1',
                ],
                'attr' => ['onchange' => 'onStatusChange()'],
                'data' => Settings::get('Status') !== false ? Settings::get('Status') : null,
            ])
            ->add('Send', ChoiceType::class, [
                'label' => $this->trans('Shipping information', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Moloniprestashopes.Settings') => '1',
                    $this->trans('No', 'Modules.Moloniprestashopes.Settings') => '0',
                ],
                'data' => Settings::get('Send'),
            ])
            ->add('Transport', ChoiceType::class, [
                'label' => $this->trans('Document transport', 'Modules.Moloniprestashopes.Settings'),
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Moloniprestashopes.Settings') => '1',
                    $this->trans('No', 'Modules.Moloniprestashopes.Settings') => '0',
                ],
                'attr' => ['onchange' => 'onStatusChange2()'],
                'data' => Settings::get('Transport'),
            ])
            ->add('Address', ChoiceType::class, [
                'label' => $this->trans('Loading address', 'Modules.Moloniprestashopes.Settings'),
                'attr' => ['class' => ''],
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => $choicesAddress,
                'data' => Settings::get('Address') !== false ? Settings::get('Address') : null,
            ])
            ->add('SendEmail', ChoiceType::class, [
                'label' => $this->trans('Send e-mail', 'Modules.Moloniprestashopes.Settings'),
                'label_attr' => ['class' => 'form-control-label'],
                'choices' => [
                    $this->trans('Yes', 'Modules.Moloniprestashopes.Settings') => '1',
                    $this->trans('No', 'Modules.Moloniprestashopes.Settings') => '0',
                ],
                'attr' => ['onchange' => 'onStatusChange2()'],
                'data' => Settings::get('SendEmail'),
            ])
            ->add('SaveChanges', SubmitType::class, [
                'attr' => ['class' => 'btn-outline-success'],
                'label' => $this->trans('Save changes', 'Modules.Moloniprestashopes.Settings'),
            ])
            ->setAction($this->generateUrl('moloni_es_settings_documents_submit'))
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
        $form = $this->buildFormDocs();

        if ($request !== null && $request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $submitData = $form->getData();

                // invoice + receipt cant be draft
                if ($form->get('Status')->getViewData() == 0
                    && $form->get('Type')->getViewData() == 'receipts') {
                    // maybe trow and error here
                    $this->addFlash('warning', $this->trans(
                        'Cannot save "Invoice + Receipt" and "Draft" at the same time!!',
                        'Modules.Moloniprestashopes.Errors'
                    ));

                    return $this->redirectSettingsIndex();
                }

                // Transport cant be draft
                if ($form->get('Status')->getViewData() == 0
                    && $form->get('Transport')->getViewData() == 1) {
                    // maybe trow and error here
                    $this->addFlash('warning', $this->trans(
                        'Cannot save Transport documents and "Draft" at the same time!!',
                        'Modules.Moloniprestashopes.Errors'
                    ));

                    return $this->redirectSettingsIndex();
                }

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
                    'Settings created.',
                    'Modules.Moloniprestashopes.Success'
                ));

                return $this->redirectSettingsIndex();
            }

            $this->addFlash('warning', $this->trans(
                'Form not valid!!',
                'Modules.Moloniprestashopes.Errors'
            ));

            return $this->redirectSettingsIndex();
        }

        $this->addFlash('warning', $this->trans(
            'Form not correctly sent!!',
            'Modules.Moloniprestashopes.Errors'
        ));

        return $this->redirectSettingsIndex();
    }
}
