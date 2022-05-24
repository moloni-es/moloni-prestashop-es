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

namespace Moloni\Actions\Settings;

use DateTime;
use Exception;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Form\SettingsFormType;
use Moloni\Helpers\Settings;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class SettingsForm extends AbstractSettingsAction
{
    protected $router;

    public function __construct(int $languageId, FormFactory $formBuilder, Router $router)
    {
        parent::__construct($languageId, $formBuilder);

        $this->router = $router;
    }

    /**
     * @throws Exception
     */
    public function handle(): FormInterface
    {
        try {
            $apiData = $this->getRequiredFormData();
        } catch (MoloniApiException $e) {
            $apiData = [];
        }

        $form = $this->formBuilder->create(SettingsFormType::class, null, [
            'url' => $this->router->generate('moloni_es_settings_save', [], UrlGeneratorInterface::ABSOLUTE_PATH),
            'api_data' => $apiData
        ]);

        $setting = Settings::getAll();

        if (isset($setting['dateCreated'])) {
            $setting['dateCreated'] = new DateTime($setting['dateCreated']);
        }

        $form->setData($setting);

        return $form;
    }
}
