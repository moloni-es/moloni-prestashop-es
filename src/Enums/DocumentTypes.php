<?php

namespace Moloni\Enums;

class DocumentTypes
{
    public const INVOICES = 'invoices';
    public const RECEIPTS = 'receipts';
    public const INVOICE_AND_RECEIPT = 'invoiceAndReceipts';
    public const PURCHASE_ORDERS = 'purchaseOrders';
    public const PRO_FORMA_INVOICES = 'proFormaInvoices';
    public const SIMPLIFIED_INVOICES = 'simplifiedInvoices';
    public const ESTIMATE = 'estimate';
    public const BILLS_OF_LADING = 'billsOfLading';

    public const TYPES_WITH_PAYMENTS = [
        self::RECEIPTS,
        self::PRO_FORMA_INVOICES,
        self::SIMPLIFIED_INVOICES,
    ];

    public const TYPES_WITH_DELIVERY = [
        self::INVOICES,
        self::RECEIPTS,
        self::PURCHASE_ORDERS,
        self::PRO_FORMA_INVOICES,
        self::SIMPLIFIED_INVOICES,
        self::ESTIMATE,
        self::BILLS_OF_LADING,
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
        ];
    }

    public static function hasPayments(?string $documentType = ''): bool
    {
        return in_array($documentType, self::TYPES_WITH_PAYMENTS, true);
    }

    public static function hasDelivery(?string $documentType = ''): bool
    {
        return in_array($documentType, self::TYPES_WITH_DELIVERY, true);
    }
}
