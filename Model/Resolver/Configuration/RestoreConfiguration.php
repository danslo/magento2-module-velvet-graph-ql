<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Configuration;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class RestoreConfiguration implements ResolverInterface, AdminAuthorizationInterface
{
    private ConfigResource $configResource;
    private ReinitableConfigInterface $reinitableConfig;
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ConfigResource $configResource,
        ReinitableConfigInterface $reinitableConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->configResource = $configResource;
        $this->reinitableConfig = $reinitableConfig;
        $this->scopeConfig = $scopeConfig;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->configResource->deleteConfig(
            $args['path'],
            $args['scope_type'] ?? ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $args['scope_id'] ?? 0
        );

        $this->reinitableConfig->reinit();

        return $this->scopeConfig->getValue($args['path']);
    }
}
