<?php

namespace Shopsys\FrameworkBundle\Model\Product\Search\Export;

use BadMethodCallException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;

class ProductSearchExportRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository
     */
    protected $parameterRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    protected $productFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Setter injection for ParameterRepository to maintain backward compatibility
     *
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @deprecated Will be replaced with constructor injection in the next major release
     */
    public function setParameterRepository(ParameterRepository $parameterRepository): void
    {
        $this->parameterRepository = $parameterRepository;
    }

    /**
     * Setter injection for ProductFacade to maintain backward compatibility
     *
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @deprecated Will be replaced with constructor injection in the next major release
     */
    public function setProductFacade(ProductFacade $productFacade): void
    {
        $this->productFacade = $productFacade;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param int $startFrom
     * @param int $batchSize
     * @return array
     */
    public function getProductsData(int $domainId, string $locale, int $startFrom, int $batchSize): array
    {
        $queryBuilder = $this->createQueryBuilder($domainId, $locale)
            ->setFirstResult($startFrom)
            ->setMaxResults($batchSize);

        $query = $queryBuilder->getQuery();

        $result = [];
        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        foreach ($query->getResult() as $product) {
            $flagIds = $this->extractFlags($product);
            $categoryIds = $this->extractCategories($domainId, $product);
            $parameters = $this->extractParameters($locale, $product);
            $prices = $this->extractPrices($domainId, $product);

            $result[] = [
                'id' => $product->getId(),
                'catnum' => $product->getCatnum(),
                'partno' => $product->getPartno(),
                'ean' => $product->getEan(),
                'name' => $product->getName($locale),
                'description' => $product->getDescription($domainId),
                'shortDescription' => $product->getShortDescription($domainId),
                'brand' => $product->getBrand() ? $product->getBrand()->getId() : '',
                'flags' => $flagIds,
                'categories' => $categoryIds,
                'in_stock' => $product->getCalculatedAvailability()->getDispatchTime() === 0,
                'prices' => $prices,
                'parameters' => $parameters,
                'ordering_priority' => $product->getOrderingPriority(),
                'calculated_selling_denied' => $product->getCalculatedSellingDenied(),
            ];
        }

        return $result;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createQueryBuilder(int $domainId, string $locale): QueryBuilder
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->where('p.variantType != :variantTypeVariant')
            ->join(ProductVisibility::class, 'prv', Join::WITH, 'prv.product = p.id')
            ->andWhere('prv.domainId = :domainId')
            ->andWhere('prv.visible = TRUE')
            ->join('p.translations', 't')
            ->andWhere('t.locale = :locale')
            ->join('p.domains', 'd')
            ->andWhere('d.domainId = :domainId')
            ->groupBy('p.id')
            ->orderBy('p.id');

        $queryBuilder->setParameter('domainId', $domainId)
            ->setParameter('locale', $locale)
            ->setParameter('variantTypeVariant', Product::VARIANT_TYPE_VARIANT);

        return $queryBuilder;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int[]
     */
    protected function extractFlags(Product $product): array
    {
        $flagIds = [];
        foreach ($product->getFlags() as $flag) {
            $flagIds[] = $flag->getId();
        }

        return $flagIds;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int[]
     */
    protected function extractCategories(int $domainId, Product $product): array
    {
        $categoryIds = [];
        foreach ($product->getCategoriesIndexedByDomainId()[$domainId] as $category) {
            $categoryIds[] = $category->getId();
        }

        return $categoryIds;
    }

    /**
     * @param string $locale
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return array
     */
    protected function extractParameters(string $locale, Product $product): array
    {
        if ($this->parameterRepository === null) {
            throw new BadMethodCallException(\sprintf('Class of type "%s" has to be set with "%s::injectParameterRepository" method in dependency injection container.', ParameterRepository::class, self::class));
        }

        $parameters = [];
        $productParameterValues = $this->parameterRepository->getProductParameterValuesByProductSortedByName($product, $locale);
        foreach ($productParameterValues as $index => $productParameterValue) {
            $parameter = $productParameterValue->getParameter();
            $parameterValue = $productParameterValue->getValue();
            if ($parameter->getName($locale) !== null && $parameterValue->getLocale() === $locale) {
                $parameters[] = [
                    'parameter_id' => $parameter->getId(),
                    'parameter_value_id' => $parameterValue->getId(),
                ];
            }
        }

        return $parameters;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return array
     */
    protected function extractPrices(int $domainId, Product $product): array
    {
        if ($this->parameterRepository === null) {
            throw new BadMethodCallException(\sprintf('Class of type "%s" has to be set with "%s::injectProductFacade" method in dependency injection container.', ProductFacade::class, self::class));
        }

        $prices = [];
        $productSellingPrices = $this->productFacade->getAllProductSellingPricesIndexedByDomainId($product)[$domainId];
        /** @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductSellingPrice $productSellingPrice */
        foreach ($productSellingPrices as $productSellingPrice) {
            $prices[] = [
                'pricing_group_id' => $productSellingPrice->getPricingGroup()->getId(),
                'amount' => $productSellingPrice->getSellingPrice()->getPriceWithVat()->getAmount(),
            ];
        }

        return $prices;
    }
}
