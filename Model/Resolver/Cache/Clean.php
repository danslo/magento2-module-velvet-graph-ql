<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Cache;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Clean implements ResolverInterface, AdminAuthorizationInterface
{
    private CacheManager $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $cacheId = $args['cache_id'];
        if ($cacheId === null) {
            throw new GraphQlInputException(__('Cache ID must be specified.'));
        }
        $this->cacheManager->clean([$cacheId]);
        return true;
    }
}
