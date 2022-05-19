<?php

namespace Moloni\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Moloni\Entity\MoloniSettings;

class MoloniSettingsRepository extends EntityRepository
{
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

            if ($label === 'productSyncFields' && !empty($value)) {
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

            if (is_object($value) && $label === 'dateCreated') {
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
