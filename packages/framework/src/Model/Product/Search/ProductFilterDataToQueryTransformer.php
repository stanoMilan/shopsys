<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class ProductFilterDataToQueryTransformer
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $filterQuery
     */
    public function addBrandsToQuery(ProductFilterData $productFilterData, FilterQuery $filterQuery): void
    {
        $brandIds = [];
        foreach ($productFilterData->brands as $brand) {
            $brandIds[] = $brand->getId();
        }
        if ($brandIds) {
            $filterQuery->filterByBrands($brandIds);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $filterQuery
     */
    public function addFlagsToQuery(ProductFilterData $productFilterData, FilterQuery $filterQuery): void
    {
        $flagIds = [];
        foreach ($productFilterData->flags as $flag) {
            $flagIds[] = $flag->getId();
        }
        if ($flagIds) {
            $filterQuery->filterByFlags($flagIds);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $filterQuery
     */
    public function addParametersToQuery(ProductFilterData $productFilterData, FilterQuery $filterQuery): void
    {
        if ($productFilterData->parameters) {
            $parameters = $this->flattenParameterFilterData($productFilterData->parameters);

            $filterQuery->filterByParameters($parameters);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData[] $parameters
     * @return array
     */
    protected function flattenParameterFilterData(array $parameters): array
    {
        $result = [];

        foreach ($parameters as $parameterFilterData) {
            if (\count($parameterFilterData->values) === 0) {
                continue;
            }

            $result[$parameterFilterData->parameter->getId()] =
                \array_map(
                    static function (ParameterValue $item) {
                        return $item->getId();
                    },
                    $parameterFilterData->values
                );
        }

        return $result;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $filterQuery
     */
    public function addStockToQuery(ProductFilterData $productFilterData, FilterQuery $filterQuery): void
    {
        if ($productFilterData->inStock) {
            $filterQuery->filterOnlyInStock();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $filterQuery
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     */
    public function addPricesToQuery(ProductFilterData $productFilterData, FilterQuery $filterQuery, PricingGroup $pricingGroup): void
    {
        if ($productFilterData->maximalPrice || $productFilterData->minimalPrice) {
            $filterQuery->filterByPrices($pricingGroup, $productFilterData->minimalPrice, $productFilterData->maximalPrice);
        }
    }
}
