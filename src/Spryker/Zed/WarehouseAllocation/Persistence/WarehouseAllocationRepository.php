<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\WarehouseAllocation\Persistence;

use Generated\Shared\Transfer\QueryCriteriaTransfer;
use Generated\Shared\Transfer\ReservationRequestTransfer;
use Generated\Shared\Transfer\WarehouseAllocationCollectionTransfer;
use Generated\Shared\Transfer\WarehouseAllocationConditionsTransfer;
use Generated\Shared\Transfer\WarehouseAllocationCriteriaTransfer;
use Generated\Shared\Transfer\WhereClauseTransfer;
use Orm\Zed\Sales\Persistence\Map\SpySalesOrderItemTableMap;
use Orm\Zed\WarehouseAllocation\Persistence\Map\SpyWarehouseAllocationTableMap;
use Orm\Zed\WarehouseAllocation\Persistence\SpyWarehouseAllocationQuery;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \Spryker\Zed\WarehouseAllocation\Persistence\WarehouseAllocationPersistenceFactory getFactory()
 */
class WarehouseAllocationRepository extends AbstractRepository implements WarehouseAllocationRepositoryInterface
{
    /**
     * @module Stock
     */
    public function getWarehouseAllocationCollection(
        WarehouseAllocationCriteriaTransfer $warehouseAllocationCriteriaTransfer
    ): WarehouseAllocationCollectionTransfer {
        $warehouseAllocationQuery = $this->getFactory()
            ->createWarehouseAllocationPropelQuery()
            ->joinWithWarehouse();

        if ($warehouseAllocationCriteriaTransfer->getWarehouseAllocationConditions()) {
            $warehouseAllocationQuery = $this->applyFilters(
                $warehouseAllocationCriteriaTransfer->getWarehouseAllocationConditionsOrFail(),
                $warehouseAllocationQuery,
            );
        }

        $warehouseAllocationEntities = $warehouseAllocationQuery->find();

        $warehouseAllocationCollectionTransfer = new WarehouseAllocationCollectionTransfer();
        if ($warehouseAllocationEntities->count() === 0) {
            return $warehouseAllocationCollectionTransfer;
        }

        $warehouseAllocationCollectionTransfer = $this->getFactory()
            ->createWarehouseAllocationMapper()
            ->mapWarehouseAllocationEntitiesToWarehouseAllocationCollectionTransfer(
                $warehouseAllocationEntities,
                $warehouseAllocationCollectionTransfer,
            );

        if ($warehouseAllocationCriteriaTransfer->getWithStoreRelation()) {
            $warehouseAllocationCollectionTransfer = $this->expandWarehouseAllocationsWithStoreRelation(
                $warehouseAllocationCollectionTransfer,
            );
        }

        return $warehouseAllocationCollectionTransfer;
    }

    /**
     * @module Sales
     */
    public function expandOmsReservationAggregationQueryCriteria(
        QueryCriteriaTransfer $queryCriteriaTransfer,
        ReservationRequestTransfer $reservationRequestTransfer // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): QueryCriteriaTransfer {
        $whereClauseTransfer = (new WhereClauseTransfer())
            ->setClause(sprintf(
                '%s IN (SELECT %s FROM %s)',
                SpySalesOrderItemTableMap::COL_UUID,
                SpyWarehouseAllocationTableMap::COL_SALES_ORDER_ITEM_UUID,
                SpyWarehouseAllocationTableMap::TABLE_NAME,
            ));

        $queryCriteriaTransfer->addWhereClause($whereClauseTransfer);

        return $queryCriteriaTransfer;
    }

    /**
     * @module Stock
     */
    protected function expandWarehouseAllocationsWithStoreRelation(
        WarehouseAllocationCollectionTransfer $warehouseAllocationCollectionTransfer
    ): WarehouseAllocationCollectionTransfer {
        $stockIds = [];
        foreach ($warehouseAllocationCollectionTransfer->getWarehouseAllocations() as $warehouseAllocationTransfer) {
            $idStock = $warehouseAllocationTransfer->getWarehouse()?->getIdStock();
            if ($idStock !== null) {
                $stockIds[$idStock] = $idStock;
            }
        }

        if ($stockIds === []) {
            return $warehouseAllocationCollectionTransfer;
        }

        $stockStoreEntities = $this->getFactory()
            ->createStockStorePropelQuery()
            ->filterByFkStock_In($stockIds)
            ->joinWithStore()
            ->find();

        $storeRelationTransfersIndexedByIdStock = $this->getFactory()
            ->createWarehouseAllocationMapper()
            ->mapStockStoreEntitiesToStoreRelationTransfersIndexedByIdStock($stockStoreEntities);

        foreach ($warehouseAllocationCollectionTransfer->getWarehouseAllocations() as $warehouseAllocationTransfer) {
            $idStock = $warehouseAllocationTransfer->getWarehouse()?->getIdStock();
            if ($idStock === null || !isset($storeRelationTransfersIndexedByIdStock[$idStock])) {
                continue;
            }

            $warehouseAllocationTransfer->getWarehouseOrFail()->setStoreRelation(
                $storeRelationTransfersIndexedByIdStock[$idStock],
            );
        }

        return $warehouseAllocationCollectionTransfer;
    }

    protected function applyFilters(
        WarehouseAllocationConditionsTransfer $warehouseAllocationConditionsTransfer,
        SpyWarehouseAllocationQuery $warehouseAllocationQuery
    ): SpyWarehouseAllocationQuery {
        if ($warehouseAllocationConditionsTransfer->getWarehouseIds()) {
            $warehouseAllocationQuery->filterByFkWarehouse_In($warehouseAllocationConditionsTransfer->getWarehouseIds());
        }

        if ($warehouseAllocationConditionsTransfer->getSalesOrderItemUuids()) {
            $warehouseAllocationQuery->filterBySalesOrderItemUuid_In(
                $warehouseAllocationConditionsTransfer->getSalesOrderItemUuids(),
            );
        }

        return $warehouseAllocationQuery;
    }
}
