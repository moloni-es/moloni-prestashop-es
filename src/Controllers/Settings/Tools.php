<?php

namespace Moloni\ES\Controllers\Settings;

use Db;
use Moloni\ES\Controllers\Api\Products;
use Moloni\ES\Controllers\General;
use Moloni\ES\Controllers\Models\Company;
use Moloni\ES\Controllers\Models\Log;
use Order;
use Product;
use StockAvailable;

class Tools extends General
{
    /**
     * Checks for valid token and login, and displays template
     *
     * @return null returns the template
     */
    public function display()
    {
        if (!$this->checkTokenRedirect()) {
            return $this->redirectLogin();
        }

        $aux = 'tools';

        return $this->render('@Modules/moloniprestashopes/src/View/Templates/Admin/Settings/Tools.twig', [
            'tabActive' => $aux,
        ]);
    }

    /**
     * Import categories from Moloni
     *
     * @return null returns the template
     */
    public function importCategories()
    {
        if (!$this->checkTokenRedirect()) {
            return $this->redirectLogin();
        }

        $variables = [
            'companyId' => (int) Company::get('company_id'),
        ];

        $categoriasMoloni = (Products::queryProductCategories($variables));

        $dbPresta = Db::getInstance();
        $sql = 'SELECT MAX(id_category) FROM ' . _DB_PREFIX_ . 'category';
        $existRes = (int) $dbPresta->executeS($sql)[0]['MAX(id_category)'];

        $i = 0;
        foreach ($categoriasMoloni as $key => $category) {
            if (empty(\Category::searchByName(1, $category['name'])) && $category['name'] != 'Envío') {
                $categoryAdd = new \Category();
                $categoryAdd->id = ++$existRes;
                $categoryAdd->name = [1 => $category['name']];
                $categoryAdd->id_parent = 2;
                $categoryAdd->link_rewrite = [1 => \Tools::str2url($category['name'])];
                $categoryAdd->add();
                ++$i;
            }
        }
        if ($i == 0) {
            $this->addFlash('warning', $this->trans(
                'All categories already imported.',
                'Modules.Moloniprestashopes.Success'
            ));
            Log::writeLog('All categories already imported!!');

            return $this->redirectSettingsTools();
        } else {
            $this->addFlash('success', $this->trans(
                'All categories imported!!',
                'Modules.Moloniprestashopes.Settings'
            ));
            Log::writeLog('All categories imported!!');

            return $this->redirectSettingsTools();
        }
    }

    /**
     * Import products from Moloni
     *
     * @return null returns the template
     */
    public function importProducts()
    {
        if (!$this->checkTokenRedirect()) {
            return $this->redirectLogin();
        }

        $variables = [
            'companyId' => (int) Company::get('company_id'),
        ];
        $produtosMoloni = Products::queryProducts($variables);

        $i = 0;
        foreach ($produtosMoloni as $key => $products) {
            if ((Product::getIdByReference($products['reference'])) == false && $products['reference'] != 'envio') {
                $productAdd = new Product();
                $productAdd->name = $products['name'];
                $productAdd->reference = $products['reference'];
                $productAdd->price = $products['price'];
                $productAdd->quantity = $products['stock'];
                $categoryAdd = (int) \Category::searchByName(1, $products['productCategory']['name'])[0]['id_category'];
                $productAdd->id_category_default = $categoryAdd;
                $productAdd->add();
                $productAdd->addToCategories([2, $productAdd->id_category_default]);
                StockAvailable::setQuantity($productAdd->id, '', $productAdd->quantity);
                ++$i;
            }
        }
        if ($i == 0) {
            $this->addFlash('warning', $this->trans(
                'All products already imported.',
                'Modules.Moloniprestashopes.Success'
            ));
            Log::writeLog('All products already imported!!');

            return $this->redirectSettingsTools();
        } else {
            $this->addFlash('success', $this->trans(
                'All products imported.',
                'Modules.Moloniprestashopes.Success'
            ));
            Log::writeLog('All products imported!!');

            return $this->redirectSettingsTools();
        }
    }

    /**
     * Forces stock synchronization
     *
     * @return null returns the template
     */
    public function forceStocksSync()
    {
        if (!$this->checkTokenRedirect()) {
            return $this->redirectLogin();
        }

        $lastWeek = date('Y-m-d', strtotime('-1 week'));

        $variables = ['companyId' => (int) Company::get('company_id'),
            'options' => [
                'filter' => [
                    'field' => 'updatedAt',
                    'comparison' => 'gt',
                    'value' => $lastWeek,
                ],
            ],
        ];
        $usedProducts = Products::queryProducts($variables);
        $countUsedProducts = count($usedProducts);
        if (!empty($usedProducts)) {
            for ($i = 0; $i < $countUsedProducts; ++$i) {
                $referencesSearch[$i]['name'] = $usedProducts[$i]['name'];
                $referencesSearch[$i]['reference'] = $usedProducts[$i]['reference'];
                $referencesSearch[$i]['stock'] = $usedProducts[$i]['stock'];
                $referencesSearch[$i]['hasStock'] = $usedProducts[$i]['hasStock'];
            }
            $countReferenceSearch = count($referencesSearch);
            for ($i = 0; $i < $countReferenceSearch; ++$i) {
                $idPSproducts[$i]['ID'] = Product::getIdByReference($referencesSearch[$i]['reference']);
                $idPSproducts[$i]['name'] = $usedProducts[$i]['name'];
                $idPSproducts[$i]['stock'] = $referencesSearch[$i]['stock'];
                $idPSproducts[$i]['hasStock'] = $referencesSearch[$i]['hasStock'];
            }
            $tamanho = count($idPSproducts);
            for ($i = 0; $i < $tamanho; ++$i) {
                if ($idPSproducts[$i]['ID'] == false || $idPSproducts[$i]['hasStock'] == false) {
                    unset($idPSproducts[$i]);
                }
            }
            $idPSproductsClean = array_values($idPSproducts);
            $countIdPSproductsClean = count($idPSproductsClean);
            $contagem = 0;
            for ($i = 0; $i < $countIdPSproductsClean; ++$i) {
                $stockPS = StockAvailable::getQuantityAvailableByProduct($idPSproductsClean[$i]['ID']);
                if ($idPSproductsClean[$i]['stock'] == $stockPS) {
                    ++$contagem;
                }
            }
            if ($contagem == $countIdPSproductsClean) {
                $this->addFlash('warning', $this->trans(
                    'All stocks are already synchronized!!',
                    'Modules.Moloniprestashopes.Errors'
                ));
                Log::writeLog('All stocks are already synchronized!!');

                return $this->redirectSettingsTools();
            } else {
                for ($i = 0; $i < $countIdPSproductsClean; ++$i) {
                    StockAvailable::setQuantity($idPSproductsClean[$i]['ID'], '', $idPSproductsClean[$i]['stock']);
                }
            }
            $this->addFlash('success', $this->trans(
                'All stocks synchronized.',
                'Modules.Moloniprestashopes.Success'
            ));
            Log::writeLog('All stocks synchronized!!');

            return $this->redirectSettingsTools();
        } else {
            $this->addFlash('warning', $this->trans(
                'No stock to synchronize!!',
                'Modules.Moloniprestashopes.Errors'
            ));
            Log::writeLog('No stock to synchronize!!');

            return $this->redirectSettingsTools();
        }
    }

    /**
     * Mark all pending orders as already generated
     *
     * @return null returns the template
     */
    public function cleanPendentOrder()
    {
        $encomendas = Order::getOrdersWithInformations();
        $countEncomendas = count($encomendas);
        for ($i = 0; $i < $countEncomendas; ++$i) {
            if ($encomendas[$i]['invoice_number'] != '0') {
                $bla[$i] = $encomendas[$i];
            }
        }
        $encomendasWInvoice = array_values($bla);
        $countEncomendasWInvoice = count($encomendasWInvoice);
        $dbPresta = Db::getInstance();
        for ($i = 0; $i < $countEncomendasWInvoice; ++$i) {
            $sql = 'SELECT order_ref FROM ' . _DB_PREFIX_ . "moloni_documents WHERE 
            order_ref='" . $encomendasWInvoice[$i]['reference'] . "'";
            $existRes = $dbPresta->executeS($sql);
            if (!empty($existRes)) {
                $encomendasWInvoice[$i]['gerado'] = true;
            } else {
                $encomendasWInvoice[$i]['gerado'] = false;
            }
        }
        for ($i = 0; $i < $countEncomendasWInvoice; ++$i) {
            if ($encomendasWInvoice[$i]['gerado'] == true) {
                unset($encomendasWInvoice[$i]);
            }
        }
        if (!empty($encomendasWInvoice)) {
            $encomendasNgeradas = array_values($encomendasWInvoice);
            $sql = 'SELECT MIN(document_id) FROM ' . _DB_PREFIX_ . 'moloni_documents';
            $idSql = (int) ($dbPresta->executeS($sql))[0]['MIN(document_id)'];
            $countEncomendasNGeradas = count($encomendasNgeradas);
            for ($i = 0; $i < $countEncomendasNGeradas; ++$i) {
                if ($idSql >= 0) {
                    $idSql = -1;
                } else {
                    --$idSql;
                }
                $dbPresta->insert('moloni_documents', [
                    'document_id' => $idSql,
                    'reference' => pSQL($encomendasNgeradas[$i]['reference']),
                    'company_id' => Company::get('company_id'),
                    'store_id' => 1,
                    'id_order' => pSQL($encomendasNgeradas[$i]['id_order']),
                    'order_ref' => pSQL($encomendasNgeradas[$i]['reference']),
                    'order_total' => pSQL($encomendasNgeradas[$i]['total_paid']),
                    'metadata' => json_encode($encomendasNgeradas[$i]),
                ]);
            }
            $this->addFlash('success', $this->trans(
                'Pendent orders cleaned.',
                'Modules.Moloniprestashopes.Success'
            ));

            return $this->redirectSettingsTools();
        } else {
            $this->addFlash('warning', $this->trans(
                'No pendente orders to clear!!',
                'Modules.Moloniprestashopes.Errors'
            ));

            return $this->redirectSettingsTools();
        }
    }

    /**
     * Shows all logs of your company
     *
     * @return null returns the template
     */
    public function consultLogs()
    {
        $aux = 'tools';
        $pasta = _PS_MODULE_DIR_ . 'moloniprestashopes/logs';
        $ficheiro = $pasta . '/' . ((Company::get('company_id')) ? Company::get('company_id') . '_' : '0_')
            . date('Ymd')
            . '.txt';
        if (!file_exists($ficheiro)) {
            $this->addFlash('warning', $this->trans(
                'No log files!!',
                'Modules.Moloniprestashopes.Errors'
            ));

            return $this->redirectSettingsTools();
        } else {
            $fp = fopen($ficheiro, 'r');
            $logs = fread($fp, filesize($ficheiro));
            fclose($fp);

            return $this->render('@Modules/moloniprestashopes/src/View/Templates/Admin/Settings/ConsultLogs.twig', [
                'logs' => $logs,
                'tabActive' => $aux,
            ]);
        }
    }

    /**
     * Delete all stored Logs
     *
     * @return null returns the template
     */
    public function deleteLogs()
    {
        Log::deleteLogs();
        $this->addFlash('success', $this->trans(
            'All logs were deleted.',
            'Modules.Moloniprestashopes.Success'
        ));

        return $this->redirectSettingsTools();
    }

    /**
     * Exports all translations to csv file
     *
     * @return null returns the template
     */
    public function exportTranslations()
    {
        $dbPresta = Db::getInstance();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'translation';
        $sql .= ' ORDER BY `id_lang` ASC';
        $existRes = $dbPresta->executeS($sql);

        $i = 0;
        $currentLang = (int) $existRes[0]['id_lang'];
        foreach ($existRes as $translations) {
            if ($currentLang != (int) $translations['id_lang']) {
                ++$i;
                $currentLang = (int) $translations['id_lang'];
                $tradLang[$i][$translations['id_translation']] = $translations;
            } else {
                $tradLang[$i][$translations['id_translation']] = $translations;
            }
        }

        $instructions = '';
        $j = 0;
        $lang = '@idLang';
        foreach ($tradLang as $langDiff) {
            $i = 0;
            $tam = count($langDiff);
            if ($j != 0) {
                $instructions .= PHP_EOL;
            }
            $instructions .= 'INSERT INTO PREFIX_translation (`id_lang`,`key`,`translation`,`domain`) VALUES ';
            foreach ($langDiff as $diffLang) {
                if ($i == $tam - 1) {
                    $char = ';';
                } else {
                    $char = ',';
                }
                $instructions .= '(' . $lang . ',\'' . str_replace("'", "''", $diffLang['key']) . '\',\''
                    . str_replace("'", "''", $diffLang['translation'])
                    . '\',\'' . $diffLang['domain'] . '\')' . $char . PHP_EOL;
                ++$i;
            }
            ++$j;
        }

        $fp = fopen(_PS_MODULE_DIR_ . 'moloniprestashopes/sql/install/translationsExport.sql', 'w');
        fwrite($fp, $instructions);
        fclose($fp);

        $this->addFlash('success', $this->trans(
            'Translations exported sucessfully!',
            'Modules.Moloniprestashopes.Success'
        ));

        return $this->redirectSettingsTools();
    }

    /**
     * Logout of your company
     *
     * @return null returns the template
     */
    public function sair()
    {
        return $this->logOut();
    }
}
