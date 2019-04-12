<?php

namespace Tests\ShopBundle\Functional\Model\Product\Search;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\ShopBundle\DataFixtures\Demo\PricingGroupDataFixture;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class FilterQueryTest extends TransactionFunctionalTestCase
{
    private const ELASTICSEARCH_INDEX = 'product1';

    public function testBrand(): void
    {
        $filter = $this->createFilter();
        $filter->filterByBrands([1]);

        $this->assertIdWithFilter($filter, [5]);
    }

    public function testFlag(): void
    {
        $filter = $this->createFilter();
        $filter->filterByFlags([3]);

        $this->assertIdWithFilter($filter, [1, 5, 50, 16, 33, 39, 70, 40, 45]);
    }

    public function testFlagBrand(): void
    {
        $filter = $this->createFilter();
        $filter->filterByBrands([12]);
        $filter->filterByFlags([1]);

        $this->assertIdWithFilter($filter, [19, 17]);
    }

    public function testMultiFilter(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReference(PricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_1);

        $filter = $this->createFilter();
        $filter->filterOnlyInStock()
            ->filterByCategory([9])
            ->filterByFlags([1])
            ->filterByPrices($pricingGroup, null, Money::create(20));

        $this->assertIdWithFilter($filter, [50]);
    }

    public function testParameters(): void
    {
        $filter = $this->createFilter();

        $parameters = [50 => [109, 115], 49 => [105, 121], 10 => [107]];

        $filter->filterByParameters($parameters);

        $this->assertIdWithFilter($filter, [25, 28]);
    }

    public function testOrdering(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReference(PricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_1);

        $filter = $this->createFilter();
        $filter->filterByCategory([9]);

        $this->assertIdWithFilter($filter, [72, 25, 27, 29, 28, 26, 50, 33, 39, 40], 'top');

        $filter->applyOrdering(ProductListOrderingConfig::ORDER_BY_NAME_ASC, $pricingGroup);
        $this->assertIdWithFilter($filter, [72, 25, 27, 29, 28, 26, 50, 33, 39, 40], 'name asc');

        $filter->applyOrdering(ProductListOrderingConfig::ORDER_BY_NAME_DESC, $pricingGroup);
        $this->assertIdWithFilter($filter, [40, 39, 33, 50, 26, 28, 29, 27, 25, 72], 'name desc');

        $filter->applyOrdering(ProductListOrderingConfig::ORDER_BY_PRICE_ASC, $pricingGroup);
        $this->assertIdWithFilter($filter, [40, 33, 50, 39, 29, 25, 26, 27, 28, 72], 'price asc');

        $filter->applyOrdering(ProductListOrderingConfig::ORDER_BY_PRICE_DESC, $pricingGroup);
        $this->assertIdWithFilter($filter, [72, 28, 27, 26, 25, 29, 39, 50, 33, 40], 'price desc');
    }

    public function testMatchQuery(): void
    {
        $filter = $this->createFilter();

        $filter->search('kitty');
        $this->assertIdWithFilter($filter, [1, 102, 101]);

        $filter->search('mg3550');
        $this->assertIdWithFilter($filter, [9, 144, 10, 145]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $filterQuery
     * @param int[] $ids
     * @param string $message
     */
    protected function assertIdWithFilter(FilterQuery $filterQuery, array $ids, string $message = ''): void
    {
        /** @var \Elasticsearch\Client $es */
        $es = $this->getContainer()->get(Client::class);

        $params = $filterQuery->getQuery();

        $params['_source'] = false;

        $result = $es->search($params);
        $this->assertSame($ids, $this->extractIds($result), $message);
    }

    /**
     * @param array $result
     * @return int[]
     */
    protected function extractIds(array $result): array
    {
        $hits = $result['hits']['hits'];

        return array_map(static function ($element) {
            return (int)$element['_id'];
        }, $hits);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    protected function createFilter(): FilterQuery
    {
        $filter = new FilterQuery(self::ELASTICSEARCH_INDEX);

        $filter->filterOnlySellable();

        return $filter;
    }
}
