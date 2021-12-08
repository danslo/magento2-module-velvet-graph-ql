<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Dashboard;

use Magento\Directory\Model\Currency;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory;

class Totals implements ResolverInterface
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
        $period = $args['period'] ?? '7d';

        $totals = $this->collectionFactory->create()
            ->addCreateAtPeriodFilter($period)
            ->calculateTotals()
            ->getFirstItem();

        return [
            'revenue' => $this->currency->format($totals->getRevenue(), [], false),
            'tax' => $this->currency->format($totals->getTax(), [], false),
            'shipping' => $this->currency->format($totals->getShipping(), [], false),
            'quantity' => (int) $totals->getQuantity()
        ];
    }
}
