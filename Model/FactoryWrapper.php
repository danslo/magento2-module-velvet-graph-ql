<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * As it is impossible to inject generated factories into virtualTypes, this wrapper exists to reduce code duplication.
 * Also see: https://github.com/magento/magento2/issues/6896
 */
class FactoryWrapper
{
    private object $factory;

    public function __construct(ObjectManagerInterface $objectManager, string $factory)
    {
        $this->factory = $objectManager->create($factory);
    }

    public function getFactory()
    {
        return $this->factory;
    }
}
