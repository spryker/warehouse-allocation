<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\WarehouseAllocation\Business\Plugin\Oms;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ReservationRequestTransfer;
use Generated\Shared\Transfer\StockTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\WarehouseAllocationTransfer;
use Spryker\Zed\WarehouseAllocation\Communication\Plugin\Oms\WarehouseAllocationReservationRequestExpanderPlugin;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group WarehouseAllocation
 * @group Business
 * @group Plugin
 * @group Oms
 * @group WarehouseAllocationReservationRequestExpanderPluginTest
 *
 * Add your own group annotations below this line
 */
class WarehouseAllocationReservationRequestExpanderPluginTest extends Unit
{
    /**
     * @var string
     */
    protected const STORE_NAME_DE = 'DE';

    /**
     * @var string
     */
    protected const STORE_NAME_AT = 'AT';

    /**
     * @var string
     */
    protected const ORDER_ITEM_UUID = 'order-item-uuid-001';

    /**
     * @var \SprykerTest\Zed\WarehouseAllocation\WarehouseAllocationBusinessTester
     */
    protected $tester;

    public function testExpandReturnsRequestUnchangedWhenUuidIsNull(): void
    {
        // Arrange
        $reservationRequestTransfer = new ReservationRequestTransfer();

        // Act
        $resultReservationRequestTransfer = $this->createPlugin()->expand($reservationRequestTransfer);

        // Assert
        $this->assertSame($reservationRequestTransfer, $resultReservationRequestTransfer);
        $this->assertCount(0, $resultReservationRequestTransfer->getStores());
    }

    public function testExpandReturnsRequestUnchangedWhenUuidIsEmptyString(): void
    {
        // Arrange
        $reservationRequestTransfer = (new ReservationRequestTransfer())->setUuid('');

        // Act
        $resultReservationRequestTransfer = $this->createPlugin()->expand($reservationRequestTransfer);

        // Assert
        $this->assertCount(0, $resultReservationRequestTransfer->getStores());
    }

    public function testExpandAppendsStoreAssignedToAllocatedWarehouse(): void
    {
        // Arrange
        $storeTransfer = $this->tester->haveStore([StoreTransfer::NAME => static::STORE_NAME_DE]);
        $stockTransfer = $this->tester->haveStock([
            StockTransfer::STORE_RELATION => [StoreRelationTransfer::ID_STORES => [$storeTransfer->getIdStore()]],
        ]);
        $this->tester->haveWarehouseAllocation([
            WarehouseAllocationTransfer::SALES_ORDER_ITEM_UUID => static::ORDER_ITEM_UUID,
            WarehouseAllocationTransfer::WAREHOUSE => [
                StockTransfer::ID_STOCK => $stockTransfer->getIdStock(),
            ],
        ]);

        $reservationRequestTransfer = (new ReservationRequestTransfer())
            ->setUuid(static::ORDER_ITEM_UUID)
            ->setStore($storeTransfer);

        // Act
        $resultReservationRequestTransfer = $this->createPlugin()->expand($reservationRequestTransfer);

        // Assert
        $resultStoreNames = $this->collectStoreNames($resultReservationRequestTransfer);
        $this->assertContains($storeTransfer->getName(), $resultStoreNames);
    }

    public function testExpandAppendsAllStoresWhenWarehouseServesMultipleStores(): void
    {
        // Arrange
        $primaryStoreTransfer = $this->tester->haveStore([StoreTransfer::NAME => static::STORE_NAME_DE]);
        $secondaryStoreTransfer = $this->tester->haveStore([StoreTransfer::NAME => static::STORE_NAME_AT]);

        $stockTransfer = $this->tester->haveStock([
            StockTransfer::STORE_RELATION => [
                StoreRelationTransfer::ID_STORES => [
                    $primaryStoreTransfer->getIdStore(),
                    $secondaryStoreTransfer->getIdStore(),
                ],
            ],
        ]);
        $this->tester->haveWarehouseAllocation([
            WarehouseAllocationTransfer::SALES_ORDER_ITEM_UUID => static::ORDER_ITEM_UUID,
            WarehouseAllocationTransfer::WAREHOUSE => [
                StockTransfer::ID_STOCK => $stockTransfer->getIdStock(),
            ],
        ]);

        $reservationRequestTransfer = (new ReservationRequestTransfer())
            ->setUuid(static::ORDER_ITEM_UUID)
            ->setStore($primaryStoreTransfer);

        // Act
        $resultReservationRequestTransfer = $this->createPlugin()->expand($reservationRequestTransfer);

        // Assert
        $resultStoreNames = $this->collectStoreNames($resultReservationRequestTransfer);
        $this->assertContains($primaryStoreTransfer->getName(), $resultStoreNames);
        $this->assertContains($secondaryStoreTransfer->getName(), $resultStoreNames);
    }

    public function testExpandAddsNoStoresWhenWarehouseDoesNotServeRequestStore(): void
    {
        // Arrange
        $primaryStoreTransfer = $this->tester->haveStore([StoreTransfer::NAME => static::STORE_NAME_DE]);
        $secondaryStoreTransfer = $this->tester->haveStore([StoreTransfer::NAME => static::STORE_NAME_AT]);

        $stockTransfer = $this->tester->haveStock([
            StockTransfer::STORE_RELATION => [StoreRelationTransfer::ID_STORES => [$secondaryStoreTransfer->getIdStore()]],
        ]);
        $this->tester->haveWarehouseAllocation([
            WarehouseAllocationTransfer::SALES_ORDER_ITEM_UUID => static::ORDER_ITEM_UUID,
            WarehouseAllocationTransfer::WAREHOUSE => [
                StockTransfer::ID_STOCK => $stockTransfer->getIdStock(),
            ],
        ]);

        $reservationRequestTransfer = (new ReservationRequestTransfer())
            ->setUuid(static::ORDER_ITEM_UUID)
            ->setStore($primaryStoreTransfer);

        // Act
        $resultReservationRequestTransfer = $this->createPlugin()->expand($reservationRequestTransfer);

        // Assert
        $resultStoreNames = $this->collectStoreNames($resultReservationRequestTransfer);
        $this->assertNotContains($secondaryStoreTransfer->getName(), $resultStoreNames);
        $this->assertNotContains($primaryStoreTransfer->getName(), $resultStoreNames);
    }

    public function testIsApplicableReturnsTrueWhenUuidIsSet(): void
    {
        $reservationRequestTransfer = (new ReservationRequestTransfer())->setUuid('any-uuid');

        $this->assertTrue($this->createPlugin()->isApplicable($reservationRequestTransfer));
    }

    public function testIsApplicableReturnsFalseWhenUuidIsNull(): void
    {
        $this->assertFalse($this->createPlugin()->isApplicable(new ReservationRequestTransfer()));
    }

    public function testIsApplicableReturnsFalseWhenUuidIsEmptyString(): void
    {
        $reservationRequestTransfer = (new ReservationRequestTransfer())->setUuid('');

        $this->assertFalse($this->createPlugin()->isApplicable($reservationRequestTransfer));
    }

    /**
     * @return array<string>
     */
    protected function collectStoreNames(ReservationRequestTransfer $reservationRequestTransfer): array
    {
        $storeNames = [];
        foreach ($reservationRequestTransfer->getStores() as $storeTransfer) {
            $storeNames[] = $storeTransfer->getName();
        }

        return $storeNames;
    }

    protected function createPlugin(): WarehouseAllocationReservationRequestExpanderPlugin
    {
        return new WarehouseAllocationReservationRequestExpanderPlugin();
    }
}
