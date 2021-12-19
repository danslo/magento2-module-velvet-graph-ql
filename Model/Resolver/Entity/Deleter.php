<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Entity;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\ObjectManagerInterface;

class Deleter implements ResolverInterface, AdminAuthorizationInterface
{
    private $entityFactory;
    private AbstractDb $resourceModel;
    private string $aclResource;

    public function __construct(
        ObjectManagerInterface $objectManager,
        $entityFactory,
        AbstractDb $resourceModel,
        string $aclResource
    ) {
        // can't use generated factories with virtual types
        // see https://github.com/magento/magento2/issues/6896
        $this->entityFactory = $objectManager->create($entityFactory);

        $this->resourceModel = $resourceModel;
        $this->aclResource = $aclResource;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $idFieldName = $this->resourceModel->getIdFieldName();
        $entity = $this->entityFactory->create();

        if (!isset($args['input'][$idFieldName])) {
            throw new GraphQlInputException(__(sprintf('%s must be specified.', $idFieldName)));
        }

        $this->resourceModel->load($entity, $args['input'][$idFieldName]);
        if ($entity->getId() === null) {
            throw new NoSuchEntityException();
        }

        try {
            $this->resourceModel->delete($entity);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getResource(): string
    {
        return $this->aclResource;
    }
}