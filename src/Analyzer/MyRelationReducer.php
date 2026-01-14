<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

final readonly class MyRelationReducer
{
    /**
     * @param MyEdge[] $considerEdges
     * @param string[] $ignoreTypes
     * @param string[] $nodeShouldNotStartWith
     * @param string[] $nodeShouldNotContain
     */
    public function __construct(
        private array $considerEdges = [],
        private array $ignoreTypes = [],
        private array $nodeShouldNotStartWith = [],
        private array $nodeShouldNotContain = [],
    ) {
    }

    /**
     * @param MyRelation[] $relations
     *
     * @return MyRelation[]
     */
    public function __invoke(array $relations, MyRelation $relation): array
    {
        // Should this relation be ignored because of its' "to" type?
        if ($this->shouldIgnore($relation)) {
            return $relations;
        }

        // Should this relation be ignored because of its' "edge" type?
        if (!in_array($relation->edge, $this->considerEdges, strict: true)) {
            return $relations;
        }

        // Is this relation already collected?
        foreach ($relations as $current) {
            if ($relation->equals($current)) {
                return $relations;
            }
        }

        return [...$relations, $relation];
    }

    private function shouldIgnore(MyRelation $relation): bool
    {
        if (in_array($relation->to->fqcn, $this->ignoreTypes, strict: true)) {
            return true;
        }

        foreach ($this->nodeShouldNotStartWith as $prefix) {
            if (str_starts_with($relation->to->fqcn, $prefix)) {
                return true;
            }
        }

        foreach ($this->nodeShouldNotContain as $subString) {
            if (str_contains($relation->to->getName(), $subString)) {
                return true;
            }
        }

        return false;
    }
}
