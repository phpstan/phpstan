<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

use PHPStan\Broker\Broker;
use PHPStan\Type\StaticResolvableType;
use PHPStan\Type\UnionTypeHelper;

class ConstantArrayType extends ArrayType implements ConstantType, StaticResolvableType
{
	/** @var Broker */
	private $broker;

	/** @var array|TypeX[] */
	private $keyTypes;

	/** @var array|TypeX[] */
	private $valueTypes;

	public function __construct(TypeXFactory $factory, Broker $broker, array $keyTypes, array $valueTypes)
	{
		assert(count($keyTypes) === count($valueTypes));

		parent::__construct(
			$factory,
			$keyTypes ? $factory->createUnionType(...$keyTypes) : $factory->createMixedType(),
			$valueTypes ? $factory->createUnionType(...$valueTypes) : $factory->createMixedType(),
			true
		);

		$this->broker = $broker;
		$this->keyTypes = $keyTypes;
		$this->valueTypes = $valueTypes;
	}

	/**
	 * @return array|TypeX[]
	 */
	public function getKeyTypes(): array
	{
		return $this->keyTypes;
	}

	/**
	 * @return array|TypeX[]
	 */
	public function getValueTypes(): array
	{
		return $this->valueTypes;
	}

	public function generalize(): TypeX
	{
		return $this->factory->createArrayType($this->getKeyType(), $this->getValueType(), true);
	}

	public function describe(): string
	{
		return sprintf('%s[]', $this->getValueType()->describe());
	}

	public function acceptsX(TypeX $otherType): bool
	{
		if (!($otherType instanceof self)) {
			return false;
		}

		if (count($this->keyTypes) !== count($otherType->keyTypes)) {
			return false;
		}

		foreach ($this->keyTypes as $i => $keyType) {
			if (!$keyType->acceptsX($otherType->keyTypes[$i])) {
				return false;
			}

			if (!$this->valueTypes[$i]->acceptsX($otherType->valueTypes[$i])) {
				return false;
			}
		}

		return true;
	}

	public function isCallable(): int
	{
		if (count($this->keyTypes) !== 2) {
			return self::RESULT_NO;
		}

		if (!$this->keyTypes[0]->acceptsX($this->factory->createConstantIntegerType(0))) {
			return self::RESULT_NO;
		}

		if (!$this->keyTypes[1]->acceptsX($this->factory->createConstantIntegerType(1))) {
			return self::RESULT_NO;
		}

		$classOrObject = $this->valueTypes[0];
		$method = $this->valueTypes[1];

		if ($classOrObject instanceof ConstantStringType) {
			$className = $classOrObject->getValue();

		} elseif ($classOrObject instanceof ObjectType && $classOrObject->getClass() !== null) {
			$className = $classOrObject->getClass();

		} else {
			return self::RESULT_MAYBE;
		}

		if (!$this->broker->hasClass($className)) {
			return self::RESULT_NO;
		}

		if ($method instanceof ConstantStringType) {
			$methodName = $method->getValue();

		} else {
			return self::RESULT_MAYBE;
		}

		if (!$this->broker->getClass($className)->hasMethod($methodName)) {
			return self::RESULT_NO;
		}

		return self::RESULT_YES;
	}

	public function getOffsetValueType(TypeX $offsetType): TypeX
	{
		$matchingValueTypes = [];
		foreach ($this->keyTypes as $i => $keyType) {
			if ($keyType->acceptsX($offsetType)) {
				$matchingValueTypes[] = $this->valueTypes[$i];
			}
		}

		if ($matchingValueTypes) {
			return $this->factory->createUnionType(...$matchingValueTypes);

		} else {
			return $this->factory->createErrorType(ErrorType::UNDEFINED_OFFSET);
		}
	}

	public function setOffsetValueType(TypeX $offsetType = null, TypeX $valueType): TypeX
	{
		if ($offsetType instanceof ConstantScalarType) {
			foreach ($this->keyTypes as $i => $keyType) {
				if ($keyType instanceof ConstantScalarType) {
					if ($keyType->getValue() === $offsetType->getValue()) {
						$newValueTypes = $this->valueTypes;
						$newValueTypes[$i] = $valueType;
						return $this->factory->createConstantArrayType($this->keyTypes, $newValueTypes);
					}

				} else {
					break;
				}
			}
		}

		return parent::setOffsetValueType($offsetType, $valueType);
	}

	public function resolveStatic(string $className): \PHPStan\Type\Type
	{
		return $this->factory->createConstantArrayType(
			UnionTypeHelper::resolveStatic($className, $this->keyTypes),
			UnionTypeHelper::resolveStatic($className, $this->valueTypes)
		);
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		return $this->factory->createConstantArrayType(
			UnionTypeHelper::changeBaseClass($className, $this->keyTypes),
			UnionTypeHelper::changeBaseClass($className, $this->valueTypes)
		);
	}
}
