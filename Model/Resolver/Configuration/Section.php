<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Configuration;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Danslo\VelvetGraphQl\Model\Configuration;
use Magento\Config\App\Config\Type\System;
use Magento\Config\Block\System\Config\Form;
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;
use Magento\Config\Model\Config\Structure\Element\Field as ConfigField;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Framework\App\Config\Data\ProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\DataObject;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;

class Section implements ResolverInterface, AdminAuthorizationInterface
{
    private Configuration $configuration;
    private ConfigFactory $configFactory;
    private ScopeConfigInterface $scopeConfig;
    private SettingChecker $settingChecker;
    private DeploymentConfig $deploymentConfig;
    private StoreManagerInterface $storeManager;

    public function __construct(
        Configuration $configuration,
        ConfigFactory $configFactory,
        ScopeConfigInterface $scopeConfig,
        SettingChecker $settingChecker,
        DeploymentConfig $deploymentConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->configuration = $configuration;
        $this->configFactory = $configFactory;
        $this->scopeConfig = $scopeConfig;
        $this->settingChecker = $settingChecker;
        $this->deploymentConfig = $deploymentConfig;
        $this->storeManager = $storeManager;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $scopeType = $args['scope_type'] ?? Form::SCOPE_DEFAULT;
        $scopeId = $args['scope_id'] ?? null;

        /** @var \Magento\Config\Model\Config\Structure\Element\Section $section */
        $section = $this->configuration->getAdminhtmlConfigStructure($scopeType)->getElement($args['section']);
        if ($section->hasChildren() === false) {
            return [];
        }

        $configDataObject = $this->configFactory->create(
            [
                'data' => [
                    'section' => $section->getId(),
                    'website' => $scopeType === Form::SCOPE_WEBSITES ? $scopeId : null,
                    'store'   => $scopeType === Form::SCOPE_STORES ? $scopeId : null,
                ],
            ]
        );

        $configData = $configDataObject->load();

        $groups = [];
        /** @var Group $group */
        foreach ($section->getChildren() as $group) {
            $fields = [];
            foreach ($group->getChildren() as $field) {
                if (!($field instanceof ConfigField)) {
                    // TODO: handle groups inside of groups
                    continue;
                }

                $path = $field->getPath();
                $data = $this->getFieldData($configData, $field, $path, $scopeType, $scopeId);
                if (is_array($data)) {
                    // TODO: handle multi dimensional configuration
                    continue;
                }

                $options = $this->getOptionsFromField($field);
                $inheritRequired = $this->isInheritCheckboxRequired($field, $scopeType);
                $fields[] = [
                    'label' => (string) $field->getLabel(),
                    'type' => $field->getType(),
                    'comment' => ((string) $field->getComment()) ?: null,
                    'options' =>  $options,
                    'value' => $data ?? ($field->hasOptions() ? $options[0]['value'] : null),
                    'inherit' => $inheritRequired && !array_key_exists($path, $configData),
                    'show_inherit' => $inheritRequired,
                    'inherit_label' => $this->getInheritCheckboxLabel($field, $scopeType),
                    'path' => $path
                ];
            }
            $groups[] = [
                'label' => (string) $group->getLabel(),
                'fields' => $fields
            ];
        }

        return $groups;
    }

    public function canUseDefaultValue(bool $fieldValue, string $scopeType): bool
    {
        if ($scopeType == Form::SCOPE_STORES && $fieldValue) {
            return true;
        }
        if ($scopeType == Form::SCOPE_WEBSITES && $fieldValue) {
            return true;
        }
        return false;
    }

    public function canUseWebsiteValue(bool $fieldValue, string $scopeType): bool
    {
        if ($scopeType == Form::SCOPE_STORES && $fieldValue) {
            return true;
        }
        return false;
    }

    public function isCanRestoreToDefault(bool $fieldValue, string $scopeType): bool
    {
        if ($scopeType == Form::SCOPE_DEFAULT && $fieldValue) {
            return true;
        }
        return false;
    }

    private function isInheritCheckboxRequired(ConfigField $field, string $scopeType): bool
    {
        return $this->canUseDefaultValue($field->showInDefault(), $scopeType) ||
            $this->canUseWebsiteValue($field->showInWebsite(), $scopeType) ||
            $this->isCanRestoreToDefault($field->canRestore(), $scopeType);
    }

    private function getInheritCheckboxLabel(ConfigField $field, string $scopeType): string
    {
        if ($this->canUseWebsiteValue($field->showInWebsite(), $scopeType)) {
            return 'Use Website';
        }
        if ($this->canUseDefaultValue($field->showInDefault(), $scopeType)) {
            return 'Use Default';
        }
        return 'Use system value';
    }

    public function getConfigValue(string $path, string $scopeType, ?int $scopeId): ?string
    {
        return $this->scopeConfig->getValue($path, $scopeType, $scopeId);
    }

    private function getAppConfigDataValue(string $path, string $scopeType, ?int $scopeId)
    {
        $appConfig = $this->deploymentConfig->get(System::CONFIG_TYPE);
        $scopeCode = $this->getStringScopeCode($scopeType, $scopeId);
        if ($scopeType === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $data = new DataObject($appConfig[$scopeType] ?? []);
        } else {
            $data = new DataObject($appConfig[$scopeType][$scopeCode] ?? []);
        }
        return $data->getData($path);
    }

    private function getStringScopeCode(string $scopeType, ?int $scopeId): string
    {
        switch ($scopeType) {
            case Form::SCOPE_WEBSITES:
                return $this->storeManager->getWebsite($scopeId)->getCode();
            case Form::SCOPE_STORES:
                return $this->storeManager->getStore($scopeId)->getCode();
        }
        return '';
    }

    private function getFieldData(array $configData, ConfigField $field, string $path, string $scopeType, ?int $scopeId)
    {
        $data = $this->getAppConfigDataValue($path, $scopeType, $scopeId);

        $placeholderValue = $this->settingChecker->getPlaceholderValue(
            $path,
            $scopeType,
            $this->getStringScopeCode($scopeType, $scopeId)
        );

        if ($placeholderValue) {
            $data = $placeholderValue;
        }

        if ($data === null) {
            $path = $field->getConfigPath() !== null ? $field->getConfigPath() : $path;
            $data = $this->getConfigValue($path, $scopeType, $scopeId);
            if ($field->hasBackendModel()) {
                $backendModel = $field->getBackendModel();
                if (!$backendModel instanceof ProcessorInterface) {
                    if (array_key_exists($path, $configData)) {
                        $data = $configData[$path];
                    }

                    $backendModel->setPath($path)
                        ->setValue($data)
                        ->setWebsite($scopeType === Form::SCOPE_WEBSITES ? $scopeId : null)
                        ->setStore($scopeType === Form::SCOPE_STORES ? $scopeId : null)
                        ->afterLoad();
                    $data = $backendModel->getValue();
                }
            }
        }

        return $data;
    }

    private function getOptionsFromField($field): array
    {
        $options = [];
        if ($field->hasOptions()) {
            foreach ($field->getOptions() as $k => $v) {
                if (is_array($v)) {
                    $v['label'] = (string)$v['label'];
                    $options[] = $v;
                } else {
                    $options[] = ['value' => (string) $k, 'label' => (string)$v];
                }
            }
        }
        return $options;
    }
}
