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
    public function getMoloniCountryById(?int $prestashopCountryId = 0): array
    {
        $countryId = Countries::SPAIN;
        $languageId = Languages::ES;
        $countryIso = Country::getIsoById($prestashopCountryId);

        if (!empty($countryIso)) {
            $variables = [
                'options' => [
                    'search' => [
                        'field' => 'iso3166_1',
                        'value' => $countryIso,
                    ],
                ],
            ];

            $query = MoloniApiClient::countries()
                ->queryCountries($variables);

            foreach ($query as $country) {
                // todo fix multiple countries with same iso
                $countryId = (int) $country['countryId'];
                $languageId = (int) $country['language']['languageId'];

                break;
            }
        }

        return ['countryId' => $countryId, 'languageId' => $languageId, 'code' => strtoupper($countryIso)];
    }
}
