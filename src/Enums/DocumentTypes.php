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

namespace Moloni\Enums;

if (!defined('_PS_VERSION_')) {
    exit;
}

class DocumentTypes
{
    public const INVOICES = 'invoices';
    public const RECEIPTS = 'receipts';
    public const INVOICE_AND_RECEIPT = 'invoiceAndReceipts';
    public const PURCHASE_ORDERS = 'purchaseOrders';
    public const PRO_FORMA_INVOICES = 'proFormaInvoices';
    public const SIMPLIFIED_INVOICES = 'simplifiedInvoices';
    public const ESTIMATE = 'estimate';
    public const BILLS_OF_LADING = 'billsOfLadings';

    public const TYPES_WITH_PAYMENTS = [
        self::RECEIPTS,
        self::PRO_FORMA_INVOICES,
        self::SIMPLIFIED_INVOICES,
    ];

    public const TYPES_WITH_DELIVERY = [
        self::INVOICES,
        self::PURCHASE_ORDERS,
        self::PRO_FORMA_INVOICES,
        self::SIMPLIFIED_INVOICES,
        self::ESTIMATE,
        self::BILLS_OF_LADING,
    ];

    public const TYPES_REQUIRES_DELIVERY = [
        self::BILLS_OF_LADING,
    ];

    public const TYPES_WITH_PRODUCTS = [
        self::INVOICES,
        self::PURCHASE_ORDERS,
        self::PRO_FORMA_INVOICES,
        self::SIMPLIFIED_INVOICES,
        self::ESTIMATE,
        self::BILLS_OF_LADING,
    ];

    public const TYPES_NAMES = [
        self::INVOICES => 'Invoice',
        self::RECEIPTS => 'Receipt',
        self::PURCHASE_ORDERS => 'Purchase order',
        self::PRO_FORMA_INVOICES => 'Pro forma invoice',
        self::SIMPLIFIED_INVOICES => 'Simplified invoice',
        self::ESTIMATE => 'Estimate',
        self::BILLS_OF_LADING => 'Bills of lading',
    ];

    public static function getDocumentsTypes(): array
    {
        return [
            'Invoice' => self::INVOICES,
            'Invoice + Receipt' => self::INVOICE_AND_RECEIPT,
            'Purchase Order' => self::PURCHASE_ORDERS,
            'Pro Forma Invoice' => self::PRO_FORMA_INVOICES,
            'Simplified invoice' => self::SIMPLIFIED_INVOICES,
            'Budget' => self::ESTIMATE,
            'Bills of lading' => self::BILLS_OF_LADING,
        ];
    }

    public static function getDocumentTypeName(?string $documentType = ''): string
    {
        return self::TYPES_NAMES[$documentType] ?? '';
    }

    public static function hasPayments(?string $documentType = ''): bool
    {
        return in_array($documentType, self::TYPES_WITH_PAYMENTS, true);
    }

    public static function hasProducts(?string $documentType = ''): bool
    {
        return in_array($documentType, self::TYPES_WITH_PRODUCTS, true);
    }

    public static function hasDelivery(?string $documentType = ''): bool
    {
        return in_array($documentType, self::TYPES_WITH_DELIVERY, true);
    }

    public static function requiresDelivery(?string $documentType = ''): bool
    {
        return in_array($documentType, self::TYPES_REQUIRES_DELIVERY, true);
    }
}
