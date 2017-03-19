<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

use PHPStan\Type\StaticResolvableType;

class ArrayType extends BaseTypeX implements StaticResolvableType
{

	/** @var TypeX */
	private $keyType;

	/** @var TypeX */
	private $valueType;

	/** @var bool */
	private $inferredFromLiteral;

	public function __construct(TypeXFactory $factory, TypeX $keyType, TypeX $valueType, bool $inferredFromLiteral = false)
	{
		parent::__construct($factory);
		$this->keyType = $keyType;
		$this->valueType = $valueType;
		$this->inferredFromLiteral = $inferredFromLiteral;
	}

	public function getKeyType(): TypeX
	{
		return $this->keyType;
	}

	public function getValueType(): TypeX
	{
		return $this->valueType;
	}

	public function isInferredFromLiteral(): bool
	{
		return $this->inferredFromLiteral;
	}

	public function describe(): string
	{
		// TODO: change to sth like array<keyType, valueType>
		return sprintf('%s[]', $this->valueType->describe());
	}

	public function acceptsX(TypeX $otherType): bool
	{
		return $otherType instanceof self
			&& $this->getIterableKeyType()->acceptsX($otherType->getIterableKeyType())
			&& $this->getIterableValueType()->acceptsX($otherType->getIterableValueType());
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
		return $this->factory->createErrorType();
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
		return self::RESULT_NO;
	}

	public function canAccessPropertiesX(): int
	{
		return self::RESULT_NO;
	}

	public function canAccessOffset(): int
	{
		return self::RESULT_YES;
	}

	public function getOffsetValueType(TypeX $offsetType): TypeX
	{
		if ($this->keyType->acceptsX($offsetType)) {
			return $this->valueType;
		}

		return $this->factory->createErrorType();
	}

	public function setOffsetValueType(TypeX $offsetType = null, TypeX $valueType): TypeX
	{
		if ($offsetType === null) {
			$offsetType = $this->factory->createIntegerType();
		}

		if ($this->keyType->acceptsX($offsetType) && $this->valueType->acceptsX($valueType)) {
			return $this; // to keep the original $inferredFromLiteral
		}

		if ($offsetType instanceof ConstantType) {
			$offsetType = $offsetType->generalize();
		}

		if ($valueType instanceof ConstantType) {
			$valueType = $valueType->generalize();
		}

		return $this->factory->createArrayType(
			$this->keyType->combineWith($offsetType),
			$this->valueType->combineWith($valueType),
			true
		);
	}

	public function resolveStatic(string $className): \PHPStan\Type\Type
	{
		return $this->factory->createArrayType(
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
