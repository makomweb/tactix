<?php

declare(strict_types=1);

namespace Tactix\Analyzer\Class;

final readonly class ReturnType implements \Stringable
{
    /**
     * @param Name[] $unionTypes
     * @param Name[] $intersectionTypes
     */
    private function __construct(
        public ReturnTypeKind $kind,
        public ?Name $typeName = null,
        public array $unionTypes = [],
        public array $intersectionTypes = [],
    ) {
    }

    public static function void(): self
    {
        return new self(ReturnTypeKind::VOID);
    }

    public static function unknown(): self
    {
        return new self(ReturnTypeKind::UNKNOWN);
    }

    public static function regular(Name $typeName): self
    {
        return new self(ReturnTypeKind::REGULAR, $typeName);
    }

    /**
     * @param Name[] $types
     */
    public static function union(array $types): self
    {
        return new self(
            kind: ReturnTypeKind::UNION,
            typeName: new Name(implode('|', $types), NameType::UNKNOWN),
            unionTypes: $types
        );
    }

    /**
     * @param Name[] $types
     */
    public static function intersection(array $types): self
    {
        return new self(
            kind: ReturnTypeKind::UNION,
            typeName: new Name(implode('&', $types), NameType::UNKNOWN),
            intersectionTypes: $types
        );
    }

    public static function nullable(Name $typeName): self
    {
        return new self(ReturnTypeKind::NULLABLE, $typeName);
    }

    public static function collection(Name $typeName): self
    {
        return new self(ReturnTypeKind::COLLECTION, $typeName);
    }

    public static function generator(Name $typeName): self
    {
        return new self(ReturnTypeKind::GENERATOR, $typeName);
    }

    public function isArray(): bool
    {
        return $this->typeName?->isArray() ?? false;
    }

    public function isGenerator(): bool
    {
        return $this->typeName?->isGenerator() ?? false;
    }

    public function isNullable(): bool
    {
        return ReturnTypeKind::NULLABLE === $this->kind;
    }

    public function isCollection(): bool
    {
        return ReturnTypeKind::COLLECTION === $this->kind;
    }

    public function isVoid(): bool
    {
        return ReturnTypeKind::VOID === $this->kind;
    }

    public function isUnion(): bool
    {
        assert(!empty($this->unionTypes));

        return ReturnTypeKind::UNION === $this->kind;
    }

    public function isIntersection(): bool
    {
        assert(!empty($this->intersectionTypes));

        return ReturnTypeKind::INTERSECTION === $this->kind;
    }

    public function canBeIgnored(): bool
    {
        if (null === $this->typeName) {
            return true;
        }

        $type = (string) $this->typeName;

        return in_array($type, ['self', 'unknown', 'void'], true);
    }

    public function __toString(): string
    {
        return sprintf(
            '%s%s%s',
            $this->isNullable() ? '?' : '',
            match ($this->kind) {
                ReturnTypeKind::VOID => 'void',
                ReturnTypeKind::UNKNOWN => 'unknown',
                ReturnTypeKind::UNION => implode('|', array_map(fn (Name $n) => (string) $n, $this->unionTypes)),
                ReturnTypeKind::INTERSECTION => implode('&', array_map(fn (Name $n) => (string) $n, $this->intersectionTypes)),
                default => (string) $this->typeName,
            },
            $this->isCollection() ? '[]' : ''
        );
    }
}
