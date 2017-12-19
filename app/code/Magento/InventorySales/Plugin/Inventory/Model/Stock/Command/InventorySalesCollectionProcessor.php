<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\Inventory\Model\Stock\Command;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\InventorySales\Setup\Operation\CreateSalesChannelTable;
use Magento\Store\Model\Website;

class InventorySalesCollectionProcessor extends CollectionProcessor
{
    /**
     * @inheritdoc
     */
    public function process(SearchCriteriaInterface $searchCriteria, AbstractDb $collection)
    {
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                /*
                 * When we sort by sales_channel - we need to join to store_website table
                 * and sort by website name
                 */
                if ($sortOrder->getField() === 'sales_channels') {
                    $resource = $collection->getResource();
                    $salesChannelTable = $resource->getTable('inventory_stock_sales_channel');
                    $websiteTable = $resource->getTable(WebSite::ENTITY);

                    $collection
                        ->getSelect()
                        ->join(
                            ['sc' => $salesChannelTable],
                            'sc.' . CreateSalesChannelTable::STOCK_ID . ' = main_table.stock_id',
                            []
                        )->joinLeft(
                            ['site' => $websiteTable],
                            'sc.code = site.code',
                            ['sales_channels' => 'name']
                        );

                    break;

                }
            }
        }

        parent::process($searchCriteria, $collection);
    }
}
