<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use Tactix\AttributeName;
use Tactix\AttributeNameFactory;
use Tactix\Forbidden;

final readonly class MyRelation implements \Stringable
{
    private function __construct(
        public MyNode $from,
        private ?AttributeName $fromAttribute,
        public MyEdge $edge,
        public MyNode $to,
        private ?AttributeName $toAttribute,
        public bool $isForbidden,
    ) {
    }

    public static function create(MyNode $from, MyEdge $edge, MyNode $to): self
    {
        /** @var class-string $fromClass */
        $fromClass = $from->fqcn;
        $fromAttribute = AttributeNameFactory::fromClassOrNull($fromClass);

        /** @var class-string $toClass */
        $toClass = $to->fqcn;
        $toAttribute = AttributeNameFactory::fromClassOrNull($toClass);

        $isForbidden =
            !is_null($fromAttribute)
            && !is_null($toAttribute)
            && Forbidden::check($fromAttribute, $toAttribute);

        return new self($from, $fromAttribute, $edge, $to, $toAttribute, $isForbidden);
    }

    public function equals(self $other): bool
    {
        return $this->from->equals($other->from)
            && $this->edge === $other->edge
            && $this->to->equals($other->to);
    }

    public function getDescription(): string
    {
        return sprintf('%s %s', $this, $this->toTacticalString());
    }

    public function toTacticalString(): string
    {
        if (is_null($this->fromAttribute) || is_null($this->toAttribute)) {
            throw new \InvalidArgumentException(sprintf('Either "%s" or "%s" does not have a tactical attribute!', $this->from, $this->to));
        }

        return sprintf('%s %s %s', $this->fromAttribute->value, $this->edge->value, $this->toAttribute->value);
    }

    public function __toString(): string
    {
        return sprintf('(%s)-[%s]->(%s)', $this->from, $this->edge->value, $this->to);
    }
}
