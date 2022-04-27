<?php

namespace Moloni\Controller\Admin\Login;

use Db;
use Moloni\Api\Curl;
use Moloni\Api\Endpoints\Companies;
use Moloni\Controller\Admin\General;
use Moloni\Helpers\Error;
use Moloni\Helpers\Moloni;
use Moloni\Webservice\Webservice;
use PrestaShop\PrestaShop\Adapter\Entity\Tools;
use PrestaShopException as PrestaShopExceptionAlias;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use function Moloni\Controller\pSQL;
use const Moloni\Controller\_PS_BASE_URL_SSL_;
use const Moloni\Controller\_PS_MODULE_DIR_;

class Login extends General
{
    /**
     * Renders the form to sign in
     *
     * @return Response renders the login form
     *
     * @throws PrestaShopExceptionAlias
     */
    public function display()
    {
        // if login is valid, redirect to orders page
        if ($this->checkLogin()) {
            Error::addError($this->trans(
                'User already logged in!!',
                'Modules.Molonies.Errors'
            ));
            $this->getUserErrorMessage();

            return $this->redirectOrders();
        }

        $form = $this->buildForm();

        return $this->render(
            '@Modules/molonies/views/templates/admin/login/Login.twig', [
            'devConnect' => $form->createView(),
            'img' => _MODULE_DIR_ . 'molonies/views/img/logoBig.png',
        ]);
    }

    /**
     * Gets the login form data
     *
     * @return RedirectResponse redirect to moloni login page or previous page if not valid
     */
    public function displaySubmit(Request $request = null)
    {
        $form = $this->buildForm();

        if ($request !== null && $request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $submittedData = $form->getData();

                // remove spaces and tabs form strings, because copy + paste from moloni has them
                $submittedData['developerID'] = preg_replace('/\s+/', '', $submittedData['developerID']);
                $submittedData['clientSecret'] = preg_replace('/\s+/', '', $submittedData['clientSecret']);

                $dataBase = Db::getInstance();
                $dataBase->execute('TRUNCATE ' . _DB_PREFIX_ . 'moloni_app');
                $dataBase->insert('moloni_app', [
                    'client_id' => pSQL($submittedData['developerID']),
                    'client_secret' => pSQL($submittedData['clientSecret']),
                    'access_token' => '',
                    'refresh_token' => '',
                    'company_id' => '',
                    'date_login' => '',
                    'access_expire' => '',
                    'refresh_expire' => '',
                ]);

                // url to get back to right action
                $redirectUri = _PS_BASE_URL_SSL_ . $this->generateUrl(
                    'moloni_es_login_retrievecode',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                Moloni::fillCache();

                // url to login in moloni.es
                $url = 'https://api.moloni.es/v1/auth/authorize?apiClientId=' .
                    $submittedData['developerID'] .
                    '&redirectUri=' . $redirectUri;

                return $this->redirect($url, 302);
            } else {
                $this->addFlash(
                    'warning',
                    $this->trans(
                        'Form is not valid!!',
                        'Modules.Molonies.Errors'
                    )
                );
            }
        } else {
            $this->addFlash(
                'warning',
                $this->trans(
                    'Must be an valid POST request!!',
                    'Modules.Molonies.Errors'
                )
            );
        }

        return $this->redirectLogin();
    }

    /**
     * Displays the companies that the user has
     *
     * @return Response renders the page to choose company or redirects to login if database empty
     */
    public function displayCompanies()
    {
        $companiesId = Companies::queryMe();

        if (isset($companiesId['errors'])) {
            Error::addError('Error getting user companies!!');
            $this->getUserErrorMessage();

            return $this->redirectLogin();
        }

        if (empty($companiesId)) {
            $this->addFlash('error', $this->trans('You have no companies!!', 'Modules.Molonies.Errors'));

            return $this->redirectLogin();
        }

        $companies = [];

        // fill array with available companies to chose
        foreach ($companiesId['data']['me']['data']['userCompanies'] as $aux) {
            $variables = [
                'companyId' => $aux['company']['companyId'],
                'options' => ['defaultLanguageId' => 1],
            ];
            $queryCompaniesInfo = Companies::queryCompany($variables);

            // adds error to log, user view and show last request values
            if (isset($queryCompaniesInfo['errors'])) {
                Error::addError('Error getting company info!!');
                $this->getUserErrorMessage();

                return $this->redirectLogin();
            }

            $companies[] = $queryCompaniesInfo['data']['company']['data'];
        }

        return $this->render(
            '@Modules/molonies/views/templates/admin/login/LoginCompanies.twig',
            [
                'arrayCompanies' => $companies,
                'redirectRoute' => 'moloni_es_login_company',
            ]);
    }

    /**
     * Retrieve the company that the user choose before
     *
     * @param Request|null $request request data
     *
     * @return Response redirect to settings page or to login if not valid
     */
    public function displayCompaniesSubmit(Request $request = null)
    {
        if ($request !== null && $request->isMethod('GET')) {
            $companyId = Tools::getValue('id');

            if (empty($companyId)) {
                $this->addFlash('error', $this->trans('Received id is empty!!', 'Modules.Molonies.Errors'));

                return $this->redirectLogin();
            }

            $dataBaseId = Moloni::get('id');

            if (!$dataBaseId) {
                $this->addFlash('error', $this->trans('No data in database!!', 'Modules.Molonies.Errors'));

                return $this->redirectLogin();
            }

            $dataBase = Db::getInstance();
            $dataBase->update('moloni_app', [
                'company_id' => pSQL($companyId),
            ], 'id =' . $dataBaseId, 1, false);

            Moloni::fillCache();
            // create webhooks after login
            Webservice::createHooks();

            $this->addFlash('success', $this->trans('Login was successful.', 'Modules.Molonies.Success'));

            return $this->redirectSettingsIndex();
        }

        $this->addFlash('error', $this->trans('Must be a GET request!!', 'Modules.Molonies.Errors'));

        return $this->redirectLogin();
    }

    /**
     * Retrieves the code after the
     *
     * @return Response|null redirects to login page if error or redirects to choose company
     */
    public function retrieveCode()
    {
        $code = Tools::getValue('code');

        if (empty($code)) {
            $this->addFlash('warning', $this->trans('Code cannot be empty!!', 'Modules.Molonies.Errors'));

            return $this->redirectLogin();
        }

        $resArray = Curl::login($code);

        if ($resArray != false) {
            $dataBaseId = Moloni::get('id');

            $dataBase = Db::getInstance();
            $dataBase->update('moloni_app', [
                    'access_token' => pSQL($resArray['accessToken']),
                    'refresh_token' => pSQL($resArray['refreshToken']),
                    'date_login' => time(),
                    'access_expire' => (time() + 3600),
                    'refresh_expire' => (time() + 864000),
                ], 'id =' . $dataBaseId, 1, false);

            Moloni::fillCache();

            return $this->displayCompanies();
        }

        $this->getUserErrorMessage();

        return $this->redirectLogin();
    }

    /**
     * Builds and return the login form
     *
     * @return FormInterface return the login form
     */
    private function buildForm()
    {
        return $this->createFormBuilder()
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
    }
}
