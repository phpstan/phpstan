<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

use PHPStan\Type\StaticResolvableType;
use PHPStan\Type\Type;

class IterableType extends BaseTypeX implements StaticResolvableType
{

	/** @var TypeX */
	private $keyType;

	/** @var TypeX */
	private $valueType;

	public function __construct(TypeXFactory $factory, TypeX $keyType, TypeX $valueType)
	{
		parent::__construct($factory);
		$this->keyType = $keyType;
		$this->valueType = $valueType;
	}

	public function describe(): string
	{
		return sprintf('iterable(%s[])', $this->valueType->describe());
	}

	public function acceptsX(TypeX $otherType): bool
	{
		return $otherType->isIterable() === self::RESULT_YES
			&& $this->keyType->acceptsX($otherType->getIterableKeyType())
			&& $this->valueType->acceptsX($otherType->getIterableValueType());
	}

	public function isAssignable(): int
	{
		return self::RESULT_YES;
	}

	public function isCallable(): int
	{
		return self::RESULT_MAYBE;
	}

	public function getCallReturnType(TypeX ...$callArgsTypes): TypeX
	{
		return $this->factory->createMixedType();
	}

	public function isIterable(): int
	{
		return self::RESULT_YES;
	}

	public function getIterableKeyType(): TypeX
	{
		return $this->keyType;
	}

	public function getIterableValueType(): TypeX
	{
		return $this->valueType;
	}

	public function canCallMethodsX(): int
	{
		return self::RESULT_MAYBE;
	}

	public function canAccessPropertiesX(): int
	{
		return self::RESULT_MAYBE;
	}

	public function canAccessOffset(): int
	{
		return self::RESULT_MAYBE;
	}

	public function resolveStatic(string $className): Type
	{
		return $this->factory->createIterableType(
			$this->keyType instanceof StaticResolvableType ? $this->keyType->resolveStatic($className) : $this->keyType,
			$this->valueType instanceof StaticResolvableType ? $this->valueType->resolveStatic($className) : $this->valueType
		);
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		return $this->factory->createArrayType(
			$this->keyType instanceof StaticResolvableType ? $this->keyType->changeBaseClass($className) : $this->keyType,
			$this->valueType instanceof StaticResolvableType ? $this->valueType->changeBaseClass($className) : $this->valueType
		);
	}
}
