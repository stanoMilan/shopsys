<?php

namespace Tests\ShopBundle\Functional\Model\Product\Search;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportRepository;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class ProductSearchExportRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportRepository
     */
    private $repository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this->getContainer()->get(ProductSearchExportRepository::class);
        $this->domain = $this->getContainer()->get(Domain::class);
    }

    public function testProductDataHaveExpectedStructure(): void
    {
        $data = $this->repository->getProductsData($this->domain->getId(), $this->domain->getLocale(), 0, 10);
        $this->assertCount(10, $data);

        $structure = array_keys(reset($data));
        sort($structure);

        $expectedStructure = [
            'id',
            'catnum',
            'partno',
            'ean',
            'name',
            'description',
            'shortDescription',
            'brand',
            'flags',
            'categories',
            'in_stock',
            'prices',
            'parameters',
            'ordering_priority',
            'calculated_selling_denied',
        ];
        sort($expectedStructure);

        $this->assertSame($expectedStructure, $structure);
    }
}
