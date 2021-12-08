<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Api;

interface EntityTransformerInterface
{
    public function transform(array $data): array;
}
