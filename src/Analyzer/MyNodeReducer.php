<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

final readonly class MyNodeReducer
{
    /**
     * @param string[] $ignoreTypes
     * @param string[] $shouldNotStartWith
     * @param string[] $shouldNotContain
     */
    public function __construct(
        private array $ignoreTypes = [],
        private array $shouldNotStartWith = [],
        private array $shouldNotContain = [],
    ) {
    }

    /**
     * @param MyNode[] $nodes
     *
     * @return MyNode[]
     */
    public function __invoke(array $nodes, MyNode $node): array
    {
        if ($this->shouldIgnore($node)) {
            return $nodes;
        }

        foreach ($nodes as $current) {
            if ($node->equals($current)) {
                return $nodes;
            }
        }

        return [...$nodes, $node];
    }

    private function shouldIgnore(MyNode $node): bool
    {
        if (in_array($node->getName(), $this->ignoreTypes, strict: true)) {
            return true;
        }

        foreach ($this->shouldNotStartWith as $prefix) {
            if (str_starts_with($node->fqcn, $prefix)) {
                return true;
            }
        }

        foreach ($this->shouldNotContain as $subString) {
            if (str_contains($node->getName(), $subString)) {
                return true;
            }
        }

        return false;
    }
}
