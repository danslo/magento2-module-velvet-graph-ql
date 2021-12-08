<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Dashboard;

use Magento\Directory\Model\Currency;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class LastOrders implements ResolverInterface
{
    private OrderCollectionFactory $orderCollectionFactory;
    private Currency $currency;

    public function __construct(OrderCollectionFactory $orderCollectionFactory, Currency $currency)
    {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->currency = $currency;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $lastOrdersCollection = $this->orderCollectionFactory->create()
            ->addItemCountExpr()
            ->joinCustomerName('customer')
            ->orderByCreatedAt()
            ->addRevenueToSelect(true)
            ->setPageSize(5);

        $lastOrders = [];
        foreach ($lastOrdersCollection as $item) {
            $item->getCustomer() ?: $item->setCustomer($item->getBillingAddress()->getName());

            $lastOrders[] = [
                'customer_name' => $item->getCustomer(),
                'num_items' => $item->getItemsCount(),
                'total' => $this->currency->format($item->getRevenue(), [], false)
            ];
        }
        return $lastOrders;
    }
}
