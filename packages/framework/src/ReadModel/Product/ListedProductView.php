<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\ReadModel\Product;

use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\ReadModel\Image\ImageView;

class ListedProductView
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Shopsys\FrameworkBundle\ReadModel\Image\ImageView|null
     */
    protected $image;

    /**
     * @var string
     */
    protected $availability;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    protected $sellingPrice;

    /**
     * @var string
     */
    protected $shortDescription;

    /**
     * @var int[]
     */
    protected $flagIds = [];

    /**
     * @var string
     */
    protected $detailUrl;

    /**
     * @var \Shopsys\FrameworkBundle\ReadModel\Product\ProductActionView
     */
    protected $action;

    /**
     * @param int $id
     * @param string $name
     * @param \Shopsys\FrameworkBundle\ReadModel\Image\ImageView|null $image
     * @param string $availability
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice $sellingPrice
     * @param string $shortDescription
     * @param int[] $flagIds
     * @param string $detailUrl
     * @param \Shopsys\FrameworkBundle\ReadModel\Product\ProductActionView $action
     */
    public function __construct(
        int $id,
        string $name,
        ?ImageView $image,
        string $availability,
        ProductPrice $sellingPrice,
        string $shortDescription,
        array $flagIds,
        string $detailUrl,
        ProductActionView $action
    ) {
        foreach ($flagIds as $flagId) {
            if (!is_int($flagId)) {
                throw new \InvalidArgumentException('Expected an array of integers.');
            }
        }

        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->availability = $availability;
        $this->sellingPrice = $sellingPrice;
        $this->shortDescription = $shortDescription;
        $this->flagIds = $flagIds;
        $this->detailUrl = $detailUrl;
        $this->action = $action;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \Shopsys\FrameworkBundle\ReadModel\Image\ImageView|null
     */
    public function getImage(): ?ImageView
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getAvailability(): string
    {
        return $this->availability;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function getSellingPrice(): ProductPrice
    {
        return $this->sellingPrice;
    }

    /**
     * @return string
     */
    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    /**
     * @return int[]Z
     */
    public function getFlagIds(): array
    {
        return $this->flagIds;
    }

    /**
     * @return string
     */
    public function getDetailUrl(): string
    {
        return $this->detailUrl;
    }

    /**
     * @return \Shopsys\FrameworkBundle\ReadModel\Product\ProductActionView
     */
    public function getAction(): ProductActionView
    {
        return $this->action;
    }
}
