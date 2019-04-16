<?php

namespace Shopsys\ShopBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductFacade as BaseBestsellingProductFacade;

class BestsellingProductFacade extends BaseBestsellingProductFacade
{
    public const MAX_RESULTS = 1;
}
