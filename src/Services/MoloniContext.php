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

namespace Moloni\Services;

use Doctrine\ORM\EntityManager;
use Moloni\Api\MoloniApi;
use Moloni\Entity\MoloniApp;
use Moloni\Entity\MoloniSettings;
use Moloni\Repository\MoloniAppRepository;
use Moloni\Repository\MoloniSettingsRepository;
use Moloni\Tools\Logs;
use Moloni\Tools\ProductAssociations;
use Moloni\Tools\Settings;
use Moloni\Tools\SyncLogs;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MoloniContext
{
    /**
     * EntityManager
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Plugin data
     *
     * @var MoloniApp|null
     */
    private $app;

    /**
     * Plugin Settings
     *
     * @var array
     */
    private $settings;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        $this->init();
    }

    //          PRIVATES          //

    private function init(): void
    {
        $this
            ->loadData()
            ->loadRequirements();
    }

    private function loadData(): MoloniContext
    {
        /**
         * @var MoloniAppRepository $appRepository
         */
        $appRepository = $this
            ->entityManager
            ->getRepository(MoloniApp::class);

        /**
         * @var MoloniSettingsRepository $settingsRepo
         */
        $settingsRepo = $this
            ->entityManager
            ->getRepository(MoloniSettings::class);

        $this->app = $appRepository->getApp();
        $this->settings = $settingsRepo->getSettings($this->getCompanyId());

        return $this;
    }

    private function loadRequirements(): void
    {
        // API
        new MoloniApi($this->entityManager, $this->app);

        // Helpers
        new Settings($this->settings);
        new Logs($this->entityManager);
        new SyncLogs($this->entityManager);
        new ProductAssociations($this->entityManager);
    }

    //          PUBLICS          //

    public function getApp(): ?MoloniApp
    {
        return $this->app;
    }

    public function getCompanyId(): int
    {
        if ($this->app === null) {
            return 0;
        }

        return $this->app->getCompanyId();
    }
}
