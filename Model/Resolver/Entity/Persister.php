<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Entity;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\ObjectManagerInterface;

class Persister implements ResolverInterface, AdminAuthorizationInterface
{
    private $entityFactory;
    private AbstractDb $resourceModel;

    public function __construct(
        ObjectManagerInterface $objectManager,
        $entityFactory,
        AbstractDb $resourceModel
    ) {
        // can't use generated factories with virtual types
        // see https://github.com/magento/magento2/issues/6896
        $this->entityFactory = $objectManager->create($entityFactory);
        $this->resourceModel = $resourceModel;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $idFieldName = $this->resourceModel->getIdFieldName();
        $entity = $this->entityFactory->create();

        if (isset($args['input'][$idFieldName])) {
            $this->resourceModel->load($entity, $args['input'][$idFieldName]);
            if ($entity->getId() === null) {
                throw new NoSuchEntityException();
            }
        }

        $entity->addData($args['input']);
        $this->resourceModel->save($entity);
        return $entity->getData();
    }
}
