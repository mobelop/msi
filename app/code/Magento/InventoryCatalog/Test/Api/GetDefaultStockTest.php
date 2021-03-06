<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Api;

use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Class GetDefaultStockTest
 */
class GetDefaultStockTest extends WebapiAbstract
{
    /**
     * Test that default Stock is present after installation
     */
    public function testGetDefaultSource()
    {
        $defaultStockId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/inventory/stock/' . $defaultStockId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'inventoryApiStockRepositoryV1',
                'operation' => 'inventoryApiStockRepositoryV1Get',
            ],
        ];
        if (self::ADAPTER_REST == TESTS_WEB_API_ADAPTER) {
            $stock = $this->_webApiCall($serviceInfo);
        } else {
            $stock = $this->_webApiCall($serviceInfo, ['stockId' => $defaultStockId]);
        }
        $this->assertEquals($defaultStockId, $stock[StockInterface::STOCK_ID]);
    }
}
