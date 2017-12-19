<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Ui\Component\Listing\Column;

use Magento\InventorySales\Setup\Operation\CreateSalesChannelTable;
use Magento\InventorySales\Ui\SalesChannelNameResolverInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Add grid column for sales channels. Prepare data
 */
class SalesChannels extends Column
{
    /**
     * @var SalesChannelNameResolverInterface
     */
    private $salesChannelNameResolver;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SalesChannelNameResolverInterface $salesChannelNameResolver
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SalesChannelNameResolverInterface $salesChannelNameResolver,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->salesChannelNameResolver = $salesChannelNameResolver;
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($dataSource['data']['totalRecords'] > 0) {
            foreach ($dataSource['data']['items'] as &$row) {
                $row['sales_channels'] = isset($row['sales_channels'])
                    ? $this->prepareSalesChannelData($row['sales_channels']) : [];
            }
        }
        unset($row);

        return $dataSource;
    }

    /**
     * Prepare sales value
     *
     * @param array $salesChannelData
     * @return array
     */
    private function prepareSalesChannelData(array $salesChannelData): array
    {
        $preparedChannelData = [];
        foreach ($salesChannelData as $type => $salesChannel) {
            foreach ($salesChannel as $code) {
                $preparedChannelData[$type][] = [
                    'name' => $this->salesChannelNameResolver->resolve($type, $code),
                    'code' => $code,
                ];
            }
        }
        return $preparedChannelData;
    }

    /**
     * @inheritdoc
     */
    protected function applySorting()
    {
        $sorting = $this->getContext()->getRequestParam('sorting');
        $isSortable = $this->getData('config/sortable');
        if ($isSortable !== false
            && !empty($sorting['field'])
            && !empty($sorting['direction'])
            && $sorting['field'] === $this->getName()
        ) {
            $this->getContext()->getDataProvider()->addOrder(
                CreateSalesChannelTable::STOCK_ID,
                strtoupper($sorting['direction'])
            );
        }
    }
}
