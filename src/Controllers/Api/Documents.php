<?php

namespace Moloni\ES\Controllers\Api;

class Documents
{
    /**
     * Get All Documents Set from Moloni ES
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryDocumentSets($variables)
    {
        $query = 'query documentSets($companyId: Int!,$options: DocumentSetOptions){
            documentSets(companyId: $companyId, options: $options) {
                errors{
                    field
                    msg
                }
                options
                {
                    pagination
                    {
                        page
                        qty
                        count
                    }
                }
                data{
                    documentSetId
                    name
                    isDefault
                }
            }
        }';

        return Curl::complex($query, $variables, 'documentSets');
    }

    /**
     * Get All Currencies from Moloni ES
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryCurrencies($variables)
    {
        $query = 'query currencies($options: CurrencyOptions){
        currencies(options: $options) {
            errors{
                field
                msg
            }
            data{
                currencyId
                symbol
                symbolPosition
                numberDecimalPlaces
                iso4217
                largeCurrency
                description
            }
            options
            {
                pagination
                {
                    page
                    qty
                    count
                }
            }
        }
    }';

        return Curl::complex($query, $variables, 'currencies');
    }

    /**
     * Get All DeliveryMethods from Moloni ES
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryDeliveryMethods($variables)
    {
        $query = 'query deliveryMethods($companyId: Int!,$options: DeliveryMethodOptions){
        deliveryMethods(companyId: $companyId,options: $options) {
            errors{
                field
                msg
            }
            data{
                deliveryMethodId
                name
            }
            options
            {
                pagination
                {
                    page
                    qty
                    count
                }
            }
        }
    }';

        return Curl::complex($query, $variables, 'deliveryMethods');
    }

    /**
     * Get All Timezones from Moloni ES
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryTimezones($variables)
    {
        $query = 'query timezones($options: TimezoneOptions){
        timezones(options: $options) {
            errors{
                field
                msg
            }
            options
            {
                pagination
                {
                    page
                    qty
                    count
                }
            }
            data{
                timezoneId
                name
                visible
                ordering
                tzName
                offset
                country{
                       countryId
                       iso3166_1
                       title
                       language{
                                languageId
                                name
                       } 
                }
            }
        }
    }';

        return Curl::complex($query, $variables, 'timezones');
    }

    /**
     * Get All Documents Set from Moloni ES
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryCountries($variables)
    {
        $query = 'query countries($options: CountryOptions){
            countries(options: $options) {
                errors{
                    field
                    msg
                }
                options
                {
                    pagination
                    {
                        page
                        qty
                        count
                    }
                }
                data{
                    countryId
                    iso3166_1
                    title
                    language{
                            languageId
                            name
                    }
                }
            }
        }';

        return Curl::complex($query, $variables, 'countries');
    }

    /**
     * Gets invoice information
     *
     * @param $variables array variables of the request
     *
     * @return array Api data
     */
    public static function queryInvoice($variables)
    {
        $query = 'query invoice($companyId: Int!,$documentId: Int!,$options: InvoiceOptionsSingle)
                {
                    invoice(companyId: $companyId,documentId: $documentId,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Gets all invoices
     *
     * @param $variables array variables of the request
     *
     * @return array Api data
     */
    public static function queryInvoices($variables)
    {
        $query = 'query invoices($companyId: Int!,$options: InvoiceOptions)
                {
                    invoices(companyId: $companyId,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        options
                        {
                            pagination
                            {
                                page
                                qty
                                count
                            }
                        }                        
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::complex($query, $variables, 'invoices');
    }

    /**
     * Creates an invoice
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function mutationInvoiceCreate($variables)
    {
        $query = 'mutation invoiceCreate($companyId: Int!,$data: InvoiceInsert!,$options: InvoiceMutateOptions){
                invoiceCreate(companyId: $companyId,data: $data,options: $options) {
                    errors{
                        field
                        msg
                    }
                    data{
                        documentId
                        number
                        totalValue
                        documentTotal
                        documentSetName
                        ourReference
                    }
                }
            }';

        return Curl::simple($query, $variables);
    }

    /**
     * Update an invoice
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function mutationInvoiceUpdate($variables)
    {
        $query = 'mutation invoiceUpdate($companyId: Int!,$data: InvoiceUpdate!)
        {
            invoiceUpdate(companyId: $companyId,data: $data) 
            {
                errors
                {
                    field
                    msg
                }
                data
                {
                    documentId
                    status                              
                }
            }
        }';

        return Curl::simple($query, $variables);
    }

    /**
     * Gets receipt information
     *
     * @param $variables array variables of the request
     *
     * @return array Api data
     */
    public static function queryReceipt($variables)
    {
        $query = 'query receipt($companyId: Int!,$documentId: Int!,$options: ReceiptOptionsSingle)
                {
                    receipt(companyId: $companyId,documentId: $documentId,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Gets all receipts
     *
     * @param $variables array variables of the request
     *
     * @return array Api data
     */
    public static function queryReceipts($variables)
    {
        $query = 'query receipts($companyId: Int!,$options: ReceiptOptions)
                {
                    receipts(companyId: $companyId,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        options
                        {
                            pagination
                            {
                                page
                                qty
                                count
                            }
                        }                        
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::complex($query, $variables, 'receipts');
    }

    /**
     * Creates a receipt
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function mutationReceiptCreate($variables)
    {
        $query = 'mutation receiptCreate($companyId: Int!,$data: ReceiptInsert!,$options: ReceiptMutateOptions)
                {
                    receiptCreate(companyId: $companyId,data: $data,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Update a receipt
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function mutationReceiptUpdate($variables)
    {
        $query = 'mutation receiptUpdate($companyId: Int!,$data: ReceiptUpdate!)
        {
            receiptUpdate(companyId: $companyId,data: $data)
            {
                data
                {
                    documentId
                    status
                }
                errors
                {
                    field
                    msg
                }
            }
        }';

        return Curl::simple($query, $variables);
    }

    /**
     * Gets credit note information
     *
     * @param $variables array variables of the request
     *
     * @return array Api data
     */
    public static function queryCreditNote($variables)
    {
        $query = 'query creditNote($companyId: Int!,$documentId: Int!,$options: CreditNoteOptionsSingle)
                {
                    creditNote(companyId: $companyId,documentId: $documentId,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Gets all credit notes
     *
     * @param $variables array variables of the request
     *
     * @return array Api data
     */
    public static function queryCreditNotes($variables)
    {
        $query = 'query creditNotes($companyId: Int!,$options: CreditNoteOptions)
                {
                    creditNotes(companyId: $companyId,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        options
                        {
                            pagination
                            {
                                page
                                qty
                                count
                            }
                        }                    
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::complex($query, $variables, 'creditNotes');
    }

    /**
     * Creates a credit note
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function mutationCreditNoteCreate($variables)
    {
        $query = 'mutation creditNoteCreate($companyId: Int!,$data: CreditNoteInsert!,$options:CreditNoteMutateOptions)
                {
                    creditNoteCreate(companyId: $companyId,data: $data,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Gets simplified invoice information
     *
     * @param $variables variables of the request
     *
     * @return array Api data
     */
    public static function querySimplifiedInvoice($variables)
    {
        $query = 'query simplifiedInvoice($companyId: Int!,$documentId: Int!,$options: SimplifiedInvoiceOptionsSingle)
                {
                    simplifiedInvoice(companyId: $companyId,documentId: $documentId,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Gets all simplified invoices
     *
     * @param $variables array variables of the request
     *
     * @return array Api data
     */
    public static function querySimplifiedInvoices($variables)
    {
        $query = 'query simplifiedInvoices($companyId: Int!,$options: SimplifiedInvoiceOptions)
                {
                    simplifiedInvoices(companyId: $companyId,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        options
                        {
                            pagination
                            {
                                page
                                qty
                                count
                            }
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::complex($query, $variables, 'simplifiedInvoices');
    }

    /**
     * Creates a simplified invoice
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function mutationSimplifiedInvoiceCreate($variables)
    {
        $query = 'mutation simplifiedInvoiceCreate($companyId: Int!,$data: 
        SimplifiedInvoiceInsert!,$options: SimplifiedInvoiceMutateOptions)
                {
                    simplifiedInvoiceCreate(companyId: $companyId,data: $data,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Update a simplified invoice
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function mutationSimplifiedInvoiceUpdate($variables)
    {
        $query = 'mutation simplifiedInvoiceUpdate($companyId: Int!,$data: SimplifiedInvoiceUpdate!)
        {
            simplifiedInvoiceUpdate(companyId: $companyId,data: $data)
            {
                data
                {
                    documentId
                    status
                }
                errors
                {
                    field
                    msg
                }
            }
        }';

        return Curl::simple($query, $variables);
    }

    /**
     * Creates a purchase order
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryPurchaseOrder($variables)
    {
        $query = 'query purchaseOrder($companyId: Int!,$documentId: Int!,$options: PurchaseOrderOptionsSingle)
                {
                    purchaseOrder(companyId: $companyId,documentId: $documentId,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Gets all purchase orders
     *
     * @param $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryPurchaseOrders($variables)
    {
        $query = 'query purchaseOrders($companyId: Int!,$options: PurchaseOrderOptions)
                {
                    purchaseOrders(companyId: $companyId,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        options
                        {
                            pagination
                            {
                                page
                                qty
                                count
                            }
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::complex($query, $variables, 'purchaseOrders');
    }

    /**
     * Creates a purchase order
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function mutationPurchaseOrderCreate($variables)
    {
        $query = 'mutation purchaseOrderCreate($companyId: Int!,$data: 
        PurchaseOrderInsert!,$options: PurchaseOrderMutateOptions)
                {
                    purchaseOrderCreate(companyId: $companyId,data: $data,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Update a purchase order
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function mutationPurchaseOrderUpdate($variables)
    {
        $query = 'mutation purchaseOrderUpdate($companyId: Int!,$data: PurchaseOrderUpdate!)
        {
            purchaseOrderUpdate(companyId: $companyId,data: $data)
            {
                data
                {
                    documentId
                    status
                }
                errors
                {
                    field
                    msg
                }
            }
        }';

        return Curl::simple($query, $variables);
    }

    /**
     * Creates a pro forma invoice
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryProFormaInvoice($variables)
    {
        $query = 'query proFormaInvoice($companyId: Int!,$documentId: Int!,$options: ProFormaInvoiceOptionsSingle)
                {
                    proFormaInvoice(companyId: $companyId,documentId: $documentId,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Gets all pro forma invoices
     *
     * @param $variables variables of the request
     *
     * @return array Api data
     */
    public static function queryProFormaInvoices($variables)
    {
        $query = 'query proFormaInvoices($companyId: Int!,$options: ProFormaInvoiceOptions)
                {
                    proFormaInvoices(companyId: $companyId,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        options
                        {
                            pagination
                            {
                                page
                                qty
                                count
                            }
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::complex($query, $variables, 'proFormaInvoices');
    }

    /**
     * Creates a pro forma invoice
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function mutationProFormaInvoiceCreate($variables)
    {
        $query = 'mutation proFormaInvoiceCreate($companyId: Int!,$data: 
        ProFormaInvoiceInsert!,$options: ProFormaInvoiceMutateOptions)
                {
                    proFormaInvoiceCreate(companyId: $companyId,data: $data,options: $options)
                    {
                        data
                        {
                            documentId
                            number
                            ourReference
                            yourReference
                            entityVat
                            entityNumber
                            entityName
                            documentSetName
                            totalValue
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Update a pro forma invoice
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function mutationProFormaInvoiceUpdate($variables)
    {
        $query = 'mutation proFormaInvoiceUpdate($companyId: Int!,$data: ProFormaInvoiceUpdate!)
        {
            proFormaInvoiceUpdate(companyId: $companyId,data: $data)
            {
                data
                {
                    documentId
                    status
                }
                errors
                {
                    field
                    msg
                }
            }
        }';

        return Curl::simple($query, $variables);
    }

    /**
     * Get document token and path for simplified invoices
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function querySimplifiedInvoiceGetPDFToken($variables)
    {
        $query = 'query simplifiedInvoiceGetPDFToken($documentId: Int!)
                {
                    simplifiedInvoiceGetPDFToken(documentId: $documentId)
                    {
                        data
                        {
                            token
                            filename
                            path
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Get document token and path for invoices
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryInvoiceGetPDFToken($variables)
    {
        $query = 'query invoiceGetPDFToken($documentId: Int!)
                {
                    invoiceGetPDFToken(documentId: $documentId)
                    {
                        data
                        {
                            token
                            filename
                            path
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Get document token and path for receipts
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryReceiptGetPDFToken($variables)
    {
        $query = 'query receiptGetPDFToken($documentId: Int!)
                {
                    receiptGetPDFToken(documentId: $documentId)
                    {
                        data
                        {
                            token
                            filename
                            path
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Get document token and path for credit notes
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryCreditNoteGetPDFToken($variables)
    {
        $query = 'query creditNoteGetPDFToken($documentId: Int!)
                {
                    creditNoteGetPDFToken(documentId: $documentId)
                    {
                        data
                        {
                            token
                            filename
                            path
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Get document token and path for pro forma invoices
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryProFormaInvoiceGetPDFToken($variables)
    {
        $query = 'query proFormaInvoiceGetPDFToken($documentId: Int!)
                {
                    proFormaInvoiceGetPDFToken(documentId: $documentId)
                    {
                        data
                        {
                            token
                            filename
                            path
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Get document token and path for purchase orders
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function queryPurchaseOrderGetPDFToken($variables)
    {
        $query = 'query purchaseOrderGetPDFToken($documentId: Int!)
                {
                    purchaseOrderGetPDFToken(documentId: $documentId)
                    {
                        data
                        {
                            token
                            filename
                            path
                        }
                        errors
                        {
                            field
                            msg
                        }
                    }
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Creates simplified invoice pdf
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function mutationSimplifiedInvoiceGetPDF($variables)
    {
        $query = 'mutation simplifiedInvoiceGetPDF($companyId: Int!,$documentId: Int!)
                {
                    simplifiedInvoiceGetPDF(companyId: $companyId,documentId: $documentId)
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Creates invoice pdf
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function mutationInvoiceGetPDF($variables)
    {
        $query = 'mutation invoiceGetPDF($companyId: Int!,$documentId: Int!)
                {
                    invoiceGetPDF(companyId: $companyId,documentId: $documentId)
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Creates receipt pdf
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function mutationReceiptGetPDF($variables)
    {
        $query = 'mutation receiptGetPDF($companyId: Int!,$documentId: Int!)
                {
                    receiptGetPDF(companyId: $companyId,documentId: $documentId)
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Creates credit notes pdf
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function mutationCreditNoteGetPDF($variables)
    {
        $query = 'mutation creditNoteGetPDF($companyId: Int!,$documentId: Int!)
                {
                    creditNoteGetPDF(companyId: $companyId,documentId: $documentId)
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Creates pro forma invocie pdf
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function mutationProFormaInvoiceGetPDF($variables)
    {
        $query = 'mutation proFormaInvoiceGetPDF($companyId: Int!,$documentId: Int!)
                {
                    proFormaInvoiceGetPDF(companyId: $companyId,documentId: $documentId)
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Creates purchase order pdf
     *
     * @param $variables
     *
     * @return array returns the Graphql response array or an error array
     */
    public static function mutationPurchaseOrderGetPDF($variables)
    {
        $query = 'mutation purchaseOrderGetPDF($companyId: Int!,$documentId: Int!)
                {
                    purchaseOrderGetPDF(companyId: $companyId,documentId: $documentId)
                }';

        return Curl::simple($query, $variables);
    }

    /**
     * Creates an bill of lading
     *
     * @param array $variables variables of the request
     *
     * @return array Api data
     */
    public static function mutationBillsOfLadingCreate($variables)
    {
        $query = 'mutation billsOfLadingCreate($companyId: Int!,$data: BillsOfLadingInsert!,
        $options: BillsOfLadingMutateOptions){
                billsOfLadingCreate(companyId: $companyId,data: $data,options: $options) {
                    errors{
                        field
                        msg
                    }
                    data{
                        documentId
                        number
                        totalValue
                        documentTotal
                        documentSetName
                        ourReference
                    }
                }
            }';

        return Curl::simple($query, $variables);
    }
}
