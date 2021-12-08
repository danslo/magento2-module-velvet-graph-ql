<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Dashboard;

use Magento\Directory\Model\Currency;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class Sales implements ResolverInterface
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
        $sales = $this->orderCollectionFactory->create()
            ->calculateSales()
            ->getFirstItem();

        return [
            'lifetime_sales' => $this->currency->format($sales->getLifetime(), [], false),
            'average_order' => $this->currency->format($sales->getAverage(), [], false)
        ];
    }
}
