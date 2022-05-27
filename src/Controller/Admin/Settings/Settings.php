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

namespace Moloni\Controller\Admin\Settings;

use Exception;
use Moloni\Actions\Settings\SettingsSave;
use Moloni\Actions\Settings\SettingsForm;
use Moloni\Exceptions\MoloniException;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Entity\MoloniSettings;
use Moloni\Repository\MoloniSettingsRepository;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Routing\Router;

class Settings extends MoloniController
{
    /**
     * Settings form page
     *
     * @return Response
     *
     * @throws Exception
     */
    public function home(): Response
    {
        /** @var FormFactory $formFactory */
        $formFactory = $this->get('form.factory');

        /** @var Router $router */
        $router = $this->get('router');

        $languageId = $this->getContextLangId();

        $form = (new SettingsForm($languageId, $formFactory, $router))->handle();

        return $this->render(
            '@Modules/molonies/views/templates/admin/settings/Settings.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Save plugin settings
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function save(Request $request): RedirectResponse
    {
        /** @var MoloniSettingsRepository $settingsRepository */
        $settingsRepository = $this
            ->getDoctrine()
            ->getRepository(MoloniSettings::class);

        /** @var FormFactory $formFactory */
        $formFactory = $this->get('form.factory');

        $languageId = $this->getContextLangId();

        try {
            (new SettingsSave($languageId, $formFactory, $settingsRepository))->handle($request);

            $this->addSuccessMessage($this->trans('Settings saved.', 'Modules.Molonies.Common'));
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg);
        } catch (OptimisticLockException|ORMException $e) {
            $msg = $this->trans('Error saving settings', 'Modules.Molonies.Errors');
            $this->addErrorMessage($msg);
        }

        return $this->redirectToSettings();
    }
}
