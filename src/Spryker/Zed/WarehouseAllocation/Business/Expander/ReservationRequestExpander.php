<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\WarehouseAllocation\Business\Expander;

use Generated\Shared\Transfer\ReservationRequestTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Generated\Shared\Transfer\WarehouseAllocationConditionsTransfer;
use Generated\Shared\Transfer\WarehouseAllocationCriteriaTransfer;
use Spryker\Zed\WarehouseAllocation\Persistence\WarehouseAllocationRepositoryInterface;

class ReservationRequestExpander implements ReservationRequestExpanderInterface
{
    public function __construct(protected WarehouseAllocationRepositoryInterface $warehouseAllocationRepository)
    {
    }

    public function expandWithStores(ReservationRequestTransfer $reservationRequestTransfer): ReservationRequestTransfer
    {
        $salesOrderItemUuid = $reservationRequestTransfer->getUuid();
        if (!$salesOrderItemUuid) {
            return $reservationRequestTransfer;
        }

        $warehouseAllocationCriteriaTransfer = (new WarehouseAllocationCriteriaTransfer())
            ->setWarehouseAllocationConditions(
                (new WarehouseAllocationConditionsTransfer())->addSalesOrderItemUuid($salesOrderItemUuid),
            )
            ->setWithStoreRelation(true);

        $warehouseAllocationCollectionTransfer = $this->warehouseAllocationRepository
            ->getWarehouseAllocationCollection($warehouseAllocationCriteriaTransfer);

        $existingStoreTransfers = $this->extractStores($reservationRequestTransfer);
        foreach ($warehouseAllocationCollectionTransfer->getWarehouseAllocations() as $warehouseAllocationTransfer) {
            $storeRelationTransfer = $warehouseAllocationTransfer->getWarehouse()?->getStoreRelation();
            if ($storeRelationTransfer === null) {
                continue;
            }

            $this->addUniqueStores(
                $reservationRequestTransfer,
                $storeRelationTransfer,
                $existingStoreTransfers,
            );
        }

        return $reservationRequestTransfer;
    }

    /**
     * @return array<string, \Generated\Shared\Transfer\StoreTransfer>
     */
    protected function extractStores(ReservationRequestTransfer $reservationRequestTransfer): array
    {
        $existingStoreNames = [];
        foreach ($reservationRequestTransfer->getStores() as $existingStoreTransfer) {
            $existingStoreName = $existingStoreTransfer->getName();
            if ($existingStoreName === null) {
                continue;
            }
            $existingStoreNames[$existingStoreName] = $existingStoreTransfer;
        }

        return $existingStoreNames;
    }

    /**
     * @param array<string, \Generated\Shared\Transfer\StoreTransfer> $existingStoreNames
     *
     * @return array<string, \Generated\Shared\Transfer\StoreTransfer>
     */
    protected function addUniqueStores(
        ReservationRequestTransfer $reservationRequestTransfer,
        StoreRelationTransfer $storeRelationTransfer,
        array $existingStoreNames
    ): array {
        foreach ($storeRelationTransfer->getStores() as $storeTransfer) {
            $storeName = $storeTransfer->getNameOrFail();
            if (isset($existingStoreNames[$storeName])) {
                continue;
            }

            $existingStoreNames[$storeName] = $storeTransfer;
        }

        if (!isset($existingStoreNames[$reservationRequestTransfer->getStoreOrFail()->getNameOrFail()])) {
            $existingStoreNames = [];
        }

        foreach ($existingStoreNames as $storeName => $storeTransfer) {
            $reservationRequestTransfer->addStore($storeTransfer);
        }

        return $existingStoreNames;
    }
}
