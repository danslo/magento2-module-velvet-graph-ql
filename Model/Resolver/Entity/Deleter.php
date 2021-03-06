<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Entity;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Danslo\VelvetGraphQl\Model\FactoryWrapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Deleter implements ResolverInterface, AdminAuthorizationInterface
{
    private $entityFactory;
    private AbstractResource $resourceModel;
    private string $aclResource;
    private Registry $registry;

    public function __construct(
        FactoryWrapper $factoryWrapper,
        AbstractResource $resourceModel,
        Registry $registry,
        string $aclResource
    ) {
        $this->entityFactory = $factoryWrapper->getFactory();
        $this->resourceModel = $resourceModel;
        $this->aclResource = $aclResource;
        $this->registry = $registry;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $idFieldName = $this->resourceModel->getIdFieldName();
        $entity = $this->entityFactory->create();

        if (!isset($args[$idFieldName])) {
            throw new GraphQlInputException(__(sprintf('%s must be specified.', $idFieldName)));
        }

        $this->resourceModel->load($entity, $args[$idFieldName]);
        if ($entity->getId() === null) {
            throw new NoSuchEntityException();
        }

        try {
            $this->registry->register('isSecureArea', true);
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
