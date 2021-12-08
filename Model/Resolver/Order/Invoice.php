<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Order;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\InvoiceOrderInterface;

class Invoice implements ResolverInterface, AdminAuthorizationInterface
{
    private InvoiceOrderInterface $invoiceOrder;

    public function __construct(InvoiceOrderInterface $invoiceOrder)
    {
        $this->invoiceOrder = $invoiceOrder;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $orderId = $args['order_id'] ?? null;
        if ($orderId === null) {
            throw new GraphQlInputException(__('Required parameter "order_id" is missing'));
        }

        return $this->invoiceOrder->execute($orderId, true);
    }
}
