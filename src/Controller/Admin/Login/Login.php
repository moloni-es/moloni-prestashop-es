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

namespace Moloni\Controller\Admin\Login;

use Shop;
use Moloni\Api\MoloniApi;
use Moloni\Api\MoloniApiClient;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Entity\MoloniApp;
use Moloni\Enums\Domains;
use Moloni\Enums\Languages;
use Moloni\Enums\MoloniRoutes;
use Moloni\Exceptions\MoloniException;
use Moloni\Form\Login\LoginFormType;
use Moloni\Repository\MoloniAppRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Login extends MoloniController
{
    public function home(): Response
    {
        $form = $this->createForm(LoginFormType::class, null, [
            'url' => $this->generateUrl(MoloniRoutes::LOGIN)
        ]);

        return $this->render(
            '@Modules/molonies/views/templates/admin/login/Login.twig',
            [
                'form' => $form->createView(),
                'img' => _MODULE_DIR_ . 'molonies/views/img/moloni_logo_colors.svg',
                'registration_route' => MoloniRoutes::REGISTRATION,
            ]
        );
    }

    public function submit(Request $request): RedirectResponse
    {
        $form = $this->createForm(LoginFormType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $msg = $this->trans('Form is not valid!!', 'Modules.Molonies.Errors');

            $this->addWarningMessage($msg);

            return $this->redirectToLogin();
        }

        /** @var MoloniAppRepository $appRepository */
        $appRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(MoloniApp::class);

        $appRepository->deleteApp();

        $formData = $form->getData();

        $clientId = trim($formData['clientID']);
        $clientSecret = trim($formData['clientSecret']);

        $moloniApp = new MoloniApp();
        $moloniApp->setClientId($clientId);
        $moloniApp->setClientSecret($clientSecret);
        $moloniApp->setCompanyId(0);
        $moloniApp->setShopId(Shop::getContextShopID() ?? 0);

        $entityManager = $this
            ->getDoctrine()
            ->getManager();
        $entityManager->persist($moloniApp);
        $entityManager->flush();

        $redirectUri = defined('_PS_BASE_URL_SSL_') ? _PS_BASE_URL_SSL_ : '';
        $redirectUri .= $this->generateUrl(MoloniRoutes::LOGIN_RETRIEVE_CODE, [], UrlGeneratorInterface::ABSOLUTE_URL);

        $url = Domains::MOLONI_API;
        $url .= "/auth/authorize?apiClientId=$clientId&redirectUri=$redirectUri";

        return $this->redirect($url);
    }

    public function retrieveCode(Request $request): RedirectResponse
    {
        $code = $request->get('code', '');

        try {
            if (empty($code)) {
                throw new MoloniException('Code cannot be empty!');
            }

            MoloniApi::login($code);
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg);

            return $this->redirectToLogin();
        }

        return $this->redirectToCompanySelect();
    }

    public function companySelect(Request $request)
    {
        $companies = [];

        try {
            $query = MoloniApiClient::companies()
                ->queryMe();

            $queryCompanies = $query['data']['me']['data']['userCompanies'] ?? [];
            $queryLanguageId = $query['data']['me']['data']['language']['languageId'] ?? Languages::EN;

            if (empty($queryCompanies)) {
                throw new MoloniException('You have no companies!!');
            }

            foreach ($queryCompanies as $company) {
                $variables = [
                    'companyId' => $company['company']['companyId'],
                    'options' => [
                        'defaultLanguageId' => $queryLanguageId
                    ]
                ];

                $userCompanyInfo = MoloniApiClient::companies()->queryCompany($variables);

                if (!empty($userCompanyInfo)) {
                    $companies[] = $userCompanyInfo;
                }
            }
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg);

            return $this->redirectToLogin();
        }

        return $this->render(
            '@Modules/molonies/views/templates/admin/login/Companies.twig',
            [
                'companies' => $companies,
                'submit_route' => MoloniRoutes::LOGIN_COMPANY_SUBMIT,
                'default_img' => _MODULE_DIR_ . 'molonies/views/img/companyDefault.png',
                'media_api_url' => Domains::MOLONI_MEDIA_API,
            ]
        );
    }

    public function companySelectSubmit(?int $companyId): RedirectResponse
    {
        try {
            if (!is_numeric($companyId) || $companyId < 0) {
                throw new MoloniException('ID is invalid');
            }

            $moloniApp = MoloniApi::getAppEntity();

            if (!$moloniApp) {
                throw new MoloniException('Missing information in database');
            }

            $moloniApp->setCompanyId($companyId);

            $entityManager = $this
                ->getDoctrine()
                ->getManager();
            $entityManager->persist($moloniApp);
            $entityManager->flush();

            $msg = $this->trans('Company selected successfully', 'Modules.Molonies.Common');
            $this->addSuccessMessage($msg);
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg);

            return $this->redirectToCompanySelect();
        }

        return $this->redirectToSettings();
    }
}
