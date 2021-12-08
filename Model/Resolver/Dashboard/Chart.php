<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Dashboard;

use Magento\Backend\Model\Dashboard\Chart as DashboardChart;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Chart implements ResolverInterface
{
    private DashboardChart $dashboardChart;
    private string $chartParam;
    private string $label;

    public function __construct(DashboardChart $dashboardChart, string $chartParam, string $label)
    {
        $this->dashboardChart = $dashboardChart;
        $this->chartParam = $chartParam;
        $this->label = $label;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $period = $args['period'] ?? '7d';
        return [
            'label'  => $this->label,
            'points' => $this->dashboardChart->getByPeriod($period, $this->chartParam),
            'period' => $period
        ];
    }
}
