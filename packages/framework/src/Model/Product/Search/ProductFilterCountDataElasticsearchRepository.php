<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductFilterCountDataElasticsearchRepository
{
    /**
     * @var \Elasticsearch\Client
     */
    protected $client;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer
     */
    protected $productFilterDataToQueryTransformer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\AggregationResultToProductFilterCountDataTransformer
     */
    protected $aggregationResultToCountDataTransformer;

    /**
     * @param \Elasticsearch\Client $client
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\AggregationResultToProductFilterCountDataTransformer $aggregationResultToCountDataTransformer
     */
    public function __construct(
        Client $client,
        ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer,
        AggregationResultToProductFilterCountDataTransformer $aggregationResultToCountDataTransformer
    ) {
        $this->client = $client;
        $this->productFilterDataToQueryTransformer = $productFilterDataToQueryTransformer;
        $this->aggregationResultToCountDataTransformer = $aggregationResultToCountDataTransformer;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $baseFilterQuery
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
    public function getProductFilterCountDataInSearch(ProductFilterData $productFilterData, FilterQuery $baseFilterQuery): ProductFilterCountData
    {
        $nonPlusNumbersFilterQuery = clone $baseFilterQuery;
        $this->productFilterDataToQueryTransformer->addFlagsToQuery($productFilterData, $nonPlusNumbersFilterQuery);
        $this->productFilterDataToQueryTransformer->addBrandsToQuery($productFilterData, $nonPlusNumbersFilterQuery);

        $aggregationResult = $this->client->search($nonPlusNumbersFilterQuery->getNonPlusNumbersQuery());
        $countData = $this->aggregationResultToCountDataTransformer->translateNonPlusNumbers($aggregationResult);

        if (count($productFilterData->flags)) {
            $plusFlagsQuery = clone $baseFilterQuery;
            $this->productFilterDataToQueryTransformer->addBrandsToQuery($productFilterData, $plusFlagsQuery);
            $countData->countByFlagId = $this->calculateFlagsPlusNumbers($productFilterData, $plusFlagsQuery);
        }

        if (count($productFilterData->brands)) {
            $plusBrandsQuery = clone $baseFilterQuery;
            $this->productFilterDataToQueryTransformer->addFlagsToQuery($productFilterData, $plusBrandsQuery);
            $countData->countByBrandId = $this->calculateBrandsPlusNumbers($productFilterData, $plusBrandsQuery);
        }

        return $countData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $baseFilterQuery
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
    public function getProductFilterCountDataInCategory(ProductFilterData $productFilterData, FilterQuery $baseFilterQuery): ProductFilterCountData
    {
        $nonPlusNumbersFilterQuery = clone $baseFilterQuery;
        $this->productFilterDataToQueryTransformer->addFlagsToQuery($productFilterData, $nonPlusNumbersFilterQuery);
        $this->productFilterDataToQueryTransformer->addBrandsToQuery($productFilterData, $nonPlusNumbersFilterQuery);
        $this->productFilterDataToQueryTransformer->addParametersToQuery($productFilterData, $nonPlusNumbersFilterQuery);

        $aggregationResult = $this->client->search($nonPlusNumbersFilterQuery->getNonPlusNumbersWithParametersQuery());
        $countData = $this->aggregationResultToCountDataTransformer->translateNonPlusNumbersWithParameters($aggregationResult);

        if (count($productFilterData->flags)) {
            $plusFlagsQuery = clone $baseFilterQuery;
            $this->productFilterDataToQueryTransformer->addBrandsToQuery($productFilterData, $plusFlagsQuery);
            $this->productFilterDataToQueryTransformer->addParametersToQuery($productFilterData, $plusFlagsQuery);
            $countData->countByFlagId = $this->calculateFlagsPlusNumbers($productFilterData, $plusFlagsQuery);
        }

        if (count($productFilterData->brands)) {
            $plusBrandsQuery = clone $baseFilterQuery;
            $this->productFilterDataToQueryTransformer->addFlagsToQuery($productFilterData, $plusBrandsQuery);
            $this->productFilterDataToQueryTransformer->addParametersToQuery($productFilterData, $plusBrandsQuery);
            $countData->countByBrandId = $this->calculateBrandsPlusNumbers($productFilterData, $plusBrandsQuery);
        }

        if (count($productFilterData->parameters)) {
            $plusParametersQuery = clone $baseFilterQuery;
            $this->productFilterDataToQueryTransformer->addFlagsToQuery($productFilterData, $plusParametersQuery);
            $this->productFilterDataToQueryTransformer->addBrandsToQuery($productFilterData, $plusParametersQuery);

            foreach ($productFilterData->parameters as $key => $parameterFilterData) {
                $currentFilterData = clone $productFilterData;
                unset($currentFilterData->parameters[$key]);
                $countData->countByParameterIdAndValueId[$parameterFilterData->parameter->getId()] += $this->calculateParameterPlusNumbers($currentFilterData, $parameterFilterData, $plusParametersQuery);
            }
        }

        return $countData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $plusFlagsQuery
     * @return int[]
     */
    protected function calculateFlagsPlusNumbers(ProductFilterData $productFilterData, FilterQuery $plusFlagsQuery): array
    {
        $flagIds = [];
        foreach ($productFilterData->flags as $flag) {
            $flagIds[] = $flag->getId();
        }
        $flagsPlusNumberResult = $this->client->search($plusFlagsQuery->getFlagsPlusNumbersQuery($flagIds));
        return $this->aggregationResultToCountDataTransformer->translateFlagsPlusNumbers($flagsPlusNumberResult);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $plusFlagsQuery
     * @return int[]
     */
    protected function calculateBrandsPlusNumbers(ProductFilterData $productFilterData, FilterQuery $plusFlagsQuery): array
    {
        $brandsIds = [];
        foreach ($productFilterData->brands as $brand) {
            $brandsIds[] = $brand->getId();
        }
        $brandsPlusNumberResult = $this->client->search($plusFlagsQuery->getBrandsPlusNumbersQuery($brandsIds));
        return $this->aggregationResultToCountDataTransformer->translateBrandsPlusNumbers($brandsPlusNumberResult);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $currentFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData $parameterFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $filterQuery
     * @return array
     */
    protected function calculateParameterPlusNumbers(
        ProductFilterData $currentFilterData,
        ParameterFilterData $parameterFilterData,
        FilterQuery $filterQuery
    ): array {
        $parameterId = $parameterFilterData->parameter->getId();
        $valuesIds = [];
        foreach ($parameterFilterData->values as $parameterValue) {
            $valuesIds[] = $parameterValue->getId();
        }

        $currentQuery = clone $filterQuery;
        $this->productFilterDataToQueryTransformer->addParametersToQuery($currentFilterData, $currentQuery);
        $currentQueryResult = $this->client->search($currentQuery->getParametersPlusNumbersQuery($parameterId, $valuesIds));
        return $this->aggregationResultToCountDataTransformer->translateParameterValuesPlusNumbers($currentQueryResult);
    }
}
