<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\ReadModel\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\ReadModel\Image\ImageView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ListProductsFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    protected $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade
     */
    protected $productCachedAttributesFacade;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ProductFacade $productFacade,
        ProductRepository $productRepository,
        ProductCachedAttributesFacade $productCachedAttributesFacade,
        UrlGeneratorInterface $urlGenerator,
        ImageFacade $imageFacade,
        Domain $domain
    ) {
        $this->productFacade = $productFacade;
        $this->productRepository = $productRepository;
        $this->productCachedAttributesFacade = $productCachedAttributesFacade;
        $this->urlGenerator = $urlGenerator;
        $this->imageFacade = $imageFacade;
        $this->domain = $domain;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return \Shopsys\FrameworkBundle\ReadModel\Product\ListedProductView[]
     * @internal For temporary use only. There will be no such method in the merged code.
     */
    public function getListedProducts(array $products): array
    {
        return array_map([$this, 'getListedProduct'], $products);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\ReadModel\Product\ListedProductView
     * @internal For temporary use only. There will be no such method in the merged code.
     */
    public function getListedProduct(Product $product): ListedProductView
    {
        try {
            $image = $this->imageFacade->getImageByEntity($product, null);
            $imageView = new ImageView(
                $image->getId(),
                $image->getExtension(),
                $image->getEntityName(),
                $image->getType()
            );
        } catch (\Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException $e) {
            $imageView = null;
        }
        $detailUrl = $this->urlGenerator->generate('front_product_detail', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new ListedProductView(
            $product->getId(),
            $product->getName(),
            $imageView,
            $product->getCalculatedAvailability()->getName(),
            $this->productCachedAttributesFacade->getProductSellingPrice($product),
            $product->getShortDescription($this->domain->getId()) ?: '',
            array_map(function (Flag $flag): int {
                return $flag->getId();
            }, $product->getFlags()->toArray()),
            $detailUrl,
            new ProductActionView(
                $product->getId(),
                $product->isSellingDenied(),
                $product->isMainVariant(),
                $detailUrl
            )
        );
    }
}
