<?php

namespace Moloni\Api\Endpoints\Documents;

use Moloni\Api\Endpoints\Endpoint;
use Moloni\Exceptions\MoloniApiException;

class BillsOfLading extends Endpoint
{
    /**
     * Creates an bill of lading
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationBillsOfLadingCreate(?array $variables = []): array
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

        return $this->simplePost($query, $variables);
    }

    /**
     * Send bill of lading by email
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationBillsOfLadingSendEmail(?array $variables = []): array
    {
        $query = 'mutation billsOfLadingSendMail($companyId: Int!,$documents: [Int]!,$mailData: MailData)
        {
            billsOfLadingSendMail(companyId: companyId,documents: $documents,mailData: $mailData)
        }';

        return $this->simplePost($query, $variables);
    }
}
