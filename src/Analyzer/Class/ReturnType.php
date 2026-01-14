<?php

declare(strict_types=1);

namespace Tactix\Analyzer\Class;

final readonly class ReturnType implements \Stringable
{
    private function __construct(
        public ReturnTypeKind $kind,
        public ?Name $typeName = null,
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

    public static function union(Name $typeName): self
    {
        return new self(ReturnTypeKind::UNION, $typeName);
    }

    public static function intersection(Name $typeName): self
    {
        return new self(ReturnTypeKind::INTERSECTION, $typeName);
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
                default => (string) $this->typeName,
            },
            $this->isCollection() ? '[]' : ''
        );
    }
}
