<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Configuration;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Magento\Config\Block\System\Config\Form;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\Group;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

class Scopes implements ResolverInterface, AdminAuthorizationInterface
{
    private StoreManagerInterface $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    private function getStoresFromGroup(Group $group): array
    {
        $stores = [];
        foreach ($group->getStores() as $store) {
            $stores[] = [
                'name'     => $store->getName(),
                'type'     => Form::SCOPE_STORES,
                'scope_id' => $store->getId(),
                'disabled' => false,
                'children' => []
            ];
        }
        return $stores;
    }

    private function getStoreGroupsFromWebsite(Website $website): array
    {
        $groups = [];
        foreach ($website->getGroups() as $group) {
            $groups[] = [
                'name'     => $group->getName(),
                'type'     => 'groups',
                'scope_id' => $group->getId(),
                'disabled' => true,
                'children' => $this->getStoresFromGroup($group)
            ];
        }
        return $groups;
    }

    private function getWebsites(): array
    {
        $websites = [];
        foreach ($this->storeManager->getWebsites() as $website) {
            $websites[] = [
                'name'     => $website->getName(),
                'type'     => Form::SCOPE_WEBSITES,
                'scope_id' => $website->getId(),
                'disabled' => false,
                'children' => $this->getStoreGroupsFromWebsite($website)
            ];
        }
        return $websites;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        return [
            [
                'name'     => 'Default',
                'type'     => Form::SCOPE_DEFAULT,
                'scope_id' => null,
                'disabled' => false,
                'children' => $this->getWebsites()
            ]
        ];
    }
}
