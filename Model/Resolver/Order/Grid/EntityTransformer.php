<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Order\Grid;

use Danslo\VelvetGraphQl\Api\EntityTransformerInterface;
use Magento\Directory\Model\Currency;
use Magento\Store\Model\System\Store as SystemStore;

class EntityTransformer implements EntityTransformerInterface
{
    private Currency $currency;
    private array $currencyFormatFields;
    private SystemStore $store;

    public function __construct(
        Currency $currency,
        SystemStore $store,
        array $currencyFormatFields = []
    ) {
        $this->currency = $currency;
        $this->currencyFormatFields = $currencyFormatFields;
        $this->store = $store;
    }

    private function getStorenameFromStoreId(int $storeId): string
    {
        return $this->store->getStoreName($storeId);
    }

    public function transform(array $data): array
    {
        foreach ($this->currencyFormatFields as $field) {
            if (!isset($data[$field])) {
                continue;
            }
            $data['formatted_' . $field] = $this->currency->format($data[$field], [], false);
        }

        $data['store_name'] = $this->getStorenameFromStoreId((int) $data['store_id']);

        return $data;
    }
}
