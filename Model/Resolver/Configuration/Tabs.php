<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Configuration;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Danslo\VelvetGraphQl\Model\Configuration;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Tabs implements ResolverInterface, AdminAuthorizationInterface
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    private function getConfigurationTabs(string $scopeType): array
    {
        $tabs = [];
        foreach ($this->configuration->getAdminhtmlConfigStructure($scopeType)->getTabs() as $tab) {
            $sections = [];
            foreach ($tab->getChildren() as $section) {
                $sections[] = ['label' => $section->getLabel(), 'path' => $section->getId()];
            }
            $tabs[] = ['label' => $tab->getLabel(), 'sections' => $sections];
        }
        return $tabs;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        return $this->getConfigurationTabs($args['scope_type'] ?? ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }
}
