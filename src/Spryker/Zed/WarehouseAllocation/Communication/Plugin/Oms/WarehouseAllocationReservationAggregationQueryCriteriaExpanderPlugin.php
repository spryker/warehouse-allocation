<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\WarehouseAllocation\Communication\Plugin\Oms;

use Generated\Shared\Transfer\QueryCriteriaTransfer;
use Generated\Shared\Transfer\ReservationRequestTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\OmsExtension\Dependency\Plugin\OmsReservationAggregationQueryCriteriaExpanderPluginInterface;

/**
 * @method \Spryker\Zed\WarehouseAllocation\Persistence\WarehouseAllocationRepositoryInterface getRepository()
 */
class WarehouseAllocationReservationAggregationQueryCriteriaExpanderPlugin extends AbstractPlugin implements OmsReservationAggregationQueryCriteriaExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Scopes the OMS reservation aggregation to sales order items that have a warehouse allocation,
     *   so unallocated siblings of the same SKU are not counted as reserved.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QueryCriteriaTransfer $queryCriteriaTransfer
     * @param \Generated\Shared\Transfer\ReservationRequestTransfer $reservationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\QueryCriteriaTransfer
     */
    public function expand(
        QueryCriteriaTransfer $queryCriteriaTransfer,
        ReservationRequestTransfer $reservationRequestTransfer
    ): QueryCriteriaTransfer {
        return $this->getRepository()->expandOmsReservationAggregationQueryCriteria(
            $queryCriteriaTransfer,
            $reservationRequestTransfer,
        );
    }
}
