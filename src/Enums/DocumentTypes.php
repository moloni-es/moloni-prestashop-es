<?php

namespace Moloni\Enums;

class DocumentTypes
{
    public const INVOICES = 'invoices';
    public const RECEIPTS = 'receipts';
    public const PURCHASE_ORDERS = 'purchaseOrders';
    public const PRO_FORMA_INVOICES = 'proFormaInvoices';
    public const SIMPLIFIED_INVOICES = 'simplifiedInvoices';
    public const ESTIMATE = 'estimate';

    public static function getDocumentsTypes(): array
    {
        return [
            'Invoice' => self::INVOICES,
            'Invoice + Receipt' => self::RECEIPTS,
            'Purchase Order' => self::PURCHASE_ORDERS,
            'Pro Forma Invoice' => self::PRO_FORMA_INVOICES,
            'Simplified invoice' => self::SIMPLIFIED_INVOICES,
            'Budget' => self::ESTIMATE,
        ];
    }
}
