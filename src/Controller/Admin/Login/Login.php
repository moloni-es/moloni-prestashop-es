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

use Moloni\Controller\Admin\Controller;
use Moloni\Enums\Domains;
use PrestaShop\PrestaShop\Adapter\Entity\Tools;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class Login extends Controller
{
    public function home(Request $request): Response
    {
        // todo: this

        $form = $this->createFormBuilder()
            ->add('developerID', TextType::class, [
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
            ->setMethod('POST')
            ->getForm();

        return $this->render(
            '@Modules/molonies/views/templates/admin/login/Login.twig', [
            'devConnect' => $form->createView(),
            'img' => _MODULE_DIR_ . 'molonies/views/img/logoBig.png',
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        // todo: this

        $url = Domains::MOLONI_API . '/auth/authorize?apiClientId=';

        if ($request) {
            return $this->redirect($url);
        }

        return $this->redirectToLogin();
    }

    public function retrieveCode(Request $request): RedirectResponse
    {
        // todo: this

        $code = Tools::getValue('code');

        if (!empty($code)) {
            return $this->redirectToLogin();
        }

        return $this->redirectToCompanySelect();
    }

    public function companySelect(Request $request): Response
    {
        return $this->render(
            '@Modules/molonies/views/templates/admin/login/LoginCompanies.twig',
            [
                'arrayCompanies' => [],
                'redirectRoute' => 'moloni_es_login_company_submit',
            ]
        );
    }

    public function companySelectSubmit(Request $request): RedirectResponse
    {
        // todo: this

        if ($request) {
            return $this->redirectToSettingsDocuments();
        }

        return $this->redirectToCompanySelect();
    }
}
