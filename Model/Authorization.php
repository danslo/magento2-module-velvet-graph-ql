<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\GraphQl\Model\Query\ContextInterface;

class Authorization
{
    private AuthorizationInterface $authorization;

    public function __construct(AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    public function check(
        ContextInterface $context,
        string $resource
    ) {
        if ($context->getUserType() !== UserContextInterface::USER_TYPE_ADMIN) {
            throw new GraphQlAuthorizationException(__('Admin authorization required.'));
        }

        if (!$this->authorization->isAllowed($resource)) {
            throw new GraphQlAuthorizationException(__('Admin user is not allowed for the resource.'));
        }
    }
}
