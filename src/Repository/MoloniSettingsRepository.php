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

class MoloniSettingsRepository extends EntityRepository
{
    private $arraySettings = ['productSyncFields' , 'orderStatusToShow'];
    public function getSettings(): array
    {
        $settings = [];

        /**
         * @var MoloniSettings[] $settingsQuery
         */
        $settingsQuery = $this->findAll();

        foreach ($settingsQuery as $setting) {
            $value = $setting->getValue();
            $label = $setting->getLabel();

            if (!empty($value) && in_array($label, $this->arraySettings)) {
                $value = unserialize($value, ['allowed_classes' => false]);
            }

            $settings[$label] = $value;
        }

        return $settings;
    }

    /**
     * Save settings
     *
     * @param array $submitData Submited data
     * @param int $shopId Shop id
     *
     * @return void
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveSettings(array $submitData, int $shopId): void
    {
        $entityManager = $this->getEntityManager();

        foreach ($submitData as $label => $value) {
            if (is_array($value)) {
                $value = serialize($value);
            }

            if (is_object($value) && $label === 'orderDateCreated') {
                $value = $value->format('Y-m-d');
            }

            $setting = $this->findOneBy(['label' => $label]);

            if ($setting === null) {
                $setting = new MoloniSettings();
            }

            $setting->setLabel($label);
            $setting->setValue($value);
            $setting->setShopId($shopId);

            $entityManager->persist($setting);
            $entityManager->flush($setting);
        }
    }
}
