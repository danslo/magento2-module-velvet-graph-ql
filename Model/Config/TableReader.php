<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Config;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\Reader\InputObjectType;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\Reader\ObjectType;

class TableReader implements ReaderInterface
{
    private ResourceConnection $resourceConnection;
    private string $tableName;
    private string $schemaType;
    private bool $generateInputType;
    private array $nullableInputFields;

    public function __construct(
        ResourceConnection $resourceConnection,
        string $tableName,
        string $schemaType,
        bool $generateInputType = true,
        array $nullableInputFields = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->tableName = $tableName;
        $this->schemaType = $schemaType;
        $this->generateInputType = $generateInputType;
        $this->nullableInputFields = $nullableInputFields;
    }

    private function getTypeFromColumnDescription(array $description): String
    {
        switch ($description['DATA_TYPE']) {
            case 'tinyint':
            case 'smallint':
            case 'int':
            case 'mediumint':
            case 'bigint':
                return 'Int';
            case 'decimal':
            case 'numeric':
                return 'Float';
            default:
                return 'String';
        }
    }

    private function getSchemaConfig(
        string $schemaType,
        string $graphqlType = ObjectType::GRAPHQL_TYPE,
        bool $isInputType = false
    ): array {
        $config = [
            'name' => $schemaType,
            'type' => $graphqlType,
            'fields' => []
        ];

        $tableDescription = $this->resourceConnection->getConnection()->describeTable($this->tableName);
        foreach ($tableDescription as $column => $description) {
            $columnIsRequired = $description['NULLABLE'] === false;

            if ($isInputType) {
                if (in_array($column, $this->nullableInputFields) || $description['PRIMARY'] === true) {
                    $columnIsRequired = false;
                }
            }

            $config['fields'][$column] = [
                'name' => $column,
                'type' => $this->getTypeFromColumnDescription($description),
                'required' => $columnIsRequired,
                'arguments' => []
            ];
        }

        return $config;
    }

    public function read($scope = null)
    {
        $connection = $this->resourceConnection->getConnection();
        if (!$connection->isTableExists($this->tableName)) {
            return [];
        }

        $types = [$this->schemaType => $this->getSchemaConfig($this->schemaType)];

        if ($this->generateInputType) {
            $inputType = $this->schemaType . 'Input';
            $types[$inputType] = $this->getSchemaConfig($inputType, InputObjectType::GRAPHQL_INPUT, true);
        }

        return $types;
    }
}
