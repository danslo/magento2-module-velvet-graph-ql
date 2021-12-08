<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Dashboard;

use Magento\Directory\Model\Currency;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory;

class CustomersMost implements ResolverInterface
{
    private CollectionFactory $collectionFactory;
    private Currency $currency;

    public function __construct(CollectionFactory $collectionFactory, Currency $currency)
    {
        $this->collectionFactory = $collectionFactory;
        $this->currency = $currency;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $collection = $this->collectionFactory->create()
            ->groupByCustomer()
            ->addOrdersCount()
            ->joinCustomerName()
            ->addSumAvgTotals()
            ->orderByTotalAmount()
            ->setPageSize(5);

        $customers = [];
        foreach ($collection as $customer) {
            $customers[] = [
                'name' => $customer->getName(),
                'orders' => $customer->getOrdersCount() ?? 0,
                'average' =>  $this->currency->format($customer->getOrdersAvgAmount(), [], false),
                'total' => $this->currency->format($customer->getOrdersSumAmount(), [], false)
            ];
        }
        return $customers;
    }
}
