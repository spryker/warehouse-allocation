<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\WarehouseAllocation\Business\Mapper;

use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\WarehouseAllocationCollectionTransfer;
use Generated\Shared\Transfer\WarehouseAllocationTransfer;

class WarehouseAllocationOrderMapper implements WarehouseAllocationOrderMapperInterface
{
    public function mapOrderTransferToWarehouseAllocationCollectionTransfer(
        OrderTransfer $orderTransfer,
        WarehouseAllocationCollectionTransfer $warehouseAllocationCollectionTransfer
    ): WarehouseAllocationCollectionTransfer {
        foreach ($orderTransfer->getItems() as $itemTransfer) {
            if (!$this->isWarehouseDefined($itemTransfer)) {
                continue;
            }

            $warehouseAllocationCollectionTransfer->addWarehouseAllocation(
                (new WarehouseAllocationTransfer())
                    ->fromArray($itemTransfer->toArray(), true)
                    ->setSalesOrderItemUuid($itemTransfer->getUuid()),
            );
        }

        return $warehouseAllocationCollectionTransfer;
    }

    protected function isWarehouseDefined(ItemTransfer $itemTransfer): bool
    {
        return $itemTransfer->getWarehouse() && $itemTransfer->getWarehouseOrFail()->getIdStock();
    }
}
