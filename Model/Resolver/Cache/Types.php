<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Cache;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Magento\Backend\Model\Cache\ResourceModel\Grid\CollectionFactory as CacheCollectionFactory;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeList;
use Magento\Framework\DataObject;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Types implements ResolverInterface, AdminAuthorizationInterface
{
    private const STATUS_ENABLED     = 'Enabled';
    private const STATUS_DISABLED    = 'Disabled';
    private const STATUS_INVALIDATED = 'Invalidated';

    private CacheCollectionFactory $cacheCollectionFactory;
    private CacheTypeList $cacheTypeList;

    public function __construct(
        CacheCollectionFactory $cacheCollectionFactory,
        CacheTypeList $cacheTypeList
    ) {
        $this->cacheCollectionFactory = $cacheCollectionFactory;
        $this->cacheTypeList = $cacheTypeList;
    }

    public function getStatus(DataObject $cacheType): string
    {
        $invalidedTypes = $this->cacheTypeList->getInvalidated();
        if (isset($invalidedTypes[$cacheType->getId()])) {
            return self::STATUS_INVALIDATED;
        } else {
            if ($cacheType->getStatus()) {
                return self::STATUS_ENABLED;
            } else {
                return self::STATUS_DISABLED;
            }
        }
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $cacheTypes = [];
        foreach ($this->cacheCollectionFactory->create() as $cacheType) {
            $cacheTypes[] = [
                'id' => $cacheType->getId(),
                'cache_type' => $cacheType->getCacheType(),
                'description' => $cacheType->getDescription(),
                'tags' => $cacheType->getTags(),
                'status' => $this->getStatus($cacheType)
            ];
        }
        return $cacheTypes;
    }
}
