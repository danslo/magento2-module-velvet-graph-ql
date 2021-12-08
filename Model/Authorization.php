<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\GraphQl\Model\Query\ContextInterface;

class Authorization
{
    public function validate(ContextInterface $context)
    {
        if ($context->getUserType() !== UserContextInterface::USER_TYPE_ADMIN) {
            throw new GraphQlAuthorizationException(__('Admin authorization required.'));
        }
    }
}
