<?php

namespace Shopsys\ShopBundle\Model\Product;

use Shopsys\FrameworkBundle\Twig\PriceExtension as BasePriceExtension;

class PriceExtension extends BasePriceExtension
{
    const MINIMUM_FRACTION_DIGITS = 3;
}
