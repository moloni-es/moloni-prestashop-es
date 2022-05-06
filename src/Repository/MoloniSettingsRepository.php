<?php

namespace Moloni\Repository;

use Doctrine\ORM\EntityRepository;
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
            $settings[$setting->getLabel()] = $setting->getValue();
        }

        return $settings;
    }
}
