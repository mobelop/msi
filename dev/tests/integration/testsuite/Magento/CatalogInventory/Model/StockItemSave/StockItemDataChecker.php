<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\StockItemSave;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use PHPUnit\Framework\Assert;

class StockItemDataChecker
{
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param HydratorInterface $hydrator
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ProductInterfaceFactory $productFactory
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     */
    public function __construct(
        HydratorInterface $hydrator,
        StockItemRepositoryInterface $stockItemRepository,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        ProductRepositoryInterface $productRepository,
        ProductInterfaceFactory $productFactory,
        SourceItemRepositoryInterface $sourceItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->hydrator = $hydrator;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * @param string $sku
     * @param array $expectedData
     */
    public function checkStockItemData($sku, array $expectedData)
    {
        $product = $this->productRepository->get($sku, false, null, true);
        $this->doCheckStockItemData($product, $expectedData);

        /** @var Product $product */
        $productLoadedByModel = $this->productFactory->create();
        $productLoadedByModel->load($product->getId());
        $this->doCheckStockItemData($product, $expectedData);
        $this->checkIntegrityWithInventory($product, $expectedData);
    }

    /**
     * @param Product $product
     * @param array $expectedData
     */
    private function doCheckStockItemData(Product $product, array $expectedData)
    {
        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $stockItem = $this->stockItemRepository->get($stockItem->getItemId());

        $this->assertArrayContains($expectedData, $this->hydrator->extract($stockItem));

        $criteria = $this->stockItemCriteriaFactory->create();
        $result = $this->stockItemRepository->getList($criteria);
        $items = $result->getItems();
        $stockItem = current($items);
        $this->assertArrayContains($expectedData, $this->hydrator->extract($stockItem));

        $expectedQuantityAndStockStatusData = array_intersect_key($expectedData, [
            StockItemInterface::IS_IN_STOCK => 0,
            StockItemInterface::QTY => 0,
        ]);
        Assert::assertNotNull($product->getQuantityAndStockStatus());
        $this->assertArrayContains($expectedQuantityAndStockStatusData, $product->getQuantityAndStockStatus());

        Assert::assertNotNull($product->getData('quantity_and_stock_status'));
        $this->assertArrayContains($expectedQuantityAndStockStatusData, $product->getData('quantity_and_stock_status'));
    }

    /**
     * @param array $expected
     * @param array $actual
     * @return void
     */
    private function assertArrayContains(array $expected, array $actual)
    {
        foreach ($expected as $key => $value) {
            Assert::assertArrayHasKey(
                $key,
                $actual,
                "Expected value for key '{$key}' is missed"
            );
            if (is_array($value)) {
                $this->assertArrayContains($value, $actual[$key]);
            } else {
                Assert::assertEquals(
                    $value,
                    $actual[$key],
                    "Expected value for key '{$key}' doesn't match"
                );
            }
        }
    }

    /**
     * @param Product $product
     * @param array $expectedData
     * @return void
     */
    private function checkIntegrityWithInventory(Product $product, array $expectedData)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SOURCE_ID, $this->defaultSourceProvider->getId())
            ->addFilter(SourceItemInterface::SKU, $product->getSku())
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        Assert::assertCount(1, $sourceItems);

        $sourceItem = reset($sourceItems);
        Assert::assertEquals(
            $expectedData[StockItemInterface::QTY],
            $sourceItem->getQuantity()
        );
        Assert::assertEquals(
            $expectedData[StockItemInterface::IS_IN_STOCK],
            (int)$sourceItem->getStatus()
        );
    }
}
