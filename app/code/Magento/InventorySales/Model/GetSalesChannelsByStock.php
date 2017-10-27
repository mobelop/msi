<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventorySales\Model\ResourceModel\SalesChannelsProvider;

class GetSalesChannelsByStock implements GetSalesChannelsByStockInterface
{
    /**
     * @var SalesChannelsProvider
     */
    private $salesChannelsProvider;

    /**
     * @var SalesChannelFactory
     */
    private $salesChannelFactory;

    /**
     * GetSalesChannelsByStock constructor.
     *
     * @param SalesChannelsProvider $salesChannelsProvider
     * @param SalesChannelFactory $salesChannelFactory
     */
    public function __construct(
        SalesChannelsProvider $salesChannelsProvider,
        SalesChannelFactory $salesChannelFactory)
    {
        $this->salesChannelsProvider = $salesChannelsProvider;
        $this->salesChannelFactory = $salesChannelFactory;
    }

    /**
     * Get linked sales channels data for given stockId.
     *
     * @param int $stockId
     * @return SalesChannel[]
     */
    public function get(int $stockId) : array
    {
        $salesChannelItems = $this->salesChannelsProvider->resolve($stockId);
        $linkedSalesChannels = array();
        foreach ($salesChannelItems as $channelItem)
        {
            $salesChannel = $this->salesChannelFactory->create();
            $salesChannel->setType($channelItem['type']);
            $salesChannel->setCode($channelItem['code']);
            $linkedSalesChannels[] = $salesChannel;
        }
        return $linkedSalesChannels;
    }
}