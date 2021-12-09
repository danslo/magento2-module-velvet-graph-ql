<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Entity;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Danslo\VelvetGraphQl\Api\EntityTransformerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\ObjectManagerInterface;

class Loader implements ResolverInterface, AdminAuthorizationInterface
{
    private $entityFactory;
    private AbstractDb $resourceModel;
    private ?ItemTransformerInterface $itemTransformer;
    private string $aclResource;

    public function __construct(
        ObjectManagerInterface $objectManager,
        $entityFactory,
        AbstractDb $resourceModel,
        string $aclResource,
        ?ItemTransformerInterface $itemTransformer = null
    ) {
        // can't use generated factories with virtual types
        // see https://github.com/magento/magento2/issues/6896
        $this->entityFactory = $objectManager->create($entityFactory);

        $this->resourceModel = $resourceModel;
        $this->itemTransformer = $itemTransformer;
        $this->aclResource = $aclResource;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $entityId = $args[$this->resourceModel->getIdFieldName()] ?? null;
        if ($entityId === null) {
            throw new GraphQlInputException(__('Missing entity ID.'));
        }

        $entity = $this->entityFactory->create();
        $this->resourceModel->load($entity, $entityId);
        if ($entity->getId() === null) {
            throw new NoSuchEntityException();
        }

        $data = $entity->getData();
        if ($this->itemTransformer !== null) {
            return $this->itemTransformer->transform($data);
        }
        return $data;
    }

    public function getResource(): string
    {
        return $this->aclResource;
    }
}
