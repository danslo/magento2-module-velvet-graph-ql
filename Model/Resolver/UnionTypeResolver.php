<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

class UnionTypeResolver implements TypeResolverInterface
{
    /**
     * Revisit this later, as there most certainly has to be a cleaner way of doing this,
     * but Magento specifically requires a typeResolver for unions.
     */
    public function resolveType(array $data): string
    {
        return $data['schema_type'] ?? '';
    }
}
