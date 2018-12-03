<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\InaccessibleMethod;
use PHPStan\Reflection\TrivialParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CompoundType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class ConstantArrayType extends ArrayType implements ConstantType
{

	private const DESCRIBE_LIMIT = 8;

	/** @var array<int, ConstantIntegerType|ConstantStringType> */
	private $keyTypes;

	/** @var array<int, Type> */
	private $valueTypes;

	/** @var int */
	private $nextAutoIndex;

	/**
	 * @param array<int, ConstantIntegerType|ConstantStringType> $keyTypes
	 * @param array<int, Type> $valueTypes
	 * @param int $nextAutoIndex
	 */
	public function __construct(array $keyTypes, array $valueTypes, int $nextAutoIndex = 0)
	{
		assert(count($keyTypes) === count($valueTypes));

		parent::__construct(
			count($keyTypes) > 0 ? TypeCombinator::union(...$keyTypes) : new NeverType(),
			count($valueTypes) > 0 ? TypeCombinator::union(...$valueTypes) : new NeverType()
		);

		$this->keyTypes = $keyTypes;
		$this->valueTypes = $valueTypes;
		$this->nextAutoIndex = $nextAutoIndex;
	}

	public function getNextAutoIndex(): int
	{
		return $this->nextAutoIndex;
	}

	public function getKeyType(): Type
	{
		if (count($this->keyTypes) > 1) {
			return new UnionType($this->keyTypes);
		}

		return parent::getKeyType();
	}

	/**
	 * @return array<int, ConstantIntegerType|ConstantStringType>
	 */
	public function getKeyTypes(): array
	{
		return $this->keyTypes;
	}

	/**
	 * @return array<int, Type>
	 */
	public function getValueTypes(): array
	{
		return $this->valueTypes;
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		if ($type instanceof self) {
			if (count($this->keyTypes) !== count($type->keyTypes)) {
				return TrinaryLogic::createNo();
			}

			$result = TrinaryLogic::createYes();
			foreach (array_keys($this->keyTypes) as $i) {
				$result = $result
					->and($this->keyTypes[$i]->accepts($type->keyTypes[$i], $strictTypes))
					->and($this->valueTypes[$i]->accepts($type->valueTypes[$i], $strictTypes));
			}

			return $result;
		}

		return TrinaryLogic::createNo();
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof self) {
			if (count($this->keyTypes) !== count($type->keyTypes)) {
				return TrinaryLogic::createNo();
			}

			$results = [];
			foreach (array_keys($this->keyTypes) as $i) {
				$results[] = $this->keyTypes[$i]->isSuperTypeOf($type->keyTypes[$i]);
				$results[] = $this->valueTypes[$i]->isSuperTypeOf($type->valueTypes[$i]);
			}

			return TrinaryLogic::createYes()->and(...$results);
		}

		if ($type instanceof ArrayType) {
			$result = TrinaryLogic::createMaybe();
			if (count($this->keyTypes) === 0) {
				return $result;
			}

			return $result->and(
				$this->getKeyType()->isSuperTypeOf($type->getKeyType()),
				$this->getItemType()->isSuperTypeOf($type->getItemType())
			);
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		return TrinaryLogic::createNo();
	}

	public function equals(Type $type): bool
	{
		if (!$type instanceof self) {
			return false;
		}

		if (count($this->keyTypes) !== count($type->keyTypes)) {
			return false;
		}

		foreach ($this->keyTypes as $i => $keyType) {
			$valueType = $this->valueTypes[$i];
			if (!$valueType->equals($type->valueTypes[$i])) {
				return false;
			}
			if (!$keyType->equals($type->keyTypes[$i])) {
				return false;
			}
		}

		return true;
	}

	public function isCallable(): TrinaryLogic
	{
		$typeAndMethod = $this->findTypeAndMethodName();
		if ($typeAndMethod === null) {
			return TrinaryLogic::createNo();
		}

		if ($typeAndMethod->isUnknown()) {
			return TrinaryLogic::createMaybe();
		}

		return TrinaryLogic::createYes();
	}

	/**
	 * @param \PHPStan\Reflection\ClassMemberAccessAnswerer $scope
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getCallableParametersAcceptors(ClassMemberAccessAnswerer $scope): array
	{
		$typeAndMethodName = $this->findTypeAndMethodName();
		if ($typeAndMethodName === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		if ($typeAndMethodName->isUnknown()) {
			return [new TrivialParametersAcceptor()];
		}

		$method = $typeAndMethodName->getType()
			->getMethod($typeAndMethodName->getMethod(), $scope);

		if (!$scope->canCallMethod($method)) {
			return [new InaccessibleMethod($method)];
		}

		return $method->getVariants();
	}

	private function findTypeAndMethodName(): ?ConstantArrayTypeAndMethod
	{
		if (count($this->keyTypes) !== 2) {
			return null;
		}

		if ($this->keyTypes[0]->isSuperTypeOf(new ConstantIntegerType(0))->no()) {
			return null;
		}

		if ($this->keyTypes[1]->isSuperTypeOf(new ConstantIntegerType(1))->no()) {
			return null;
		}

		[$classOrObject, $method] = $this->valueTypes;

		if (!$method instanceof ConstantStringType) {
			return ConstantArrayTypeAndMethod::createUnknown();
		}

		if ($classOrObject instanceof ConstantStringType) {
			$broker = Broker::getInstance();
			if (!$broker->hasClass($classOrObject->getValue())) {
				return ConstantArrayTypeAndMethod::createUnknown();
			}
			$type = new ObjectType($broker->getClass($classOrObject->getValue())->getName());
		} elseif ((new \PHPStan\Type\ObjectWithoutClassType())->isSuperTypeOf($classOrObject)->yes()) {
			$type = $classOrObject;
		} else {
			return ConstantArrayTypeAndMethod::createUnknown();
		}

		if ($type->hasMethod($method->getValue())) {
			return ConstantArrayTypeAndMethod::createConcrete($type, $method->getValue());
		}

		return null;
	}

	public function hasOffsetValueType(Type $offsetType): TrinaryLogic
	{
		if ($offsetType instanceof UnionType) {
			$results = [];
			foreach ($offsetType->getTypes() as $innerType) {
				$results[] = $this->hasOffsetValueType($innerType);
			}

			return TrinaryLogic::extremeIdentity(...$results);
		}

		return $this->getKeyType()->isSuperTypeOf($offsetType);
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		$offsetType = ArrayType::castToArrayKeyType($offsetType);
		$matchingValueTypes = [];
		foreach ($this->keyTypes as $i => $keyType) {
			if ($keyType->isSuperTypeOf($offsetType)->no()) {
				continue;
			}

			$matchingValueTypes[] = $this->valueTypes[$i];
		}

		if (count($matchingValueTypes) > 0) {
			$type = TypeCombinator::union(...$matchingValueTypes);
			if ($type instanceof ErrorType) {
				return new MixedType();
			}

			return $type;
		}

		return new ErrorType(); // undefined offset
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		$builder = ConstantArrayTypeBuilder::createFromConstantArray($this);
		$builder->setOffsetValueType($offsetType, $valueType);

		return $builder->getArray();
	}

	public function unsetOffset(Type $offsetType): self
	{
		$offsetType = ArrayType::castToArrayKeyType($offsetType);
		if ($offsetType instanceof ConstantIntegerType || $offsetType instanceof ConstantStringType) {
			foreach ($this->keyTypes as $i => $keyType) {
				if ($keyType->getValue() === $offsetType->getValue()) {
					$newKeyTypes = $this->keyTypes;
					unset($newKeyTypes[$i]);
					$newValueTypes = $this->valueTypes;
					unset($newValueTypes[$i]);
					return new self(array_values($newKeyTypes), array_values($newValueTypes), $this->nextAutoIndex);
				}
			}
		}

		return $this;
	}

	public function removeLast(): self
	{
		if (count($this->keyTypes) === 0) {
			return $this;
		}

		$keyTypes = $this->keyTypes;
		$valueTypes = $this->valueTypes;

		$removedKeyType = array_pop($keyTypes);
		array_pop($valueTypes);
		$nextAutoindex = $removedKeyType instanceof ConstantIntegerType
			? $removedKeyType->getValue()
			: $this->nextAutoIndex;

		return new self(
			$keyTypes,
			$valueTypes,
			$nextAutoindex
		);
	}

	public function removeFirst(): ArrayType
	{
		$builder = ConstantArrayTypeBuilder::createEmpty();
		foreach ($this->keyTypes as $i => $keyType) {
			if ($i === 0) {
				continue;
			}

			$valueType = $this->valueTypes[$i];
			if ($keyType instanceof ConstantIntegerType) {
				$keyType = null;
			}

			$builder->setOffsetValueType($keyType, $valueType);
		}

		return $builder->getArray();
	}

	public function getFirstValueType(): Type
	{
		if (count($this->valueTypes) === 0) {
			return new NullType();
		}

		return $this->valueTypes[0];
	}

	public function getLastValueType(): Type
	{
		$length = count($this->valueTypes);
		if ($length === 0) {
			return new NullType();
		}

		return $this->valueTypes[$length - 1];
	}

	public function getFirstKeyType(): Type
	{
		if (count($this->keyTypes) === 0) {
			return new NullType();
		}

		return $this->keyTypes[0];
	}

	public function getLastKeyType(): Type
	{
		$length = count($this->keyTypes);
		if ($length === 0) {
			return new NullType();
		}

		return $this->keyTypes[$length - 1];
	}

	public function toBoolean(): BooleanType
	{
		return new ConstantBooleanType(count($this->keyTypes) > 0);
	}

	public function generalize(): Type
	{
		return new ArrayType(
			TypeUtils::generalizeType($this->getKeyType()),
			$this->getItemType()
		);
	}

	/**
	 * @return static
	 */
	public function generalizeValues(): ArrayType
	{
		$valueTypes = [];
		foreach ($this->valueTypes as $valueType) {
			$valueTypes[] = TypeUtils::generalizeType($valueType);
		}

		return new self($this->keyTypes, $valueTypes, $this->nextAutoIndex);
	}

	/**
	 * @return static
	 */
	public function getKeysArray(): ArrayType
	{
		$keyTypes = [];
		$valueTypes = [];
		$autoIndex = 0;

		foreach ($this->keyTypes as $i => $keyType) {
			$keyTypes[] = new ConstantIntegerType($i);
			$valueTypes[] = $keyType;
			$autoIndex++;
		}

		return new self($keyTypes, $valueTypes, $autoIndex);
	}

	/**
	 * @return static
	 */
	public function getValuesArray(): ArrayType
	{
		$keyTypes = [];
		$valueTypes = [];
		$autoIndex = 0;

		foreach ($this->valueTypes as $i => $valueType) {
			$keyTypes[] = new ConstantIntegerType($i);
			$valueTypes[] = $valueType;
			$autoIndex++;
		}

		return new self($keyTypes, $valueTypes, $autoIndex);
	}

	public function count(): Type
	{
		return new ConstantIntegerType(count($this->getKeyTypes()));
	}

	public function describe(VerbosityLevel $level): string
	{
		$describeValue = function (bool $truncate) use ($level): string {
			$items = [];
			$values = [];
			$exportValuesOnly = true;
			foreach ($this->keyTypes as $i => $keyType) {
				$valueType = $this->valueTypes[$i];
				if ($keyType->getValue() !== $i) {
					$exportValuesOnly = false;
				}

				$items[] = sprintf('%s => %s', var_export($keyType->getValue(), true), $valueType->describe($level));
				$values[] = $valueType->describe($level);
			}

			$append = '';
			if ($truncate && count($items) > self::DESCRIBE_LIMIT) {
				$items = array_slice($items, 0, self::DESCRIBE_LIMIT);
				$values = array_slice($values, 0, self::DESCRIBE_LIMIT);
				$append = ', ...';
			}

			return sprintf(
				'array(%s%s)',
				implode(', ', $exportValuesOnly ? $values : $items),
				$append
			);
		};
		return $level->handle(
			function () use ($level): string {
				return parent::describe($level);
			},
			static function () use ($describeValue): string {
				return $describeValue(true);
			},
			static function () use ($describeValue): string {
				return $describeValue(false);
			}
		);
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self($properties['keyTypes'], $properties['valueTypes'], $properties['nextAutoIndex']);
	}

}
