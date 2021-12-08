<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Danslo\VelvetGraphQl\Api\EntityTransformerInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\ObjectManagerInterface;

class Grid implements ResolverInterface, AdminAuthorizationInterface
{
    private $collectionFactory;
    private int $defaultPageSize;
    private string $defaultOrderField;
    private string $schemaType;
    private ?EntityTransformerInterface $entityTransfer;

    public function __construct(
        ObjectManagerInterface $objectManager,
        string $collectionFactoryType,
        string $defaultOrderField,
        string $schemaType,
        ?EntityTransformerInterface $entityTransfer = null,
        int $defaultPageSize = 20
    ) {
        // can't use generated factories with virtual types
        // see https://github.com/magento/magento2/issues/6896
        $this->collectionFactory = $objectManager->create($collectionFactoryType);

        $this->defaultPageSize = $defaultPageSize;
        $this->defaultOrderField = $defaultOrderField;
        $this->schemaType = $schemaType;
        $this->entityTransfer = $entityTransfer;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $collection = $this->collectionFactory->create()
            ->setCurPage($args['input']['page_number'] ?? 0)
            ->setPageSize($args['input']['page_size'] ?? $this->defaultPageSize)
            ->addOrder($this->defaultOrderField);

        $items = [];
        foreach ($collection as $item) {
            $item = array_merge($item->getData(), ['schema_type' => $this->schemaType]);
            if ($this->entityTransfer !== null) {
                $item = $this->entityTransfer->transform($item);
            }
            $items[] = $item;
        }

        return [
            'items' => $items,
            'last_page_number' => $collection->getLastPageNumber(),
            'total_items' => $collection->getTotalCount()
        ];
    }
}
