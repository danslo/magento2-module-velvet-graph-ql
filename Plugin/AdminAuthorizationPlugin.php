<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Plugin;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Danslo\VelvetGraphQl\Model\Authorization;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class AdminAuthorizationPlugin
{
    private Authorization $authorization;

    public function __construct(Authorization $authorization)
    {
        $this->authorization = $authorization;
    }

    public function beforeResolve(
        ResolverInterface $resolver,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if ($resolver instanceof AdminAuthorizationInterface) {
            $this->authorization->check($context, $resolver->getResource());
        }
        return [$field, $context, $info, $value, $args];
    }
}
