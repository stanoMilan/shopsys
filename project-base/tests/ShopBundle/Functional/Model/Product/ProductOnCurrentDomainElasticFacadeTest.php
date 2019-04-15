<?php

namespace Tests\ShopBundle\Functional\Model\Product;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade;
use Shopsys\ShopBundle\Model\Category\Category;

class ProductOnCurrentDomainElasticFacadeTest extends ProductOnCurrentDomainFacadeTest
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
        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade */
        $productOnCurrentDomainFacade = $this->getContainer()->get(ProductOnCurrentDomainElasticFacade::class);

        return $productOnCurrentDomainFacade->getPaginatedProductsInCategory(
            $productFilterData,
            ProductListOrderingConfig::ORDER_BY_NAME_ASC,
            $page,
            $limit,
            $category->getId()
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginatedProductsForBrand(Brand $brand): PaginationResult
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade */
        $productOnCurrentDomainFacade = $this->getContainer()->get(ProductOnCurrentDomainElasticFacade::class);
        $page = 1;
        $limit = 1000;

        return $productOnCurrentDomainFacade->getPaginatedProductsForBrand(
            ProductListOrderingConfig::ORDER_BY_NAME_ASC,
            $page,
            $limit,
            $brand->getId()
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginationResultInSearch(ProductFilterData $productFilterData, string $searchText): PaginationResult
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade */
        $productOnCurrentDomainFacade = $this->getContainer()->get(ProductOnCurrentDomainElasticFacade::class);
        $page = 1;
        $limit = 1000;

        return $productOnCurrentDomainFacade->getPaginatedProductsForSearch(
            $searchText,
            $productFilterData,
            ProductListOrderingConfig::ORDER_BY_NAME_ASC,
            $page,
            $limit
        );
    }

    /**
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getSearchAutocompleteProducts(string $searchText): PaginationResult
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade */
        $productOnCurrentDomainFacade = $this->getContainer()->get(ProductOnCurrentDomainElasticFacade::class);
        $limit = 1000;

        return $productOnCurrentDomainFacade->getSearchAutocompleteProducts(
            $searchText,
            $limit
        );
    }
}
