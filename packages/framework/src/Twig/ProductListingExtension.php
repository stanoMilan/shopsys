<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\ReadModel\Product\ListProductsFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProductListingExtension extends AbstractExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\ReadModel\Product\ListProductsFacade
     */
    private $listProductsFacade;

    /**
     * @param \Shopsys\FrameworkBundle\ReadModel\Product\ListProductsFacade $listProductsFacade
     */
    public function __construct(ListProductsFacade $listProductsFacade)
    {
        $this->listProductsFacade = $listProductsFacade;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'viewListedProducts',
                function (array $products) {
                    return $this->listProductsFacade->getListedProducts($products);
                }
            ),
            new TwigFunction(
                'viewListedProduct',
                function (Product $product) {
                    return $this->listProductsFacade->getListedProduct($product);
                }
            ),
        ];
    }
}
