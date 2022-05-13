<?php

namespace Moloni\Enums;

class DocumentTypes
{
    public const INVOICES = 'invoices';
    public const RECEIPTS = 'receipts';
    public const PURCHASE_ORDERS = 'purchaseOrders';
    public const PRO_FORMA_INVOICES = 'proFormaInvoices';
    public const SIMPLIFIED_INVOICES = 'simplifiedInvoices';

    public static function getDocumentsTypes(): array
    {
        return [
            'Invoice' => 'invoices',
            'Invoice + Receipt' => 'receipts',
            'Purchase Order' => 'purchaseOrders',
            'Pro Forma Invoice' => 'proFormaInvoices',
            'Simplified invoice' => 'simplifiedInvoices',
        ];
    }
}
