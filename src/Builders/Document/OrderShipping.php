<?php

namespace Moloni\Builders\Document;

use Moloni\Api\MoloniApiClient;
use Moloni\Builders\Interfaces\BuilderItemInterface;
use Moloni\Enums\ProductType;
use Moloni\Exceptions\Document\MoloniDocumentShippingException;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Helpers\Moloni;

class OrderShipping implements BuilderItemInterface
{
    /**
     * Product id in Moloni
     *
     * @var int
     */
    public $productId = 0;

    /**
     * Moloni product type
     *
     * @var int
     */
    protected $type;

    /**
     * Product name
     *
     * @var string
     */
    protected $name;

    /**
     * Product price
     *
     * @var float
     */
    protected $price;

    /**
     * Product price with taxes
     *
     * @var float
     */
    protected $priceWithTaxes;

    /**
     * Product quantity
     *
     * @var float
     */
    protected $quantity;

    /**
     * Product discount
     *
     * @var float
     */
    protected $discount;

    /**
     * Product reference
     *
     * @var string
     */
    protected $reference;

    /**
     * Taxes builder
     *
     * @var OrderProductTax[]
     */
    protected $taxes;

    /**
     * Product Exemption reason
     *
     * @var string
     */
    protected $exemptionReason;

    /**
     * Fiscal Zone
     *
     * @var string
     */
    protected $ficalZone;

    /**
     * Warehouse settings
     *
     * @var array|null Warehouse settings
     */
    protected $currencyExchange;

    /**
     * Order product data
     *
     * @var array
     */
    protected $orderShipping;

    public function __construct(array $orderShipping, ?string $fiscalZone = 'ES')
    {
        $this->orderShipping = $orderShipping;
        $this->ficalZone = $fiscalZone;
    }

    //          PUBLICS          //

    /**
     * Exports product data to array format
     *
     * @param int|null $order
     *
     * @return array
     */
    public function toArray(?int $order = 0): array
    {
        return [
            'ordering' => $order,
        ];
    }

    public function insert(): void
    {

    }

    /**
     * Searches product in Moloni
     *
     * @throws MoloniDocumentShippingException
     */
    public function search(): OrderShipping
    {
        return $this->getByReference();
    }

    //          PRIVATES          //

    /**
     * Start initial values
     *
     * @return $this
     */
    protected function init(): OrderShipping
    {
        $this
            ->setReference()
            ->setName()
            ->setQuantity()
            ->setTaxes()
            ->setPrice()
            ->setType()
            ->setDiscounts();

        return $this;
    }

    //          SETS          //

    /**
     * Define product reference
     *
     * @return OrderShipping
     */
    protected function setReference(): OrderShipping
    {
        $this->reference = 'ENVIO';

        return $this;
    }

    /**
     * Define name
     *
     * @return OrderShipping
     */
    protected function setName(): OrderShipping
    {
        $this->name = $this->orderShipping['carrier_name'] ?? 'CARRIER';

        return $this;
    }

    /**
     * Define price
     *
     * @return OrderShipping
     */
    protected function setPrice(): OrderShipping
    {
        $this->price = $this->orderShipping['shipping_cost_tax_excl'] ?? 0;
        $this->priceWithTaxes = $this->orderShipping['shipping_cost_tax_incl'] ?? 0;

        return $this;
    }

    /**
     * Define type
     *
     * @return OrderShipping
     */
    protected function setType(): OrderShipping
    {
        $this->type = ProductType::SERVICE;

        return $this;
    }

    /**
     * Calculate discounts
     *
     * @return OrderShipping
     */
    protected function setDiscounts(): OrderShipping
    {
        return $this;
    }

    /**
     * Define product quantity
     *
     * @return OrderShipping
     */
    protected function setQuantity(): OrderShipping
    {
        $this->quantity = 1;

        return $this;
    }

    /**
     * Build product taxes
     */
    protected function setTaxes(): OrderShipping
    {
        return $this;
    }

    //          REQUESTS          //

    /**
     * Search shipping by reference
     *
     * @return OrderShipping
     *
     * @throws MoloniDocumentShippingException
     */
    protected function getByReference(): OrderShipping
    {
        $variables = [
            'companyId' => (int) Moloni::get('company_id'),
            'options' => [
                'search' => [
                    'field' => 'reference',
                    'value' => $this->reference,
                ],
            ],
        ];

        try {
            $query = MoloniApiClient::products()
                ->queryProducts($variables);

            if (!empty($query)) {
                $this->productId = $query[0]['productId'];
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentShippingException('Error fetching shipping by reference: ({0})', [$this->reference], $e->getData());
        }

        return $this;
    }
}
