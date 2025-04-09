<?php

namespace Pasha234\Hw10;

use Pasha234\Hw10\Models\Book;
use Elastic\Elasticsearch\ClientBuilder;

class ElasticSearchHelper
{
    protected static function getElasticSearch()
    {
        $client = ClientBuilder::create()
            ->setHosts([$_ENV['ELASTIC_HOST'] ?? 'localhost:9200'])
            ->build();

        return new ElasticSearch($client);
    }

    public static function searchBooks(?string $search, ?string $price, ?string $category)
    {
        $elasticSearch = static::getElasticSearch();
        
        $filters = [
            [
                'range' => [
                    'stock.stock' => [
                        'gt' => 0
                    ]
                ]
            ]
        ];

        $criteria = [
            'title.autocomplete' => $search ?? '',
        ];

        if (!empty($category)) {
            $filters[] = [
                'term' => [
                    'category.keyword' => $category
                ]
            ];
        }

        if (!empty($price)) {
            $filters[] = [
                'range' => [
                    'price' => [
                        'lte' => (int) $price
                    ]
                ]
            ];
        }

        return $elasticSearch->search(Book::class, $criteria, $filters);
    }

    public static function getCategories(): array
    {
        $elasticSearch = static::getElasticSearch();

        return $elasticSearch->aggregationSearch(Book::class, 'categories', [
            'terms' => [
                'field' => 'category.keyword',
                'size' => 100
            ]
        ]);
    }

}
