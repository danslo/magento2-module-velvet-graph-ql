<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Indexer;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Indexer\Block\Backend\Grid\Column\Renderer\ScheduleStatus;
use Magento\Indexer\Ui\DataProvider\Indexer\DataCollectionFactory;

class Indexers implements ResolverInterface, AdminAuthorizationInterface
{
    private DataCollectionFactory $dataCollectionFactory;
    private ScheduleStatus $scheduleStatus;

    public function __construct(
        DataCollectionFactory $dataCollectionFactory,
        ScheduleStatus $scheduleStatus
    ) {
        $this->dataCollectionFactory = $dataCollectionFactory;
        $this->scheduleStatus = $scheduleStatus;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $indexers = [];
        foreach ($this->dataCollectionFactory->create() as $indexer) {
            $indexers[] = [
                'title' => $indexer->getTitle(),
                'description' => $indexer->getDescription(),
                'is_scheduled' => $indexer->getIsScheduled(),
                'status' => $indexer->getStatus(),
                'schedule_status' => $this->scheduleStatus->render($indexer),
                'updated' => $indexer->getUpdated()
            ];
        }
        return $indexers;
    }
}
