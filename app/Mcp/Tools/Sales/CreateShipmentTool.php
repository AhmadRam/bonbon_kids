<?php

namespace App\Mcp\Tools\Sales;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Inventory\Repositories\InventorySourceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\ShipmentRepository;

#[Name('create_shipment')]
#[Description('Create a shipment for an order with optional tracking number and carrier name.')]
class CreateShipmentTool extends BaseAdminTool
{
    public function handle(
        Request $request,
        OrderRepository $orderRepository,
        ShipmentRepository $shipmentRepository,
        InventorySourceRepository $inventorySourceRepository
    ): Response {
        if ($authError = $this->checkPermission('sales.shipments.create')) {
            return $authError;
        }

        $orderId = $request->get('order_id');
        $incrementId = $request->get('increment_id');
        $carrierTitle = $request->get('carrier_title', 'Standard Shipping');
        $trackNumber = $request->get('tracking_number', '');

        if (! $orderId && ! $incrementId) {
            return $this->errorResponse('Either order_id or increment_id must be provided.');
        }

        $order = $orderId ? $orderRepository->find($orderId) : $orderRepository->findOneByField('increment_id', $incrementId);

        if (! $order) {
            return $this->errorResponse('Order not found.');
        }

        if (! $order->canShip()) {
            return $this->errorResponse("Order #{$order->increment_id} cannot be shipped (already shipped, canceled, or completed).");
        }

        $source = $inventorySourceRepository->first();
        $sourceId = $source ? $source->id : 1;

        $items = [];
        foreach ($order->items as $item) {
            $qtyToShip = $item->qty_to_ship;
            if ($qtyToShip > 0) {
                $items[$item->id] = [
                    $sourceId => $qtyToShip,
                ];
            }
        }

        if (empty($items)) {
            return $this->errorResponse('No remaining items found to ship for this order.');
        }

        $shipment = $shipmentRepository->create([
            'order_id' => $order->id,
            'shipment' => [
                'source' => $sourceId,
                'carrier_title' => $carrierTitle,
                'track_number' => $trackNumber,
                'items' => $items,
            ],
        ]);

        return $this->successResponse("Shipment #{$shipment->increment_id} created successfully for order #{$order->increment_id}.", [
            'shipment_id' => $shipment->id,
            'shipment_increment_id' => $shipment->increment_id,
            'order_id' => $order->id,
            'order_increment_id' => $order->increment_id,
            'carrier_title' => $carrierTitle,
            'tracking_number' => $trackNumber,
            'total_qty' => (int) $shipment->total_qty,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'order_id' => $schema->integer()->description('The order database ID.'),
            'increment_id' => $schema->string()->description('The order public increment ID (e.g. 1).'),
            'carrier_title' => $schema->string()->description('Carrier or delivery service name (e.g. DHL, Aramex, FedEx).'),
            'tracking_number' => $schema->string()->description('Waybill or shipment tracking number.'),
        ];
    }
}
