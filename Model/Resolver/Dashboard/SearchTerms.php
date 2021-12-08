<?php

declare(strict_types=1);

namespace Danslo\VelvetGraphQl\Model\Resolver\Dashboard;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as SearchCollectionFactory;

class SearchTerms implements ResolverInterface
{
    private bool $popularFilter;
    private bool $recentFilter;
    private SearchCollectionFactory $searchCollectionFactory;

    public function __construct(
        SearchCollectionFactory $searchCollectionFactory,
        bool $popularFilter = false,
        bool $recentFilter = false
    ) {
        $this->popularFilter = $popularFilter;
        $this->recentFilter = $recentFilter;
        $this->searchCollectionFactory = $searchCollectionFactory;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $searchCollection = $this->searchCollectionFactory->create()->setPageSize(5);

        if ($this->popularFilter) {
            $searchCollection->setPopularQueryFilter();
        }

        if ($this->recentFilter) {
            $searchCollection->setRecentQueryFilter();
        }

        $searchTerms = [];
        foreach ($searchCollection as $searchTerm) {
            $searchTerms[] = [
                'search_term' => $searchTerm->getQueryText(),
                'results' => $searchTerm->getNumResults(),
                'uses' => $searchTerm->getPopularity()
            ];
        }
        return $searchTerms;
    }
}
