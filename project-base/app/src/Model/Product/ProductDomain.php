<?php

declare(strict_types=1);

namespace App\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableChild;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableParentProperty;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain as BaseProductDomain;

/**
 * @ORM\Table(
 *     name="product_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="product_domain", columns={"product_id", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 * @property \Doctrine\Common\Collections\ArrayCollection<int, \App\Model\Product\Flag\Flag> $flags
 * @method \App\Model\Product\Flag\Flag[] getFlags()
 * @method setFlags(\App\Model\Product\Flag\Flag[] $flags)
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Product\Flag\Flag> $flags
 */
#[LoggableChild(LoggableChild::STRATEGY_INCLUDE_ALL)]
class ProductDomain extends BaseProductDomain
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false, name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    #[LoggableParentProperty]
    protected $product;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $calculatedSaleExclusion;

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     */
    public function __construct(Product $product, $domainId)
    {
        parent::__construct($product, $domainId);

        $this->calculatedSaleExclusion = true;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        return $this->product;
    }

    /**
     * @return bool
     */
    public function getCalculatedSaleExclusion(): bool
    {
        return $this->calculatedSaleExclusion;
    }
}
