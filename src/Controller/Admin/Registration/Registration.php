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

namespace Moloni\Controller\Admin\Registration;

use Moloni\Controller\Admin\MoloniController;
use Moloni\Enums\MoloniRoutes;
use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

class Registration extends MoloniController
{
    public function home(Request $request)
    {
        $registrationFormHandler = $this->getRegistrationFormHandler();

        $registrationForm = $registrationFormHandler->getForm();
        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            try {
                $errors = $registrationFormHandler->save($registrationForm->getData());
            } catch (\Exception $e) {
                $errors = [];
                $errors[] = $e->getMessage();
            }

            if (empty($errors)) {
                $this->addSuccessMessage(
                    $this->trans(
                        'A confirmation email has been sent to your email address.',
                        'Modules.Molonies.Common'
                    )
                );

                return $this->redirectToLogin();
            }

            foreach ($errors as $error) {
                $this->addErrorMessage($error);
            }
        }

        return $this->render(
            '@Modules/molonies/views/templates/admin/registration/Registration.twig',
            [
                'form' => $registrationForm->createView(),
                'img' => _MODULE_DIR_ . 'molonies/views/img/moloni_logo_colors.svg',
                'login_route' => MoloniRoutes::LOGIN,
            ]
        );
    }

    private function getRegistrationFormHandler(): FormHandlerInterface
    {
        return $this->get('moloni.registration.form');
    }
}
