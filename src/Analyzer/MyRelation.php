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
        public MyEdge $edge,
        public MyNode $to,
    ) {
    }

    public static function create(MyNode $from, MyEdge $edge, MyNode $to): self
    {
        return new self($from, $edge, $to);
    }

    public function isForbidden(): bool
    {
        $from = $this->getFromAttribute();
        $to = $this->getToAttribute();

        return $from && $to && Forbidden::check($from, $to);
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
        $from = $this->getFromAttribute();
        $to = $this->getToAttribute();

        if (!$from || !$to) {
            throw new \InvalidArgumentException(sprintf('Either "%s" or "%s" does not have a tactical attribute!', $this->from, $this->to));
        }

        return sprintf('%s %s %s', $from->value, $this->edge->value, $to->value);
    }

    public function getFromAttribute(): ?AttributeName
    {
        return AttributeNameFactory::fromClassOrNull($this->from->fqcn);
    }

    public function getToAttribute(): ?AttributeName
    {
        return AttributeNameFactory::fromClassOrNull($this->to->fqcn);
    }

    public function __toString(): string
    {
        return sprintf('(%s)-[%s]->(%s)', $this->from, $this->edge->value, $this->to);
    }
}
