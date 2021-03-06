<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Inventory\Model\ResourceModel\StockSourceLink\SaveMultiple;
use Magento\InventoryApi\Api\AssignSourcesToStockInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class AssignSourcesToStock implements AssignSourcesToStockInterface
{
    /**
     * @var SaveMultiple
     */
    private $saveMultiple;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SaveMultiple $saveMultiple
     * @param LoggerInterface $logger
     */
    public function __construct(
        SaveMultiple $saveMultiple,
        LoggerInterface $logger
    ) {
        $this->saveMultiple = $saveMultiple;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $sourceIds, int $stockId)
    {
        if (empty($sourceIds)) {
            throw new InputException(__('Input data is invalid'));
        }
        try {
            $this->saveMultiple->execute($sourceIds, $stockId);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not assign Sources to Stock'), $e);
        }
    }
}
