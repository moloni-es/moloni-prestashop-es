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

namespace Moloni\Actions\Registration;

use Moloni\Api\MoloniApiClient;
use Moloni\Enums\Languages;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CreateNewMoloniAccount
{
    private $formData;

    /**
     * Construct
     *
     * @throws MoloniException
     */
    public function __construct($formData)
    {
        $this->formData = $formData;

        $this->handle();
    }

    /**
     * Handler
     *
     * @throws MoloniException
     */
    private function handle(): void
    {
        $props = [
            'administratorName' => $this->formData['username'],
            'administratorEmail' => $this->formData['email'],
            'administratorPwd' => $this->formData['password'],
            'administratorCell' => $this->formData['phone'],
            'vat' => $this->formData['vat'],
            'name' => $this->formData['companyName'],
            'slug' => $this->formData['slug'],
            'countryId' => (int)$this->formData['country'],
            'languageId' => Languages::ES,
            'newsletterAccepted' => (bool)$this->formData['newsletter'],
            'conditionsAccepted' => (bool)$this->formData['serviceTerms'],
            'businessAreas' => [],
        ];

        if ($this->formData['businessType'] === 'custom') {
            $props['businessAreas'][] = [
                'title' => $this->formData['businessTypeName']
            ];
        } else {
            $props['businessAreas'][] = [
                'businessAreaId' => $this->formData['businessType']
            ];
        }

        try {
            $mutation = MoloniApiClient::registration()->mutationSignUpCompany(['data' => $props]);
        } catch (MoloniApiException $e) {
            throw new MoloniException('Error creating account', [], $e->getData());
        }

        if (!$mutation) {
            throw new MoloniException('Data is not valid', [], $props);
        }
    }
}
