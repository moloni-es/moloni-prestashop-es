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
 */

namespace Moloni\Controller\Admin\Settings;

use Moloni\Controller\Admin\MoloniController;
use Moloni\Form\Settings\SettingsFormHandler;
use Moloni\Services\MoloniContext;
use Moloni\Tools\Settings as SettingsTools;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Settings extends MoloniController
{
    private $formHandler;

    public function __construct(MoloniContext $context, SettingsFormHandler $formHandler)
    {
        parent::__construct($context);

        $this->formHandler = $formHandler;
    }

    public function home(Request $request): Response
    {
        $settingsForm = $this->formHandler->getForm();
        $settingsForm->handleRequest($request);

        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            try {
                $errors = $this->formHandler->save($settingsForm->getData());
            } catch (\Exception $e) {
                $errors = [];
                $errors[] = $e->getMessage();
            }

            if (empty($errors)) {
                $this->addSuccessMessage(
                    $this->trans(
                        'Your module settings were successfuly updated.',
                        'Modules.Molonies.Settings'
                    )
                );
                return $this->redirectToSettings();
            }

            $this->flashErrors($errors);
        }

        return $this->render(
            '@Modules/molonies/views/templates/admin/settings/Settings.twig',
            [
                'form' => $settingsForm->createView(),
                'companyName' => SettingsTools::get('companyName'),
            ]
        );
    }
}
