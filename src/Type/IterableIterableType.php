<?php declare(strict_types = 1);

namespace PHPStan\Type;

class IterableIterableType implements IterableType
{
    use ClassTypeHelperTrait, IterableTypeTrait;

    public function __construct(
        Type $itemType,
        bool $nullable
    ) {
        $this->itemType = $itemType;
        $this->nullable = $nullable;
    }

    /**
     * @return string[]
     */
    public function getReferencedClasses(): array
    {
        return $this->getItemType()->getReferencedClasses();
    }

    public function combineWith(Type $otherType): Type
    {
        if ($otherType instanceof IterableType) {
            return new self(
                $this->getItemType()->combineWith($otherType->getItemType()),
                $this->isNullable() || $otherType->isNullable()
            );
        }

        if ($otherType instanceof NullType) {
            return $this->makeNullable();
        }

        return new MixedType();
    }

    public function makeNullable(): Type
    {
        return new self($this->getItemType(), true);
    }

    public function accepts(Type $type): bool
    {
        if ($type instanceof IterableType) {
            return $this->getItemType()->accepts($type->getItemType());
        }

        if ($type->getClass() !== null && $this->exists($type->getClass())) {
            $classReflection = new \ReflectionClass($type->getClass());
            return $classReflection->implementsInterface(\Traversable::class);
        }

        if ($type instanceof MixedType) {
            return true;
        }

        if ($this->isNullable() && $type instanceof NullType) {
            return true;
        }

        if ($type instanceof UnionType && UnionTypeHelper::acceptsAll($this, $type)) {
            return true;
        }

        return false;
    }

    public function describe(): string
    {
        return sprintf('iterable(%s[])', $this->getItemType()->describe()) . ($this->nullable ? '|null' : '');
    }

    public function isDocumentableNatively(): bool
    {
        return true;
    }

    public function resolveStatic(string $className): Type
    {
        if ($this->getItemType() instanceof StaticResolvableType) {
            return new self(
                $this->getItemType()->resolveStatic($className),
                $this->isNullable()
            );
        }

        return $this;
    }

    public function changeBaseClass(string $className): StaticResolvableType
    {
        if ($this->getItemType() instanceof StaticResolvableType) {
            return new self(
                $this->getItemType()->changeBaseClass($className),
                $this->isNullable()
            );
        }

        return $this;
    }
}
