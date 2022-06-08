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

namespace Moloni\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Moloni\Repository\MoloniProductAssociationsRepository")
 */
class MoloniProductAssociations
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="ps_product_id", type="integer")
     */
    private $psProductId;

    /**
     * @var string
     *
     * @ORM\Column(name="ps_product_reference", type="string", length=250)
     */
    private $psProductReference;

    /**
     * @var string
     *
     * @ORM\Column(name="ps_combination_id", type="integer")
     */
    private $psCombinationId;

    /**
     * @var string
     *
     * @ORM\Column(name="ps_combination_reference", type="string", length=250)
     */
    private $psCombinationReference;

    /**
     * @var int
     *
     * @ORM\Column(name="ml_product_id", type="integer")
     */
    private $mlProductId;

    /**
     * @var string
     *
     * @ORM\Column(name="ml_product_reference", type="string", length=250)
     */
    private $mlProductReference;

    /**
     * @var int
     *
     * @ORM\Column(name="ml_variant_id", type="integer")
     */
    private $mlVariantId;

    /**
     * @var int
     *
     * @ORM\Column(name="active", type="integer")
     */
    private $active;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPsProductId(): int
    {
        return $this->psProductId;
    }

    /**
     * @param int $psProductId
     */
    public function setPsProductId(int $psProductId): void
    {
        $this->psProductId = $psProductId;
    }

    /**
     * @return int
     */
    public function getMlProductId(): int
    {
        return $this->mlProductId;
    }

    /**
     * @param int $mlProductId
     */
    public function setMlProductId(int $mlProductId): void
    {
        $this->mlProductId = $mlProductId;
    }

    /**
     * @return int
     */
    public function getActive(): int
    {
        return $this->active;
    }

    /**
     * @param int $active
     */
    public function setActive(int $active): void
    {
        $this->active = $active;
    }

    /**
     * @return int
     */
    public function getPsCombinationId(): int
    {
        return $this->psCombinationId;
    }

    /**
     * @param int $psCombinationId
     */
    public function setPsCombinationId(int $psCombinationId): void
    {
        $this->psCombinationId = $psCombinationId;
    }

    /**
     * @return string
     */
    public function getPsProductReference(): string
    {
        return $this->psProductReference;
    }

    /**
     * @param string $psProductReference
     */
    public function setPsProductReference(string $psProductReference): void
    {
        $this->psProductReference = $psProductReference;
    }

    /**
     * @return string
     */
    public function getPsCombinationReference(): string
    {
        return $this->psCombinationReference;
    }

    /**
     * @param string $psCombinationReference
     */
    public function setPsCombinationReference(string $psCombinationReference): void
    {
        $this->psCombinationReference = $psCombinationReference;
    }

    /**
     * @return string
     */
    public function getMlProductReference(): string
    {
        return $this->mlProductReference;
    }

    /**
     * @param string $mlProductReference
     */
    public function setMlProductReference(string $mlProductReference): void
    {
        $this->mlProductReference = $mlProductReference;
    }

    /**
     * @return int
     */
    public function getMlVariantId(): int
    {
        return $this->mlVariantId;
    }

    /**
     * @param int $mlVariantId
     */
    public function setMlVariantId(int $mlVariantId): void
    {
        $this->mlVariantId = $mlVariantId;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'ps_product_id' => $this->getPsProductId(),
            'ps_product_reference' => $this->getPsProductReference(),
            'ps_combination_id' => $this->getPsCombinationId(),
            'ps_combination_reference' => $this->getPsCombinationReference(),
            'ml_product_id' => $this->getMlProductId(),
            'ml_product_reference' => $this->getMlProductReference(),
            'ml_variant_id' => $this->getMlVariantId(),
            'active' => $this->getActive(),
        ];
    }
}
