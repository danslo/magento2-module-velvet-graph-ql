<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Api;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

interface CollectionProcessorInterface
{
    public function process(Field $field, AbstractCollection $collection);
}
