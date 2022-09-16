<?php
declare(strict_types=1);

namespace Emergento\PonyUShipAutomatically\Observer;

use Emergento\PonyUShipment\Model\Service\IsPonyUOrder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Psr\Log\LoggerInterface;

class ShipOrderObserver implements ObserverInterface
{
    private ShipOrderInterface $shipOrder;
    private LoggerInterface $logger;
    private IsPonyUOrder $isPonyUOrder;

    public function __construct(
        ShipOrderInterface $shipOrder,
        LoggerInterface $logger,
        IsPonyUOrder $isPonyUOrder
    ) {
        $this->shipOrder = $shipOrder;
        $this->logger = $logger;
        $this->isPonyUOrder = $isPonyUOrder;
    }

    public function execute(Observer $observer):void
    {
        /* @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();

        if (!$this->isPonyUOrder->execute($order)) {
            return;
        }

        if (!$order->canShip()) {
            return;
        }

        try {
            $this->shipOrder->execute($order->getEntityId());
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            return;
        }
    }
}
