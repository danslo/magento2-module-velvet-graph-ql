<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Order;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;

class Order implements ResolverInterface, AdminAuthorizationInterface
{
    private OrderRepositoryInterface $orderRepository;
    private OrderFormatter $orderFormatter;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderFormatter $orderFormatter
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderFormatter = $orderFormatter;
    }

    private function getOrderActionsAvailability(SalesOrder $order): array
    {
        return [
            'can_ship'       => $order->canShip(),
            'can_cancel'     => $order->canCancel(),
            'can_invoice'    => $order->canInvoice(),
            'can_hold'       => $order->canHold(),
            'can_unhold'     => $order->canUnhold(),
            'can_creditmemo' => $order->canCreditmemo()
        ];
    }

    private function getOrderCustomerData(SalesOrder $order): array
    {
        return [
            'customer_email'      => $order->getCustomerEmail(),
            'customer_is_guest'   => (bool) $order->getCustomerIsGuest(),
            'customer_id'         => $order->getCustomerId() !== null ? (int) $order->getCustomerId() : null,
            'customer_dob'        => $order->getCustomerDob(),
            'customer_firstname'  => $order->getCustomerFirstname(),
            'customer_lastname'   => $order->getCustomerLastname(),
            'customer_middlename' => $order->getCustomerMiddlename(),
            'customer_prefix'     => $order->getCustomerPrefix(),
            'customer_suffix'     => $order->getCustomerSuffix(),
            'customer_gender'     => $order->getCustomerGender()
        ];
    }

    private function getOrderStatusHistory(SalesOrder $order): array
    {
        $statusHistory = [];

        /** @var \Magento\Sales\Model\Order\Status $status */
        foreach ($order->getStatusHistoryCollection() as $status) {
            $statusHistory[] = [
                'created_at' => $status->getCreatedAt(),
                'status' => $status->getStatus(),
                'comment' => $status->getComment(),
                'customer_notified' => (bool) $status->getIsCustomerNotified()
            ];
        }

        return ['status_history' => $statusHistory];
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $orderId = $args['order_id'] ?? null;
        if ($orderId === null) {
            throw new GraphQlInputException(__('Required parameter "order_id" is missing'));
        }

        // At this point we have passed admin authorization.
        // Bypass core customer validation.
        $context->getExtensionAttributes()->setIsCustomer(true);

        /** @var SalesOrder $order */
        $order = $this->orderRepository->get($orderId);

        return array_merge(
            $this->orderFormatter->format($order),
            $this->getOrderActionsAvailability($order),
            $this->getOrderCustomerData($order),
            $this->getOrderStatusHistory($order)
        );
    }
}
