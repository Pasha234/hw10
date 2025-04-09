<?php

namespace Pasha234\Hw10\Models;

class Book implements ElasticModelInterface {
    protected $index = 'otus-shop';

    public function __construct(
        public string $title = '',
        public string $category = '',
        public float $price = 0,
        public array $stock = []
    )
    {
        
    }

    public static function createFromElastic(array $doc): static
    {
        $stock = [];
        if (!empty($doc['_source']['stock'] ?? []) && is_array($doc['_source']['stock'] ?? [])) {
            foreach ($doc['_source']['stock'] as $stockEntry) {
                $stock[$stockEntry['shop'] ?? ''] = $stockEntry['stock'] ?? 0;
            }
        }

        return new static(
            $doc['_source']['title'] ?? '',
            $doc['_source']['category'] ?? '',
            $doc['_source']['price'] ?? '',
            $stock
        );
    }

    public function toElasticDocument(): array
    {
        return [
            'title' => $this->title,
            'category' => $this->category,
            'price' => $this->price,
            'stock' => $this->stock
        ];
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'category' => $this->category,
            'price' => $this->price,
            'stock' => $this->stock
        ];
    }

    public function getIndex(): string
    {
        return $this->index;
    }
}