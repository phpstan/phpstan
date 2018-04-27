<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\TrivialParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CompoundType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

/**
 * @method ConstantIntegerType|ConstantStringType getKeyType()
 */
class ConstantArrayType extends ArrayType implements ConstantType
{

	private const DESCRIBE_LIMIT = 8;

	/** @var (ConstantIntegerType|ConstantStringType)[] */
	private $keyTypes;

	/** @var Type[] */
	private $valueTypes;

	/** @var int */
	private $nextAutoIndex;

	/**
	 * @param (ConstantIntegerType|ConstantStringType)[] $keyTypes
	 * @param Type[] $valueTypes
	 * @param int $nextAutoIndex
	 */
	public function __construct(array $keyTypes, array $valueTypes, int $nextAutoIndex = 0)
	{
		assert(count($keyTypes) === count($valueTypes));

		parent::__construct(
			count($keyTypes) > 0 ? TypeCombinator::union(...$keyTypes) : new MixedType(),
			count($valueTypes) > 0 ? TypeCombinator::union(...array_map(function (Type $valueType): Type {
				if ($valueType instanceof self) {
					return $valueType->generalize();
				}

				return $valueType;
			}, $valueTypes)) : new MixedType(),
			true
		);

		$this->keyTypes = $keyTypes;
		$this->valueTypes = $valueTypes;
		$this->nextAutoIndex = $nextAutoIndex;
	}

	/**
	 * @return (ConstantIntegerType|ConstantStringType)[]
	 */
	public function getKeyTypes(): array
	{
		return $this->keyTypes;
	}

	/**
	 * @return Type[]
	 */
	public function getValueTypes(): array
	{
		return $this->valueTypes;
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof self) {
			if (count($this->keyTypes) !== count($type->keyTypes)) {
				return false;
			}

			foreach (array_keys($this->keyTypes) as $i) {
				if (!$this->keyTypes[$i]->accepts($type->keyTypes[$i])) {
					return false;
				}

				if (!$this->valueTypes[$i]->accepts($type->valueTypes[$i])) {
					return false;
				}
			}

			return true;
		}

		return false;
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
			return TrinaryLogic::createMaybe()->and(
				$this->getKeyType()->isSuperTypeOf($type->getKeyType()),
				$this->getItemType()->isSuperTypeOf($type->getItemType())
			);
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		return TrinaryLogic::createNo();
	}

	public function isCallable(): TrinaryLogic
	{
		$classAndMethod = $this->findClassNameAndMethod();
		if ($classAndMethod === null) {
			return TrinaryLogic::createNo();
		}

		[$className, $methodName] = $classAndMethod;
		if ($className === null && $methodName === null) {
			return TrinaryLogic::createMaybe();
		}

		return TrinaryLogic::createYes();
	}

	public function getCallableParametersAcceptor(Scope $scope): ParametersAcceptor
	{
		$classAndMethod = $this->findClassNameAndMethod();
		if ($classAndMethod === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		[$className, $methodName] = $classAndMethod;
		if ($className === null && $methodName === null) {
			return new TrivialParametersAcceptor();
		}

		$broker = Broker::getInstance();
		$classReflection = $broker->getClass($className);

		return $classReflection->getMethod($methodName, $scope);
	}

	/**
	 * @return string[]|null[]|null
	 */
	private function findClassNameAndMethod(): ?array
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

		$classOrObject = $this->valueTypes[0];
		$method = $this->valueTypes[1];

		if ($classOrObject instanceof ConstantStringType) {
			$className = $classOrObject->getValue();

		} elseif ($classOrObject instanceof TypeWithClassName) {
			$className = $classOrObject->getClassName();

		} else {
			return [null, null];
		}

		$broker = Broker::getInstance();
		if (!$broker->hasClass($className)) {
			return [null, null];
		}

		if (!($method instanceof ConstantStringType)) {
			return [null, null];
		}

		$methodName = $method->getValue();
		$classReflection = $broker->getClass($className);

		if (!$classReflection->hasMethod($methodName)) {
			if (!$classReflection->getNativeReflection()->isFinal()) {
				return [null, null];
			}

			return null;
		}

		return [$className, $methodName];
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
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
		if ($offsetType === null) {
			$offsetType = new ConstantIntegerType($this->nextAutoIndex);

		} else {
			$offsetType = parent::castToArrayKeyType($offsetType);
		}

		if ($offsetType instanceof ConstantIntegerType || $offsetType instanceof ConstantStringType) {
			foreach ($this->keyTypes as $i => $keyType) {
				if ($keyType->getValue() === $offsetType->getValue()) {
					$newValueTypes = $this->valueTypes;
					$newValueTypes[$i] = $valueType;
					return new self($this->keyTypes, $newValueTypes, $this->nextAutoIndex);
				}
			}

			$newKeyTypes = $this->keyTypes;
			$newKeyTypes[] = $offsetType;
			$newValueTypes = $this->valueTypes;
			$newValueTypes[] = $valueType;
			$newNextAutoIndex = $offsetType instanceof ConstantIntegerType
				? max($this->nextAutoIndex, $offsetType->getValue() + 1)
				: $this->nextAutoIndex;

			return new self($newKeyTypes, $newValueTypes, $newNextAutoIndex);
		}

		return parent::setOffsetValueType($offsetType, $valueType);
	}

	public function unsetOffset(Type $offsetType): self
	{
		$offsetType = parent::castToArrayKeyType($offsetType);
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

	public function toBoolean(): BooleanType
	{
		return new ConstantBooleanType(count($this->keyTypes) > 0);
	}

	public function generalize(): Type
	{
		return new ArrayType($this->getKeyType(), $this->getItemType(), true);
	}

	/**
	 * @return static
	 */
	public function generalizeValues(): ArrayType
	{
		$valueTypes = [];
		foreach ($this->valueTypes as $valueType) {
			if ($valueType instanceof ConstantType) {
				$valueType = $valueType->generalize();
			} elseif ($valueType instanceof UnionType) {
				$valueType = TypeCombinator::union(...array_map(function (Type $type): Type {
					if ($type instanceof ConstantType) {
						return $type->generalize();
					}

					return $type;
				}, $valueType->getTypes()));
			}

			$valueTypes[] = $valueType;
		}

		return new self($this->keyTypes, $valueTypes, $this->nextAutoIndex);
	}

	public function intersectWith(ArrayType $otherArray): ArrayType
	{
		if (!$otherArray instanceof self) {
			return parent::intersectWith($otherArray);
		}

		$newArray = new self([], []);
		foreach ($this->getKeyTypes() as $i => $keyType) {
			$otherValueType = $otherArray->getOffsetValueType($keyType);
			if ($otherValueType instanceof ErrorType) {
				continue;
			}
			$newArray = $newArray->setOffsetValueType($keyType, TypeCombinator::union(
				$this->valueTypes[$i],
				$otherValueType
			));
		}

		foreach ($otherArray->getKeyTypes() as $otherKeyType) {
			if (!$this->getOffsetValueType($otherKeyType) instanceof ErrorType) {
				continue;
			}

			$newArray = $newArray->unsetOffset($otherKeyType);
		}

		return $newArray;
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

	public function count(): int
	{
		return count($this->getKeyTypes());
	}

	public function describe(VerbosityLevel $level): string
	{
		return $level->handle(
			function () use ($level): string {
				return parent::describe($level);
			},
			function () use ($level): string {
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
				if (count($items) > self::DESCRIBE_LIMIT) {
					$items = array_slice($items, 0, self::DESCRIBE_LIMIT);
					$values = array_slice($values, 0, self::DESCRIBE_LIMIT);
					$append = ', ...';
				}

				return sprintf(
					'array(%s%s)',
					implode(', ', $exportValuesOnly ? $values : $items),
					$append
				);
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
