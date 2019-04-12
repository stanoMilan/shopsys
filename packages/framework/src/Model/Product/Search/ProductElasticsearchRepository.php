<?php

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Doctrine\ORM\QueryBuilder;
use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchStructureManager;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class ProductElasticsearchRepository
{
    public const ELASTICSEARCH_INDEX = 'product';

    /**
     * @var string
     */
    protected $indexPrefix;

    /**
     * @var \Elasticsearch\Client
     */
    protected $client;

    /**
     * @var int[][][]
     */
    protected $foundProductIdsCache = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter
     */
    protected $productElasticsearchConverter;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchStructureManager
     */
    protected $elasticsearchStructureManager;

    /**
     * @param string $indexPrefix
     * @param \Elasticsearch\Client $client
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter $productElasticsearchConverter
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchStructureManager $elasticsearchStructureManager
     */
    public function __construct(
        string $indexPrefix,
        Client $client,
        ProductElasticsearchConverter $productElasticsearchConverter,
        ElasticsearchStructureManager $elasticsearchStructureManager
    ) {
        $this->indexPrefix = $indexPrefix;
        $this->client = $client;
        $this->productElasticsearchConverter = $productElasticsearchConverter;
        $this->elasticsearchStructureManager = $elasticsearchStructureManager;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $productQueryBuilder
     * @param string|null $searchText
     */
    public function filterBySearchText(QueryBuilder $productQueryBuilder, $searchText)
    {
        $productIds = $this->getFoundProductIds($productQueryBuilder, $searchText);

        if (count($productIds) > 0) {
            $productQueryBuilder->andWhere('p.id IN (:productIds)')->setParameter('productIds', $productIds);
        } else {
            $productQueryBuilder->andWhere('TRUE = FALSE');
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $productQueryBuilder
     * @param string|null $searchText
     */
    public function addRelevance(QueryBuilder $productQueryBuilder, $searchText)
    {
        $productIds = $this->getFoundProductIds($productQueryBuilder, $searchText);

        if (count($productIds)) {
            $productQueryBuilder->addSelect('field(p.id, ' . implode(',', $productIds) . ') AS HIDDEN relevance');
        } else {
            $productQueryBuilder->addSelect('-1 AS HIDDEN relevance');
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $productQueryBuilder
     * @param $searchText
     * @return int[]
     */
    protected function getFoundProductIds(QueryBuilder $productQueryBuilder, $searchText)
    {
        $domainId = $productQueryBuilder->getParameter('domainId')->getValue();

        if (!isset($this->foundProductIdsCache[$domainId][$searchText])) {
            $foundProductIds = $this->getProductIdsBySearchText($domainId, $searchText);

            $this->foundProductIdsCache[$domainId][$searchText] = $foundProductIds;
        }

        return $this->foundProductIdsCache[$domainId][$searchText];
    }

    /**
     * @param int $domainId
     * @return string
     */
    protected function getIndexName(int $domainId): string
    {
        return $this->indexPrefix . self::ELASTICSEARCH_INDEX . $domainId;
    }

    /**
     * @param int $domainId
     * @param string|null $searchText
     * @return int[]
     */
    public function getProductIdsBySearchText(int $domainId, ?string $searchText): array
    {
        if (!$searchText) {
            return [];
        }
        $parameters = $this->createQuery($this->getIndexName($domainId), $searchText);
        $result = $this->client->search($parameters);
        return $this->extractIds($result);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingModeId
     * @param int $categoryId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\ResultIdsData
     */
    public function getSortedProductIdsByFilterDataFromCategory(
        int $domainId,
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $categoryId,
        PricingGroup $pricingGroup,
        int $page,
        int $limit
    ): ResultIdsData {
        $filterQuery = $this->createFilterQuery($this->getIndexName($domainId), $productFilterData, $orderingModeId, $pricingGroup);
        $filterQuery->filterByCategory([$categoryId]);

        $filterQuery->setPage($page);
        $filterQuery->setLimit($limit);

        $result = $this->client->search($filterQuery->getQuery());

        return new ResultIdsData($this->extractTotalCount($result), $this->extractIds($result));
    }

    /**
     * @param string $indexName
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingModeId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    protected function createFilterQuery(string $indexName, ProductFilterData $productFilterData, string $orderingModeId, PricingGroup $pricingGroup): FilterQuery
    {
        $filterQuery = new FilterQuery($indexName);

        $filterQuery->filterOnlySellable();

        $brandIds = [];
        foreach ($productFilterData->brands as $brand) {
            $brandIds[] = $brand->getId();
        }
        if ($brandIds) {
            $filterQuery->filterByBrands($brandIds);
        }

        $flagIds = [];
        foreach ($productFilterData->flags as $flag) {
            $flagIds[] = $flag->getId();
        }
        if ($flagIds) {
            $filterQuery->filterByFlags($flagIds);
        }

        if ($productFilterData->parameters) {
            $parameters = $this->flattenParameterFilterData($productFilterData->parameters);

            $filterQuery->filterByParameters($parameters);
        }

        if ($productFilterData->inStock) {
            $filterQuery->filterOnlyInStock();
        }

        if ($productFilterData->maximalPrice || $productFilterData->minimalPrice) {
            $filterQuery->filterByPrices($pricingGroup, $productFilterData->minimalPrice, $productFilterData->maximalPrice);
        }

        $filterQuery->applyOrdering($orderingModeId, $pricingGroup);

        return $filterQuery;
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html
     * @param string $indexName
     * @param string $searchText
     * @return array
     */
    protected function createQuery(string $indexName, string $searchText): array
    {
        $query = new FilterQuery($indexName);
        $query->search($searchText);
        return $query->getQuery();
    }

    /**
     * @param array $result
     * @return int[]
     */
    protected function extractIds(array $result): array
    {
        $hits = $result['hits']['hits'];
        return array_column($hits, '_id');
    }

    /**
     * @param array $result
     * @return int
     */
    protected function extractTotalCount(array $result): int
    {
        return (int)$result['hits']['total'];
    }

    /**
     * @param int $domainId
     * @param array $data
     */
    public function bulkUpdate(int $domainId, array $data): void
    {
        $body = $this->productElasticsearchConverter->convertBulk(
            $this->elasticsearchStructureManager->getIndexName($domainId, self::ELASTICSEARCH_INDEX),
            $data
        );

        $params = [
            'body' => $body,
        ];
        $this->client->bulk($params);
    }

    /**
     * @param int $domainId
     * @param int[] $keepIds
     */
    public function deleteNotPresent(int $domainId, array $keepIds): void
    {
        $this->client->deleteByQuery([
            'index' => $this->elasticsearchStructureManager->getIndexName($domainId, self::ELASTICSEARCH_INDEX),
            'type' => '_doc',
            'body' => [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            'ids' => [
                                'values' => $keepIds,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData[] $parameters
     * @return array
     */
    protected function flattenParameterFilterData(array $parameters): array
    {
        $return = [];

        foreach ($parameters as $parameterFilterData) {
            /* @var $parameterFilterData \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData */
            if (\count($parameterFilterData->values) === 0) {
                continue;
            }

            foreach ($parameters as $parameter) {
                $return[$parameter->parameter->getId()] =
                    \array_map(
                        static function (ParameterValue $item) {
                            return $item->getId();
                        },
                        $parameter->values
                    );
            }
        }

        return $return;
    }
}
