<?php

/**
 * 2025 - Moloni.com
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

namespace Moloni\Traits;

use Country;
use Moloni\Api\MoloniApiClient;
use Moloni\Enums\Countries;
use Moloni\Enums\Languages;
use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

trait CountryTrait
{
    /**
     * Returns Moloni country by prestashop country id
     *
     * @throws MoloniApiException
     */
    public function getMoloniCountryById(?int $prestashopCountryId = 0, ?int $prestashopStateId = 0): array
    {
        $countryIso = \Country::getIsoById($prestashopCountryId) ?? '';

        $default = ['countryId' => Countries::SPAIN, 'languageId' => Languages::EN, 'code' => strtoupper($countryIso)];

        /* Early return */
        if (empty($countryIso)) {
            return $default;
        }

        $variables = [
            'options' => [
                'search' => [
                    'field' => 'iso3166_1',
                    'value' => $countryIso,
                ],
                'order' => [
                    [
                        'field' => 'ordering',
                        'sort' => 'ASC',
                    ],
                ],
                'defaultLanguageId' => Languages::EN,
            ],
        ];

        $targetCountries = MoloniApiClient::countries()->queryCountries($variables);

        /* Early return */
        if (empty($targetCountries)) {
            return $default;
        }

        /* Return the only one found */
        if (count($targetCountries) === 1) {
            return [
                'countryId' => $targetCountries[0]['countryId'],
                'languageId' => $targetCountries[0]['language']['languageId'],
                'code' => strtoupper($countryIso),
            ];
        }

        $state = \State::getNameById($prestashopStateId) ?? '';
        $state = strtolower($state);

        /* Try to find the best match */
        foreach ($targetCountries as $targetCountry) {
            if ($state === strtolower($targetCountry['title'])) {
                return [
                    'countryId' => $targetCountry['countryId'],
                    'languageId' => $targetCountry['language']['languageId'],
                    'code' => strtoupper($countryIso),
                ];
            }
        }

        /* Fallback */
        return [
            'countryId' => $targetCountries[0]['countryId'],
            'languageId' => $targetCountries[0]['language']['languageId'],
            'code' => strtoupper($countryIso),
        ];
    }
}
