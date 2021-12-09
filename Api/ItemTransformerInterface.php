<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Api;

use Magento\Framework\DataObject;

interface ItemTransformerInterface
{
    public function transform(DataObject $model, array $data): array;
}
