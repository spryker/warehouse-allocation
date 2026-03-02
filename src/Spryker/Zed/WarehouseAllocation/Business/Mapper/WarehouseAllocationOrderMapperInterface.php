<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\WarehouseAllocation\Business\Mapper;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\WarehouseAllocationCollectionTransfer;

interface WarehouseAllocationOrderMapperInterface
{
    public function mapOrderTransferToWarehouseAllocationCollectionTransfer(
        OrderTransfer $orderTransfer,
        WarehouseAllocationCollectionTransfer $warehouseAllocationCollectionTransfer
    ): WarehouseAllocationCollectionTransfer;
}
