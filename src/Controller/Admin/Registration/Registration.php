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

use Tools;
use Moloni\Actions\Registration\IsFormValid;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Enums\MoloniRoutes;
use Moloni\Exceptions\MoloniException;
use Moloni\Form\Registration\RegistrationFormHandler;
use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Registration extends MoloniController
{
    public function home(Request $request)
    {
        /** @var RegistrationFormHandler $registrationFormHandler */
        $registrationFormHandler = $this->getRegistrationFormHandler();

        $registrationForm = $registrationFormHandler->getForm();
        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            try {
                $validator = new IsFormValid($registrationForm->getData(), $this->getContext()->getTranslator());

                if (!$validator->isValid()) {
                    throw new MoloniException('An unexpected error occurred', [], ['errors' => $validator->getErrors()]);
                }

                $registrationFormHandler->submit($registrationForm->getData());

                $this->addSuccessMessage(
                    $this->trans(
                        'A confirmation email has been sent to your email address.',
                        'Modules.Molonies.Common'
                    )
                );

                return $this->redirectToLogin();
            } catch (MoloniException $e) {
                $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors');

                $this->addErrorMessage($msg, $e->getData());
            }
        }

        return $this->render(
            '@Modules/molonies/views/templates/admin/registration/Registration.twig',
            [
                'form' => $registrationForm->createView(),
                'loginRoute' => MoloniRoutes::LOGIN,
                'verifyFormAction' => MoloniRoutes::REGISTRATION_VERIFY_FORM,
            ]
        );
    }

    public function verifyForm(): Response
    {
        $data = Tools::getValue('MoloniRegistration', []);

        $validator = new IsFormValid($data, $this->getContext()->getTranslator());

        $response = [
            'valid' => $validator->isValid(),
            'errors' => $validator->getErrors(),
            'post' => [
                'formData' => $data
            ]
        ];

        return new Response(json_encode($response));
    }

    private function getRegistrationFormHandler(): object
    {
        return $this->get('moloni.registration.form');
    }
}
