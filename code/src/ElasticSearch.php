<?php

namespace Pasha234\Hw10;

use Elastic\Elasticsearch\ClientBuilder;

class ElasticSearch
{
    protected static function getClient()
    {
        $client = ClientBuilder::create()
            ->setHosts([$_ENV['ELASTIC_HOST'] ?? 'localhost:9200'])
            ->build();

        return $client;
    }

    public static function searchBooks(?string $search, ?string $price, ?string $category)
    {
        $client = self::getClient();
        
        $filters = [
            [
                'range' => [
                    'stock.stock' => [
                        'gt' => 0
                    ]
                ]
            ]
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

        $query = [
            'index' => $_ENV['ELASTIC_INDEX'],
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'match' => [
                                    'title.autocomplete' => $search ?? '',
                                ]
                            ]
                        ],
                        'filter' => $filters,
                    ]
                ]
            ]
        ];

        return $client->search($query);
    }

    public static function getCategories(): array
    {
        $client = self::getClient();

        $params = [
            'index' => $_ENV['ELASTIC_INDEX'],
            'body' => [
                'size' => 0,
                'aggs' => [
                    'categories' => [
                        'terms' => [
                            'field' => 'category.keyword',
                            'size' => 100
                        ]
                    ]
                ]
            ]
        ];

        $response = $client->search($params);

        return array_map(
            fn($bucket) => $bucket['key'],
            $response['aggregations']['categories']['buckets'] ?? []
        );
    }

}