<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Api;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\GraphQl\Config\Element\Field;

interface CollectionProcessorInterface
{
    public function process(Field $field, AbstractDb $collection);
}
