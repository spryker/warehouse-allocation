<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\WarehouseAllocation\Business\Allocator;

use Generated\Shared\Transfer\OrderTransfer;

interface WarehouseAllocatorInterface
{
    public function allocateWarehouses(OrderTransfer $orderTransfer): OrderTransfer;
}
