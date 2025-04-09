<?php

namespace Pasha234\Hw10\Models;

interface ElasticModelInterface {
    public function toElasticDocument(): array;

    public function toArray(): array;

    public function getIndex(): string;

    public static function createFromElastic(array $doc): static;
}