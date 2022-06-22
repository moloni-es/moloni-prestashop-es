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

namespace Moloni\Actions\Mixed;

use Symfony\Component\Translation\TranslatorInterface;

class DummyTranslations
{

    private $translator;

    /**
     * Construct
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function errors(): void
    {
        $this->translator->trans('Discarded order not found!', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching pdf token', [], 'Modules.Molonies.Errors');
        $this->translator->trans('ID is invalid', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Order does not exist!', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating account', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Data is not valid', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating webservice key', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating {0} hook', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error deleting hooks', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error paginating request', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Code missing', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Request error', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching tokens', [], 'Modules.Molonies.Errors');
        $this->translator->trans('The client credentials are invalid', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating category', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching categories', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating tax: ({0} - {1})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching taxes', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating customer ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching countries', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching customer next number', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching customer by VAT: ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching customer by e-mail: ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating delivery method: ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error getting load country', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error getting delivery country', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching payment methods', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating payment method: ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching product by reference: ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating shipping product', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Shipping has no taxes applied. Please add an exemption reason in plugin settings.', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching shipping by reference: ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating {0} attribute group', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching property groups', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Failed to update existing property group "{0}"', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Failed to find matching property name for "{0}".', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating stock movement ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Cannot update product in Moloni. Product types do not match', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating product ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error updating product ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating stock movement ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching company data', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Company does not have a default warehouse, please select one', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching default company warehouse', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching product by reference: ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating Prestashop category', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error getting product categories', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating combination ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error updating combination ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error when creating product attributes', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Could not fetch pdf link.', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Code cannot be empty!', [], 'Modules.Molonies.Errors');
        $this->translator->trans('You have no companies!!', [], 'Modules.Molonies.Errors');
        $this->translator->trans('ID is invalid', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Missing information in database', [], 'Modules.Molonies.Errors');
        $this->translator->trans('An unexpected error occurred', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Could not find product in Moloni ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error fetching product by id ({0})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Could not close {0}, totals do not match ({1})', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Created document not found', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Moloni document not found', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error getting prestashop order', [], 'Modules.Molonies.Errors');
        $this->translator->trans('Error creating document.', [], 'Modules.Molonies.Errors');
    }

    public function common(): void
    {
        $this->translator->trans('Invoice', [], 'Modules.Molonies.Common');
        $this->translator->trans('Invoice + Receipt', [], 'Modules.Molonies.Common');
        $this->translator->trans('Purchase Order', [], 'Modules.Molonies.Common');
        $this->translator->trans('Pro Forma Invoice', [], 'Modules.Molonies.Common');
        $this->translator->trans('Simplified invoice', [], 'Modules.Molonies.Common');
        $this->translator->trans('Budget', [], 'Modules.Molonies.Common');
        $this->translator->trans('Bills of lading', [], 'Modules.Molonies.Common');

        $this->translator->trans('Error', [], 'Modules.Molonies.Common');
        $this->translator->trans('Warning', [], 'Modules.Molonies.Common');
        $this->translator->trans('Information', [], 'Modules.Molonies.Common');

        $this->translator->trans('Stock is already updated in Moloni ({0})', [], 'Modules.Molonies.Common');
        $this->translator->trans('Stock updated in Moloni (old: {0} | new: {1}) ({2})', [], 'Modules.Molonies.Common');
        $this->translator->trans('Products export. Part {0}', [], 'Modules.Molonies.Common');
        $this->translator->trans('Products stock export. Part {0}', [], 'Modules.Molonies.Common');
        $this->translator->trans('Products import. Part {0}', [], 'Modules.Molonies.Common');
        $this->translator->trans('Products stock import. Part {0}', [], 'Modules.Molonies.Common');
        $this->translator->trans('{0} document created with success ({1})', [], 'Modules.Molonies.Common');
        $this->translator->trans('Product created in Moloni ({0})', [], 'Modules.Molonies.Common');
        $this->translator->trans('Product updated in Moloni ({0})', [], 'Modules.Molonies.Common');
        $this->translator->trans('Stock is already updated in Prestashop ({0})', [], 'Modules.Molonies.Common');
        $this->translator->trans('Stock updated in Prestashop (old: {0} | new: {1}) ({2})', [], 'Modules.Molonies.Common');
        $this->translator->trans('Combination created in Prestashop ({0})', [], 'Modules.Molonies.Common');
        $this->translator->trans('Combination updated in Prestashop ({0})', [], 'Modules.Molonies.Common');
        $this->translator->trans('Product created in Prestashop ({0})', [], 'Modules.Molonies.Common');
        $this->translator->trans('Product updated in Prestashop ({0})', [], 'Modules.Molonies.Common');
    }
}
