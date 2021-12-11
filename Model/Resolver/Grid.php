<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Danslo\VelvetGraphQl\Api\CollectionProcessorInterface;
use Danslo\VelvetGraphQl\Api\ItemTransformerInterface;
use GraphQL\Language\AST\FieldNode;
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
    private string $aclResource;
    private ?ItemTransformerInterface $itemTransformer;
    private ?CollectionProcessorInterface $collectionProcessor;

    public function __construct(
        ObjectManagerInterface $objectManager,
        string $collectionFactoryType,
        string $defaultOrderField,
        string $schemaType,
        string $aclResource,
        ?ItemTransformerInterface $itemTransformer = null,
        ?CollectionProcessorInterface $collectionProcessor = null,
        int $defaultPageSize = 20
    ) {
        // can't use generated factories with virtual types
        // see https://github.com/magento/magento2/issues/6896
        $this->collectionFactory = $objectManager->create($collectionFactoryType);

        $this->defaultPageSize = $defaultPageSize;
        $this->defaultOrderField = $defaultOrderField;
        $this->schemaType = $schemaType;
        $this->itemTransformer = $itemTransformer;
        $this->aclResource = $aclResource;
        $this->collectionProcessor = $collectionProcessor;
    }

    private function getItemsFieldNode(ResolveInfo $info): ?FieldNode
    {
        foreach ($info->fieldNodes[0]->selectionSet->selections as $selection) {
            if ($selection instanceof FieldNode && $selection->name->value === 'items') {
                return $selection;
            }
        }
        return null;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $collection = $this->collectionFactory->create()
            ->setCurPage($args['input']['page_number'] ?? 0)
            ->setPageSize($args['input']['page_size'] ?? $this->defaultPageSize)
            ->addOrder($this->defaultOrderField);

        if ($this->collectionProcessor !== null) {
            $fieldNode = $this->getItemsFieldNode($info);
            if ($fieldNode !== null) {
                $this->collectionProcessor->process($fieldNode, $info, $collection);
            }
        }

        $items = [];
        foreach ($collection as $item) {
            $data = array_merge($item->getData(), ['schema_type' => $this->schemaType]);
            if ($this->itemTransformer !== null) {
                $data = $this->itemTransformer->transform($item, $data);
            }
            $items[] = $data;
        }

        return [
            'items' => $items,
            'last_page_number' => $collection->getLastPageNumber(),
            'total_items' => $collection->getSize()
        ];
    }

    public function getResource(): string
    {
        return $this->aclResource;
    }
}
