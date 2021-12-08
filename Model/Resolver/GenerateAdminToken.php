<?php

namespace Danslo\VelvetGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Integration\Api\AdminTokenServiceInterface;

class GenerateAdminToken implements ResolverInterface
{
    private AdminTokenServiceInterface $adminTokenService;

    public function __construct(AdminTokenServiceInterface $adminTokenService)
    {
        $this->adminTokenService = $adminTokenService;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->validateArguments($args);

        try {
            return $this->adminTokenService->createAdminAccessToken($args['username'], $args['password']);
        } catch (\Exception $e) {
            throw new GraphQlAuthorizationException(__('Unable to authorize admin token.'));
        }
    }

    private function validateArguments(?array $args): void
    {
        if (empty(trim($args['username'], " "))) {
            throw new GraphQlInputException(__('Specify the username.'));
        }
        if (empty(trim($args['password'], " "))) {
            throw new GraphQlInputException(__('Specify the password.'));
        }
    }
}
