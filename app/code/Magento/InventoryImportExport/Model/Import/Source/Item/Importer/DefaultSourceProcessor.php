<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Import\Source\Item\Importer;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Inventory\Model\SourceItemFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;

class DefaultSourceProcessor
{
    /**
     * Source Item Interface Factory
     *
     * @var SourceItemFactory $sourceItemFactory
     */
    private $sourceItemFactory;

    /**
     * Default Source Provider
     *
     * @var DefaultSourceProviderInterface $defaultSourceProvider
     */
    private $defaultSourceProvider;

    /**
     * StockItemImporter constructor
     *
     * @param SourceItemFactory $sourceItemFactory
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     */
    public function __construct(
        SourceItemFactory $sourceItemFactory,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->sourceItemFactory = $sourceItemFactory;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * Return Source Item Interface for Default Source Item from given data
     *
     * @param array $data
     * @return SourceItemInterface
     */
    public function execute(array $data)
    {
        $inStock = (isset($data['is_in_stock'])) ? $data['is_in_stock'] : 0;
        $sourceItem = $this->sourceItemFactory->create();
        $sourceItem->setSku($data[Product::COL_SKU]);
        $sourceItem->setSourceId($this->defaultSourceProvider->getId());
        $sourceItem->setQuantity($this->getQty($data['qty']));
        $sourceItem->setStatus($inStock);
        return $sourceItem;
    }

    /**
     * Return qty from value passed
     *
     * @param $qty
     * @return int|string
     */
    private function getQty($qty)
    {
        // If not numeric $qty must be "default={{value}}"
        if (!is_numeric($qty)) {
            // Explode at the "="
            $parts = explode('=', $qty);
            // Return the number after the "="
            return (int)$parts[1];
        }
        return $qty;
    }
}
