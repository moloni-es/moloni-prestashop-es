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
     * @var int
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
     * @var int
     *
     * @ORM\Column(name="ps_combination_reference", type="string", length=250)
     */
    private $psCombinationReference;

    /**
     * @var string
     *
     * @ORM\Column(name="ml_product_id", type="integer")
     */
    private $mlProductId;

    /**
     * @var int
     *
     * @ORM\Column(name="ml_product_reference", type="string", length=250)
     */
    private $mlProductReference;

    /**
     * @var string
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
     * @return string
     */
    public function getMlProductId(): string
    {
        return $this->mlProductId;
    }

    /**
     * @param string $mlProductId
     */
    public function setMlProductId(string $mlProductId): void
    {
        $this->mlProductId = $mlProductId;
    }

    /**
     * @return string
     */
    public function getActive(): string
    {
        return $this->active;
    }

    /**
     * @param string $active
     */
    public function setActive(string $active): void
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getPsCombinationId(): string
    {
        return $this->psCombinationId;
    }

    /**
     * @param string $psCombinationId
     */
    public function setPsCombinationId(string $psCombinationId): void
    {
        $this->psCombinationId = $psCombinationId;
    }

    /**
     * @return int
     */
    public function getPsProductReference(): int
    {
        return $this->psProductReference;
    }

    /**
     * @param int $psProductReference
     */
    public function setPsProductReference(int $psProductReference): void
    {
        $this->psProductReference = $psProductReference;
    }

    /**
     * @return int
     */
    public function getPsCombinationReference(): int
    {
        return $this->psCombinationReference;
    }

    /**
     * @param int $psCombinationReference
     */
    public function setPsCombinationReference(int $psCombinationReference): void
    {
        $this->psCombinationReference = $psCombinationReference;
    }

    /**
     * @return int
     */
    public function getMlProductReference(): int
    {
        return $this->mlProductReference;
    }

    /**
     * @param int $mlProductReference
     */
    public function setMlProductReference(int $mlProductReference): void
    {
        $this->mlProductReference = $mlProductReference;
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
            'active' => $this->getActive(),
        ];
    }
}
