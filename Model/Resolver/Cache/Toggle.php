<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Cache;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Toggle implements ResolverInterface, AdminAuthorizationInterface
{
    private CacheManager $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $enable = $args['enable'] ?? null;
        if ($enable === null) {
            throw new GraphQlInputException(__('Enable value must be specified.'));
        }

        $cacheId = $args['cache_id'] ?? null;
        if ($cacheId === null) {
            throw new GraphQlInputException(__('Cache ID must be specified.'));
        }

        return in_array($cacheId, $this->cacheManager->setEnabled([$cacheId], $enable));
    }
}
