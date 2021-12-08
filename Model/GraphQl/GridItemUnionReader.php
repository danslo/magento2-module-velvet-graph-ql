<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\GraphQl;

use Danslo\VelvetGraphQl\Model\Resolver\UnionTypeResolver;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\Reader\UnionType;

class GridItemUnionReader implements \Magento\Framework\Config\ReaderInterface
{
    private array $gridItemTypes;

    public function __construct(array $gridItemTypes = [])
    {
        $this->gridItemTypes = $gridItemTypes;
    }

    public function read($scope = null)
    {
        return [
            'GridItem' => [
                'name' => 'GridItem',
                'type' => UnionType::GRAPHQL_UNION,
                'typeResolver' => UnionTypeResolver::class,
                'types' => $this->gridItemTypes
            ]
        ];
    }
}
