<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Config;

use Magento\Config\Block\System\Config\Form;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ScopeDefiner extends \Magento\Config\Model\Config\ScopeDefiner
{
    private ?string $scope;

    public function __construct(?string $scope = null)
    {
        $this->scope = $scope;
    }

    public function getScope()
    {
        switch ($this->scope) {
            case Form::SCOPE_STORES:
                return ScopeInterface::SCOPE_STORE;
            case Form::SCOPE_WEBSITES:
                return ScopeInterface::SCOPE_WEBSITE;
        }
        return ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
    }
}
