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

declare(strict_types=1);

namespace Moloni\Form\Registration;

use Moloni\Api\MoloniApiClient;
use Moloni\Enums\Languages;
use Moloni\Exceptions\MoloniApiException;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\Translation\TranslatorInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RegistrationFormDataProvider implements FormDataProviderInterface
{
    private $translator;

    private $countries = [];
    private $businessAreas = [];

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getData(): array
    {
        return [];
    }

    public function setData(array $data): void
    {
        return;
    }

    /**
     * Fetch required data for registration form
     *
     * @throws MoloniApiException
     */
    public function loadMoloniData(): RegistrationFormDataProvider
    {
        $props = [
            'options' => [
                'defaultLanguageId' => Languages::ES
            ]
        ];

        $countriesQuery = MoloniApiClient::countries()->queryCountries($props);
        $businessAreasQuery = MoloniApiClient::businessAreas()->queryBusinessAreas($props)['data']['businessAreas']['data'] ?? [];

        foreach ($countriesQuery as $country) {
            $this->countries[$country['title']] = $country['countryId'];
        }

        foreach ($businessAreasQuery as $area) {
            $this->businessAreas[$area['title']] = $area['businessAreaId'];
        }

        $this->businessAreas[$this->trans('Other', 'Modules.Molonies.Common')] = 'custom';

        return $this;
    }

    /**
     * Simple translator implementation
     *
     * @param string $string
     * @param string $domain
     *
     * @return string
     * @noinspection PhpSameParameterValueInspection
     */
    private function trans(string $string, string $domain): string
    {
        return $this->translator->trans($string, [], $domain);
    }

    /**
     * @return array
     */
    public function getCountries(): array
    {
        return $this->countries;
    }

    /**
     * @return array
     */
    public function getBusinessAreas(): array
    {
        return $this->businessAreas;
    }
}
