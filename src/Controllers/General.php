<?php

namespace Moloni\ES\Controllers;

use Db;
use Moloni\ES\Controllers\Api\Curl;
use Moloni\ES\Controllers\Api\Documents;
use Moloni\ES\Controllers\Api\Products;
use Moloni\ES\Controllers\Models\Company;
use Moloni\ES\Controllers\Models\Error;
use Moloni\ES\Controllers\Models\Log;
use Moloni\ES\WebHooks\WebHooks;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

class General extends FrameworkBundleAdminController
{
    /**
     * Checks if Tokens and Login are still valid
     *
     * @return null to index login page in case they are not valid
     */
    public function checkTokenRedirect()
    {
        if (!$this->checkTokens()) {
            $this->getUserErrorMessage();

            return false;
        }

        if (!$this->checkLogin()) {
            $this->getUserErrorMessage();

            return false;
        }

        return true;
    }

    /**
     * Redirect to Login index Page
     *
     * @return null redirect to index login page
     */
    public function redirectLogin()
    {
        $this->resetCompany();

        return $this->redirectToRoute('moloni_es_login_index');
    }

    /**
     * Redirect to Settings Products page
     *
     * @return null redirect to settings products page
     */
    public function redirectSettingsProducts()
    {
        return $this->redirectToRoute('moloni_es_settings_products');
    }

    /**
     * Redirect to Index Settings Page
     *
     * @return null redirect to index settings page
     */
    public function redirectSettingsIndex()
    {
        return $this->redirectToRoute('moloni_es_settings_index');
    }

    /**
     * Redirect to Automation settings page
     *
     * @return null redirect to automation settings page
     */
    public function redirectSettingsAuto()
    {
        return $this->redirectToRoute('moloni_es_settings_automation');
    }

    /**
     * Redirect to Tools settings page
     *
     * @return null redirect to automation settings page
     */
    public function redirectSettingsTools()
    {
        return $this->redirectToRoute('moloni_es_settings_tools');
    }

    /**
     * Redirect to documents settings page
     *
     * @return null redirect to automation settings page
     */
    public function redirectDocuments()
    {
        return $this->redirectToRoute('moloni_es_documents_index');
    }

    /**
     * Redirect to Orders page
     *
     * @return null redirect to automation settings page
     */
    public function redirectOrders()
    {
        return $this->redirectToRoute('moloni_es_home_index');
    }

    /**
     * Checks if the user is logged in
     *
     * @return array[]|bool
     */
    public function checkLogin()
    {
        $array = Company::getAll();

        if (empty($array) || $array['company_id'] == '0' || $array['date_login'] == '') {
            Error::addError($this->trans('No user registered!!', 'Modules.Moloniprestashopes.Errors'));

            return false;
        }

        return true;
    }

    /**
     * Checks the validity of the tokens
     *
     * @return array|bool returns true if successful or an array with the error
     */
    public function checkTokens()
    {
        //get saved data in moloni_app table
        $dataArray = Company::getAll();
        if ($dataArray === false || empty($dataArray)) {
            Log::writeLog($this->trans('Database is empty!!', 'Modules.Moloniprestashopes.Errors'));

            return false;
        }

        //if refresh token is expired new login is required
        if ($dataArray['refresh_expire'] < (time())) {
            Log::writeLog($this->trans('Refresh token has expired', 'Modules.Moloniprestashopes.Errors'));

            Error::addError(
                $this->trans(
                    'Session has expired!!',
                    'Modules.Moloniprestashopes.Errors'
                )
            );

            return false;
        }

        //if access token is expired or about to (5 minutes), refresh it
        if ($dataArray['access_expire'] < (time() + 300)) {
            $newDataArray = Curl::refreshTokens();

            if (!isset($newDataArray['errors'])) {
                $dataBase = Db::getInstance();
                $dataBase->update('moloni_app', [
                    'access_token' => pSQL($newDataArray['accessToken']),
                    'refresh_token' => pSQL($newDataArray['refreshToken']),
                    'access_expire' => (time() + 3600),
                    'refresh_expire' => (time() + 864000),
                ], 'id =' . $dataArray['id'], 1, false);

                Company::fillCache();
                Log::writeLog($this->trans('Tokens Refreshed', 'Modules.Moloniprestashopes.Errors'));

                return true;
            } else {
                Log::writeLog($this->trans('Problem refreshing tokens!!', 'Modules.Moloniprestashopes.Errors'));
                Error::addError($this->trans('Problem refreshing tokens!!', 'Modules.Moloniprestashopes.Errors'));

                return false;
            }
        }

        return true;
    }

    /**
     * Resets the moloni_app table to force new login
     *
     * @return bool
     */
    public function resetCompany()
    {
        $dataBase = Db::getInstance();
        $dataBase->execute('TRUNCATE ' . _DB_PREFIX_ . 'moloni_app');

        return true;
    }

    /**
     * Deletes all data from the plugin database
     *
     * @return bool returns true
     */
    public function logOut()
    {
        //delete Webhooks on moloni
        WebHooks::deleteHooks();
        //deletes all data form the database tables
        $dataBase = Db::getInstance();
        $dataBase->execute('TRUNCATE ' . _DB_PREFIX_ . 'moloni_app');
        $dataBase->execute('TRUNCATE ' . _DB_PREFIX_ . 'moloni_settings');

        return $this->redirectLogin();
    }

    /**
     * Returns the documents that can be generated
     *
     * @return array returns array with documents and abbreviations
     */
    public function getDocumentsTypes()
    {
        return [
            $this->trans('Invoice', 'Modules.Moloniprestashopes.Settings') => 'invoices',
            $this->trans('Invoice + Receipt', 'Modules.Moloniprestashopes.Settings') => 'receipts',
            $this->trans('Purchase Order', 'Modules.Moloniprestashopes.Settings') => 'purchaseOrders',
            $this->trans('Pro Forma Invoice', 'Modules.Moloniprestashopes.Settings') => 'proFormaInvoices',
            $this->trans('Simplified invoice', 'Modules.Moloniprestashopes.Settings') => 'simplifiedInvoices',
        ];
    }

    /**
     * Adds error message to the user
     *
     * @return bool|void
     */
    public function getUserErrorMessage()
    {
        $error = Error::getErrors();

        if (empty($error)) {
            return;
        }

        $this->addFlash('error', $error . $this->getRequestDump());

        return true;
    }

    /**
     * Returns html with request error
     *
     * @return string|void
     */
    public function getRequestDump()
    {
        $msg = $this->trans('Click for more information.', 'Modules.Moloniprestashopes.Errors');
        $request = Error::getRequests();

        if (empty($request)) {
            return;
        }

        $error = '</br><a onclick="toggleDiv(\'toggleDiv\');" href="#">' . $msg . '</a></br>';
        $error .= '<div style="display: none;" id="toggleDiv">';

        foreach ($request as $key => $value) {
            $error .= '<br><b>' . $key . ': </b>' . print_r($value, true) . '<p></p>';
        }

        $error .= '</div>';

        return $error;
    }

    /**
     * Returns the pagination data
     *
     * @param $currentPage int the page where the user is trying to load
     * @param $entries int number of entries on the database
     *
     * @return array return current page, number of tabs, offSet and lines per page
     */
    public function getPaginator($currentPage, $entries)
    {
        $linesPerPage = 10; //lines per page
        $numberOfTabs = 1; //default number or tabs

        if ($currentPage <= 0 || !isset($currentPage)) {
            $currentPage = 1;
        }

        if ($entries <= $linesPerPage) {
            $currentPage = 1;
        } else {
            $numberOfTabs = ceil(($entries / $linesPerPage));

            if ($currentPage > $numberOfTabs) {
                $currentPage = $numberOfTabs;
            }
        }
        //where it starts depending on the page
        $offSet = ($currentPage * $linesPerPage) - $linesPerPage;

        return [
            'numberOfTabs' => $numberOfTabs,
            'currentPage' => $currentPage,
            'offSet' => $offSet,
            'linesPerPage' => $linesPerPage,
        ];
    }

    /**
     * Checks the validity of the tokens
     *
     * @return bool
     */
    public static function staticCheckTokens()
    {
        //get saved data in moloni_app table
        $dataArray = Company::getAll();

        if ($dataArray === false || empty($dataArray)) {
            return false;
        }

        //if refresh token is expired new login is required
        if ($dataArray['refresh_expire'] < (time())) {
            Log::writeLog('Login expired');

            return false;
        }

        //if access token is expired or about to (5 minutes), refresh it
        if ($dataArray['access_expire'] < (time() + 300)) {
            $newDataArray = Curl::refreshTokens();

            if (!isset($newDataArray['errors'])) {
                $dataBase = Db::getInstance();
                $dataBase->update('moloni_app', [
                    'access_token' => pSQL($newDataArray['accessToken']),
                    'refresh_token' => pSQL($newDataArray['refreshToken']),
                    'access_expire' => (time() + 3600),
                    'refresh_expire' => (time() + 864000),
                ], 'id =' . $dataArray['id'], 1, false);

                Company::fillCache();
                Log::writeLog('Tokens refreshed');

                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets ALL categories from Moloni, from top to bottom
     * First call $categoryParentId should be null
     *
     * @param $categoryParentId
     *
     * @return array
     */
    public static function getAllCategoriesFromMoloni($categoryParentId)
    {
        $array = [];

        $variables = [
            'companyId' => (int) Company::get('company_id'),
            'options' => [
                'filter' => [
                    'field' => 'parentId',
                    'comparison' => 'eq',
                    'value' => $categoryParentId,
                ],
            ],
        ];

        $categories = (Products::queryProductCategories($variables));

        foreach ($categories as $category) {
            if ($category['name'] === 'Envío') {
                continue;
            }

            if (empty($category['child'])) {
                $array[$category['name']] = [];
            } else {
                $array[$category['name']] = self::getAllCategoriesFromMoloni((string) $category['productCategoryId']);
            }
        }

        return $array;
    }

    /**
     * Creates all categories fetched form moloni
     *
     * @param $arrayCategories
     * @param $parentId
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    public static function createCategoriesFromMoloni($arrayCategories, $parentId)
    {
        $lang = (int) \Configuration::get('PS_LANG_DEFAULT');
        $somethingNew = false;

        foreach ($arrayCategories as $name => $child) {
            if (\Category::searchByNameAndParentCategoryId($lang, $name, $parentId) === false) {
                $category = new \Category();
                $category->name = [$lang => (string) $name];
                $category->id_parent = $parentId;
                $category->link_rewrite = [1 => \Tools::str2url((string) $name)];
                $category->save();

                $currentId = $category->id;

                $somethingNew = true;
            } else {
                $currentId = (\Category::searchByNameAndParentCategoryId($lang, $name, $parentId))['id_category'];
            }

            if (!empty($child)) {
                self::createCategoriesFromMoloni($child, (int) $currentId);
            }
        }

        return $somethingNew;
    }
}
