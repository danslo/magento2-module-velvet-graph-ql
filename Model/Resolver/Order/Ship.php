<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Order;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\ShipOrderInterface;

class Ship implements ResolverInterface, AdminAuthorizationInterface
{
    private ShipOrderInterface $shipOrder;

    public function __construct(ShipOrderInterface $shipOrder)
    {
        $this->shipOrder = $shipOrder;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $orderId = $args['order_id'] ?? null;
        if ($orderId === null) {
            throw new GraphQlInputException(__('Required parameter "order_id" is missing'));
        }

        return $this->shipOrder->execute($orderId);
    }
}
