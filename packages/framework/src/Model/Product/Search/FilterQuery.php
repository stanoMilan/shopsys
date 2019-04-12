<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;

class FilterQuery
{
    /** @var array */
    protected $filters = [];

    /** @var string */
    protected $indexName;

    /** @var array */
    protected $sorting = ['ordering_priority' => 'asc', 'name.keyword' => 'asc'];

    /** @var int */
    protected $limit = 1000;

    /** @var int */
    protected $page = 1;

    /** @var array */
    protected $match;

    /**
     * @param string $indexName
     */
    public function __construct(string $indexName)
    {
        $this->indexName = $indexName;
        $this->match = $this->matchAll();
    }

    /**
     * @param string $orderingModeId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function applyOrdering(string $orderingModeId, PricingGroup $pricingGroup): self
    {
        if ($orderingModeId === ProductListOrderingConfig::ORDER_BY_NAME_ASC) {
            $this->sorting = [
                'name.keyword' => 'asc',
            ];
        } elseif ($orderingModeId === ProductListOrderingConfig::ORDER_BY_NAME_DESC) {
            $this->sorting = [
                'name.keyword' => 'desc',
            ];
        } elseif ($orderingModeId === ProductListOrderingConfig::ORDER_BY_PRICE_ASC) {
            $this->sorting = [
                'prices.amount' => [
                    'order' => 'asc',
                    'nested' => [
                        'path' => 'prices',
                        'filter' => [
                            'term' => [
                                'prices.pricing_group_id' => $pricingGroup->getId(),
                            ],
                        ],
                    ],
                ],
                'ordering_priority' => 'asc',
                'name.keyword' => 'asc',
            ];
        } elseif ($orderingModeId === ProductListOrderingConfig::ORDER_BY_PRICE_DESC) {
            $this->sorting = [
                'prices.amount' => [
                    'order' => 'desc',
                    'nested' => [
                        'path' => 'prices',
                        'filter' => [
                            'term' => [
                                'prices.pricing_group_id' => $pricingGroup->getId(),
                            ],
                        ],
                    ],
                ],
                'ordering_priority' => 'asc',
                'name.keyword' => 'desc',
            ];
        }

        return $this;
    }

    /**
     * @param array $parameters
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterByParameters(array $parameters): self
    {
        foreach ($parameters as $parameterId => $parameterValues) {
            $this->filters[] = [
                'nested' => [
                    'path' => 'parameters',
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match_all' => new \stdClass(),
                            ],
                            'filter' => [
                                ['term' => [
                                    'parameters.parameter_id' => $parameterId,
                                ]],
                                ['terms' => [
                                    'parameters.parameter_value_id' => $parameterValues,
                                ]],
                            ],
                        ],
                    ],
                ],
            ];
        }

        return $this;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $minimalPrice
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $maximalPrice
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterByPrices(PricingGroup $pricingGroup, Money $minimalPrice = null, Money $maximalPrice = null): self
    {
        $prices = [];
        if ($minimalPrice !== null) {
            $prices['gte'] = (float)$minimalPrice->getAmount();
        }
        if ($maximalPrice !== null) {
            $prices['lte'] = (float)$maximalPrice->getAmount();
        }

        $this->filters[] = ['nested' => [
            'path' => 'prices',
            'query' => [
                'bool' => [
                    'must' => [
                        'match_all' => new \stdClass(),
                    ],
                    'filter' => [
                        ['term' => [
                            'prices.pricing_group_id' => $pricingGroup->getId(),
                        ]],
                        ['range' => [
                            'prices.amount' => $prices,
                        ],
                        ],
                    ],
                ],
            ],
        ]];

        return $this;
    }

    /**
     * @param int[] $categoryIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterByCategory(array $categoryIds): self
    {
        $this->filters[] = ['terms' => [
            'categories' => $categoryIds,
        ]];

        return $this;
    }

    /**
     * @param int[] $brandIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterByBrands(array $brandIds): self
    {
        $this->filters[] = ['terms' => [
            'brand' => $brandIds,
        ]];

        return $this;
    }

    /**
     * @param int[] $flagIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterByFlags(array $flagIds): self
    {
        $this->filters[] = ['terms' => [
            'flags' => $flagIds,
        ]];

        return $this;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterOnlyInStock(): self
    {
        $this->filters[] = ['term' => [
            'in_stock' => true,
        ]];

        return $this;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterOnlySellable(): self
    {
        $this->filters[] = ['term' => [
            'calculated_selling_denied' => false,
        ]];

        return $this;
    }

    /**
     * @param string $text
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function search(string $text): self
    {
        $this->match = [
            'multi_match' => [
                'query' => $text,
                'fields' => [
                    'name.full_with_diacritic^60',
                    'name.full_without_diacritic^50',
                    'name^45',
                    'name.edge_ngram_with_diacritic^40',
                    'name.edge_ngram_without_diacritic^35',
                    'catnum^50',
                    'catnum.edge_ngram^25',
                    'partno^40',
                    'partno.edge_ngram^20',
                    'ean^60',
                    'ean.edge_ngram^30',
                    'short_description^5',
                    'description^5',
                ],
            ],
        ];

        return $this;
    }

    /**
     * @param int $page
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        $query = [
            'index' => $this->indexName,
            'type' => '_doc',
            'body' => [
                'from' => $this->countFrom($this->page, $this->limit),
                'size' => $this->limit,
                'sort' => $this->sorting,
                'query' => [
                    'bool' => [
                        'must' => $this->match,
                        'filter' => $this->filters,
                    ],
                ],
            ],
        ];

        return $query;
    }

    /**
     * @return array
     */
    protected function matchAll(): array
    {
        return [
            'match_all' => new \stdClass(),
        ];
    }

    /**
     * @param int $page
     * @param int $limit
     * @return int
     */
    protected function countFrom(int $page, int $limit): int
    {
        return ($page - 1) * $limit;
    }
}
