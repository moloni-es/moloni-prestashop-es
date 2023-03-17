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
 * @noinspection PhpIllegalPsrClassPathInspection
 */

use Moloni\Webservice\Product\ProductCreate;
use Moloni\Webservice\Product\ProductStockChange;
use Moloni\Webservice\Product\ProductUpdate;

if (!defined('_PS_VERSION_')) {
    exit;
}

class WebserviceSpecificManagementMoloniResource implements WebserviceSpecificManagementInterface
{
    /** @var WebserviceOutputBuilder */
    protected $objOutput;

    /** @var string */
    protected $output;

    /** @var WebserviceRequest */
    protected $wsObject;

    /**
     * Interface method
     *
     * @param WebserviceOutputBuilderCore|WebserviceOutputBuilder $obj
     * @return $this
     */
    public function setObjectOutput($obj): WebserviceSpecificManagementMoloniResource
    {
        $this->objOutput = $obj;

        return $this;
    }

    /**
     * Interface method
     *
     * @return WebserviceOutputBuilderCore|WebserviceOutputBuilder
     */
    public function getObjectOutput()
    {
        return $this->objOutput;
    }

    /**
     * Interface method
     *
     * @param WebserviceRequestCore|WebserviceRequest $obj
     *
     * @return $this
     */
    public function setWsObject($obj): WebserviceSpecificManagementMoloniResource
    {
        $this->wsObject = $obj;

        return $this;
    }

    /**
     * Interface method
     */
    public function getWsObject()
    {
        return $this->wsObject;
    }

    /**
     * Manages the incoming requests
     * Switches between operations
     */
    public function manage()
    {
        $request = file_get_contents('php://input');
        $request = json_decode($request, true);

        if (!isset($request['model'], $request['operation'], $request['productId']) || $request['model'] !== 'Product') {
            $this->output = 'Bad request';

            return $this->wsObject->getOutputEnabled();
        }

        switch ($request['operation']) {
            case 'create':
                (new ProductCreate((int)$request['productId']))->handle();
                break;
            case 'update':
                (new ProductUpdate((int)$request['productId']))->handle();
                break;
            case 'stockChanged':
                (new ProductStockChange((int)$request['productId']))->handle();
                break;
        }

        $this->output = 'Acknowledge';

        return $this->wsObject->getOutputEnabled();
    }

    /**
     * Interface method
     *
     * @return array|string|null
     */
    public function getContent()
    {
        return $this->objOutput->getObjectRender()->overrideContent($this->output);
    }
}
