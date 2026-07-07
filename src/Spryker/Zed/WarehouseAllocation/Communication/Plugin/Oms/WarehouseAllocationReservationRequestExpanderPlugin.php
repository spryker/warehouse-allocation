<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\WarehouseAllocation\Communication\Plugin\Oms;

use Generated\Shared\Transfer\ReservationRequestTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\OmsExtension\Dependency\Plugin\ReservationRequestExpanderPluginInterface;

/**
 * @method \Spryker\Zed\WarehouseAllocation\Business\WarehouseAllocationFacadeInterface getFacade()
 * @method \Spryker\Zed\WarehouseAllocation\Business\WarehouseAllocationBusinessFactory getBusinessFactory()
 * @method \Spryker\Zed\WarehouseAllocation\WarehouseAllocationConfig getConfig()
 */
class WarehouseAllocationReservationRequestExpanderPlugin extends AbstractPlugin implements ReservationRequestExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - For each warehouse allocation matching `ReservationRequestTransfer.uuid`, appends the
     *   resolved store to `ReservationRequestTransfer.stores`, deduplicated by store name.
     * - No-op when `ReservationRequestTransfer.uuid` is not set.
     * - Pre-existing entries in `ReservationRequestTransfer.stores` are kept.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ReservationRequestTransfer $reservationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\ReservationRequestTransfer
     */
    public function expand(ReservationRequestTransfer $reservationRequestTransfer): ReservationRequestTransfer
    {
        return $this->getBusinessFactory()->createReservationRequestExpander()->expandWithStores($reservationRequestTransfer);
    }

    public function isApplicable(ReservationRequestTransfer $reservationRequestTransfer): bool
    {
        return (bool)$reservationRequestTransfer->getUuid();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 300;
    }
}
