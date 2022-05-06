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

use Doctrine\Persistence\ManagerRegistry;
use Moloni\Api\MoloniApi;
use Moloni\Api\MoloniApiClient;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Entity\MoloniApp;
use Moloni\Enums\Domains;
use Moloni\Exceptions\MoloniException;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class Login extends MoloniController
{
    public function home(): Response
    {
        $form = $this
            ->getLoginFormBuilder()
            ->getForm();

        return $this->render(
            '@Modules/molonies/views/templates/admin/login/Login.twig',
            [
                'form' => $form->createView(),
                'img' => _MODULE_DIR_ . 'molonies/views/img/logoBig.png',
            ]
        );
    }

    public function submit(Request $request, ManagerRegistry $doctrine): RedirectResponse
    {
        $form = $this->getLoginFormBuilder()
            ->getForm()
            ->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $msg = $this->trans('Form is not valid!!', 'Modules.Molonies.Errors');

            $this->addWarningMessage($msg);

            return $this->redirectToLogin();
        }

        $formData = $form->getData();

        $clientId = preg_replace('/\s+/', '', $formData['clientID']);
        $clientSecret = preg_replace('/\s+/', '', $formData['clientSecret']);

        $entityManager = $doctrine->getManager();

        $moloniApp = new MoloniApp();
        $moloniApp->setClientId($clientId);
        $moloniApp->setClientSecret($clientSecret);
        $moloniApp->setCompanyId(0);
        $moloniApp->setLoginDate(time());

        $entityManager->persist($moloniApp);
        $entityManager->flush();

        $redirectUri = _PS_BASE_URL_SSL_ . $this->generateUrl('moloni_es_login_retrievecode', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $url = Domains::MOLONI_API . "/auth/authorize?apiClientId=$clientId&redirectUri=$redirectUri";

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
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);

            return $this->redirectToLogin();
        }

        return $this->redirectToCompanySelect();
    }

    public function companySelect(Request $request): Response
    {
        $companies = [];

        try {
            $me = MoloniApiClient::companies()
                ->queryMe();

            if (empty($me)) {
                throw new MoloniException('You have no companies!!');
            }

            foreach ($me['data']['me']['data']['userCompanies'] as $userCompany) {
                $variables = [
                    'companyId' => $userCompany['company']['companyId'],
                ];

                $userCompanyInfo = MoloniApiClient::companies()
                    ->queryCompany($variables);

                if (isset($userCompanyInfo['data']['company']['data'])) {
                    $companies[] = $userCompanyInfo['data']['company']['data'];
                }
            }
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg);

            return $this->redirectToLogin();
        }

        return $this->render(
            '@Modules/molonies/views/templates/admin/login/LoginCompanies.twig',
            [
                'companies' => $companies,
                'redirectRoute' => 'moloni_es_login_company_submit',
            ]
        );
    }

    public function companySelectSubmit(?int $companyId, ManagerRegistry $doctrine): RedirectResponse
    {
        try {
            if (!is_numeric($companyId) || $companyId < 0) {
                throw new MoloniException('ID is invalid');
            }

            $moloniApp = MoloniApi::getAppEntity();
            $moloniApp->setCompanyId($companyId);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($moloniApp);
            $entityManager->flush();

            $msg = $this->trans('Company selected successfully', 'Modules.Molonies.Success');
            $this->addSuccessMessage($msg);
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg);

            return $this->redirectToCompanySelect();
        }

        return $this->redirectToSettings();
    }

    private function getLoginFormBuilder(): FormBuilderInterface
    {
        return $this->createFormBuilder()
            ->add('clientID', TextType::class, [
                'label' => $this->trans('Developer ID', 'Modules.Molonies.Login'),
                'required' => true,
                'label_attr' => ['class' => 'loginLabel'],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('clientSecret', TextType::class, [
                'label' => $this->trans('Client Secret', 'Modules.Molonies.Login'),
                'required' => true,
                'label_attr' => ['class' => 'loginLabel'],
                'constraints' => [
                    new Length(['min' => 3]),
                    new NotBlank(),
                ],
            ])
            ->add('connect', SubmitType::class, [
                'attr' => ['class' => 'btn-primary'],
                'label' => $this->trans('Connect', 'Modules.Molonies.Login'),
            ])
            ->add('reset', ResetType::class, [
                'attr' => ['class' => 'btn-primary'],
                'label' => $this->trans('Reset', 'Modules.Molonies.Login'),
            ])
            ->setAction($this->generateUrl('moloni_es_login_submit'))
            ->setMethod('POST');
    }
}
