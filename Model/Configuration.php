<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model;

use Danslo\VelvetGraphQl\Model\Config\ScopeDefinerFactory;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\DataFactory as StructureDataFactory;
use Magento\Config\Model\Config\StructureFactory;
use Magento\Framework\App\Area;
use Magento\Framework\Config\ScopeInterfaceFactory;

class Configuration
{
    private StructureFactory $structureFactory;
    private StructureDataFactory $structureDataFactory;
    private ScopeInterfaceFactory $scopeFactory;
    private ScopeDefinerFactory $scopeDefinerFactory;

    public function __construct(
        StructureFactory $structureFactory,
        StructureDataFactory $structureDataFactory,
        ScopeInterfaceFactory $scopeFactory,
        ScopeDefinerFactory $scopeDefinerFactory
    ) {
        $this->structureFactory = $structureFactory;
        $this->structureDataFactory = $structureDataFactory;
        $this->scopeFactory = $scopeFactory;
        $this->scopeDefinerFactory = $scopeDefinerFactory;
    }

    public function getAdminhtmlConfigStructure(?string $scope = null): Structure
    {
        $configScope = $this->scopeFactory->create();
        $configScope->setCurrentScope(Area::AREA_ADMINHTML);
        return $this->structureFactory->create([
            'structureData' => $this->structureDataFactory->create(['configScope' => $configScope]),
            'scopeDefiner' => $this->scopeDefinerFactory->create(['scope' => $scope])
        ]);
    }
}
