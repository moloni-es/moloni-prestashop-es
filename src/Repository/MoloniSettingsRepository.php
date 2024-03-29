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

namespace Moloni\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Moloni\Entity\MoloniSettings;
use Moloni\Enums\Date;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MoloniSettingsRepository extends EntityRepository
{
    private $arraySettings = ['productSyncFields' , 'orderStatusToShow'];

    public function getSettings(?int $companyId = 0): array
    {
        $settings = [];

        /**
         * @var MoloniSettings[] $settingsQuery
         */
        $settingsQuery = $this->findBy(['companyId' => $companyId]);

        foreach ($settingsQuery as $setting) {
            $value = $setting->getValue();
            $label = $setting->getLabel();

            $settings[$label] = $this->getOptionValue($label, $value);
        }

        return $settings;
    }

    /**
     * Save settings
     *
     * @param array $submitData Submited data
     * @param int $shopId Shop id
     * @param int $companyId Company id
     *
     * @return void
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveSettings(array $submitData, int $shopId, int $companyId): void
    {
        $entityManager = $this->getEntityManager();

        foreach ($submitData as $label => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            if (is_object($value) && $label === 'orderDateCreated') {
                $value = $value->format(Date::DATE_FORMAT);
            }

            if (is_null($value)) {
                $value = '';
            }

            $setting = $this->findOneBy(['label' => $label, 'companyId' => $companyId]);

            if ($setting === null) {
                $setting = new MoloniSettings();
            }

            $setting->setShopId($shopId);
            $setting->setCompanyId($companyId);
            $setting->setLabel($label);
            $setting->setValue($value);

            $entityManager->persist($setting);
            $entityManager->flush();
        }
    }

    /**
     * Get safe value to load
     *
     * @param string|null $label
     * @param string|null $value
     *
     * @return array|string|null
     */
    private function getOptionValue(?string $label = '', ?string $value = '')
    {
        if (in_array($label, $this->arraySettings)) {
            if (empty($value)) {
                return [];
            }

            $tempValue = json_decode($value, true);

            if (is_array($tempValue)) {
                return $tempValue;
            }

            /** Depretaced stuff */
            $tempValue = unserialize($value, ['allowed_classes' => false]);

            if (is_array($tempValue)) {
                return $tempValue;
            }

            /** Better safe than sorry */
            return [];
        }

        return $value;
    }
}
