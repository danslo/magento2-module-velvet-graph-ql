<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Api;

use GraphQL\Language\AST\FieldNode;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

interface CollectionProcessorInterface
{
    public function process(FieldNode $field, ResolveInfo $info, AbstractDb $collection);
}
