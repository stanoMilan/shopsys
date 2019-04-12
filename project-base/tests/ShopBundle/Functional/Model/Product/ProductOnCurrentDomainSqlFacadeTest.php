<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Product;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade;
use Shopsys\ShopBundle\Model\Category\Category;

class ProductOnCurrentDomainSqlFacadeTest extends ProductOnCurrentDomainFacadeTest
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\ShopBundle\Model\Category\Category $category
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginationResultInCategoryWithPageAndLimit(ProductFilterData $productFilterData, Category $category, int $page, int $limit): PaginationResult
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade $productOnCurrentDomainFacade */
        $productOnCurrentDomainFacade = $this->getContainer()->get(ProductOnCurrentDomainFacade::class);

        return $productOnCurrentDomainFacade->getPaginatedProductsInCategory(
            $productFilterData,
            ProductListOrderingConfig::ORDER_BY_NAME_ASC,
            $page,
            $limit,
            $category->getId()
        );
    }
}
