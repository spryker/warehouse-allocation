<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\WarehouseAllocation\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\StockTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\WarehouseAllocationCollectionTransfer;
use Generated\Shared\Transfer\WarehouseAllocationTransfer;
use Orm\Zed\Stock\Persistence\SpyStock;
use Orm\Zed\WarehouseAllocation\Persistence\SpyWarehouseAllocation;
use Propel\Runtime\Collection\Collection;

class WarehouseAllocationMapper
{
    public function mapWarehouseAllocationTransferToWarehouseAllocationEntity(
        WarehouseAllocationTransfer $warehouseAllocationTransfer,
        SpyWarehouseAllocation $warehouseAllocationEntity
    ): SpyWarehouseAllocation {
        return $warehouseAllocationEntity
            ->fromArray($warehouseAllocationTransfer->modifiedToArray())
            ->setFkWarehouse($warehouseAllocationTransfer->getWarehouseOrFail()->getIdStockOrFail());
    }

    /**
     * @param \Propel\Runtime\Collection\Collection<array-key, \Orm\Zed\WarehouseAllocation\Persistence\SpyWarehouseAllocation> $warehouseAllocationEntities
     * @param \Generated\Shared\Transfer\WarehouseAllocationCollectionTransfer $warehouseAllocationCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\WarehouseAllocationCollectionTransfer
     */
    public function mapWarehouseAllocationEntitiesToWarehouseAllocationCollectionTransfer(
        Collection $warehouseAllocationEntities,
        WarehouseAllocationCollectionTransfer $warehouseAllocationCollectionTransfer
    ): WarehouseAllocationCollectionTransfer {
        foreach ($warehouseAllocationEntities as $warehouseAllocationEntity) {
            $warehouseAllocationTransfer = $this->mapWarehouseAllocationEntityToWarehouseAllocationTransfer(
                $warehouseAllocationEntity,
                new WarehouseAllocationTransfer(),
            );

            $warehouseAllocationCollectionTransfer->addWarehouseAllocation($warehouseAllocationTransfer);
        }

        return $warehouseAllocationCollectionTransfer;
    }

    public function mapWarehouseAllocationEntityToWarehouseAllocationTransfer(
        SpyWarehouseAllocation $warehouseAllocationEntity,
        WarehouseAllocationTransfer $warehouseAllocationTransfer
    ): WarehouseAllocationTransfer {
         return $warehouseAllocationTransfer
             ->fromArray($warehouseAllocationEntity->toArray(), true)
             ->setWarehouse(
                 $this->mapStockEntityToStockTransfer($warehouseAllocationEntity->getWarehouse(), new StockTransfer()),
             );
    }

    protected function mapStockEntityToStockTransfer(SpyStock $stockEntity, StockTransfer $stockTransfer): StockTransfer
    {
        return $stockTransfer->fromArray($stockEntity->toArray());
    }

    /**
     * @param \Propel\Runtime\Collection\Collection<array-key, \Orm\Zed\Stock\Persistence\SpyStockStore> $stockStoreEntities
     *
     * @return array<int, \Generated\Shared\Transfer\StoreRelationTransfer>
     */
    public function mapStockStoreEntitiesToStoreRelationTransfersIndexedByIdStock(Collection $stockStoreEntities): array
    {
        $storeRelationTransfersIndexedByIdStock = [];
        foreach ($stockStoreEntities as $stockStoreEntity) {
            $idStock = $stockStoreEntity->getFkStock();
            if (!isset($storeRelationTransfersIndexedByIdStock[$idStock])) {
                $storeRelationTransfersIndexedByIdStock[$idStock] = (new StoreRelationTransfer())->setIdEntity($idStock);
            }

            $storeRelationTransfersIndexedByIdStock[$idStock]->addStores(
                (new StoreTransfer())->fromArray($stockStoreEntity->getStore()->toArray(), true),
            );
        }

        return $storeRelationTransfersIndexedByIdStock;
    }
}
