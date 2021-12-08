<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Api;

interface AdminAuthorizationInterface
{
    public function getResource(): string;
}
