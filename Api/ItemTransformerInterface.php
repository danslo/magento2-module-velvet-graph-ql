<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Api;

use Magento\Framework\Model\AbstractModel;

interface ItemTransformerInterface
{
    public function transform(AbstractModel $model, array $data): array;
}
