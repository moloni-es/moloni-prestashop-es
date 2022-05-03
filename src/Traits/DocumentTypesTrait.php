<?php

namespace Moloni\Traits;

trait DocumentTypesTrait
{
    /**
     * Returns the documents that can be generated
     *
     * @return array returns array with documents and abbreviations
     */
    public function getDocumentsTypes(): array
    {
        return [
            $this->trans('Invoice', 'Modules.Molonies.Settings') => 'invoices',
            $this->trans('Invoice + Receipt', 'Modules.Molonies.Settings') => 'receipts',
            $this->trans('Purchase Order', 'Modules.Molonies.Settings') => 'purchaseOrders',
            $this->trans('Pro Forma Invoice', 'Modules.Molonies.Settings') => 'proFormaInvoices',
            $this->trans('Simplified invoice', 'Modules.Molonies.Settings') => 'simplifiedInvoices',
        ];
    }
}
