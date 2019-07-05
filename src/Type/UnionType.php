<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Generic\TemplateTypeMap;

class UnionType implements CompoundType, StaticResolvableType
{

	/** @var \PHPStan\Type\Type[] */
	private $types;

	/**
	 * @param Type[] $types
	 */
	public function __construct(array $types)
	{
		$throwException = static function () use ($types): void {
			throw new \PHPStan\ShouldNotHappenException(sprintf(
				'Cannot create %s with: %s',
				self::class,
				implode(', ', array_map(static function (Type $type): string {
					return $type->describe(VerbosityLevel::value());
				}, $types))
			));
		};
		if (count($types) < 2) {
			$throwException();
		}
		foreach ($types as $type) {
			if (!($type instanceof UnionType)) {
				continue;
			}

			$throwException();
		}
		$this->types = UnionTypeHelper::sortTypes($types);
	}

	/**
	 * @return \PHPStan\Type\Type[]
	 */
	public function getTypes(): array
	{
		return $this->types;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return UnionTypeHelper::getReferencedClasses($this->getTypes());
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		if ($type instanceof CompoundType && !$type instanceof CallableType) {
			return CompoundTypeHelper::accepts($type, $this, $strictTypes);
		}

		$results = [];
		foreach ($this->getTypes() as $innerType) {
			$results[] = $innerType->accepts($type, $strictTypes);
		}

		return TrinaryLogic::createNo()->or(...$results);
	}

	public function isSuperTypeOf(Type $otherType): TrinaryLogic
	{
		if ($otherType instanceof self || $otherType instanceof IterableType) {
			return $otherType->isSubTypeOf($this);
		}

		$results = [];
		foreach ($this->getTypes() as $innerType) {
			$results[] = $innerType->isSuperTypeOf($otherType);
		}

		return TrinaryLogic::createNo()->or(...$results);
	}

	public function isSubTypeOf(Type $otherType): TrinaryLogic
	{
		$results = [];
		foreach ($this->getTypes() as $innerType) {
			$results[] = $otherType->isSuperTypeOf($innerType);
		}

		return TrinaryLogic::extremeIdentity(...$results);
	}

	public function isAcceptedBy(Type $acceptingType, bool $strictTypes): TrinaryLogic
	{
		$results = [];
		foreach ($this->getTypes() as $innerType) {
			$results[] = $acceptingType->accepts($innerType, $strictTypes);
		}

		return TrinaryLogic::extremeIdentity(...$results);
	}

	public function equals(Type $type): bool
	{
		if (!$type instanceof static) {
			return false;
		}

		if (count($this->types) !== count($type->types)) {
			return false;
		}

		foreach ($this->types as $i => $innerType) {
			if (!$innerType->equals($type->types[$i])) {
				return false;
			}
		}

		return true;
	}

	public function describe(VerbosityLevel $level): string
	{
		$joinTypes = static function (array $types) use ($level): string {
			$typeNames = [];
			foreach ($types as $type) {
				if ($type instanceof IntersectionType || $type instanceof ClosureType || $type instanceof CallableType) {
					$typeNames[] = sprintf('(%s)', $type->describe($level));
				} else {
					$typeNames[] = $type->describe($level);
				}
			}

			return implode('|', $typeNames);
		};

		return $level->handle(
			function () use ($joinTypes): string {
				$types = TypeCombinator::union(...array_map(static function (Type $type): Type {
					if (
						$type instanceof ConstantType
						&& !$type instanceof ConstantBooleanType
					) {
						return $type->generalize();
					}

					return $type;
				}, $this->types));

				if ($types instanceof UnionType) {
					return $joinTypes($types->getTypes());
				}

				return $joinTypes([$types]);
			},
			function () use ($joinTypes): string {
				$arrayDescription = [];
				$constantArrays = [];
				$commonTypes = [];

				foreach ($this->types as $type) {
					if (!$type instanceof ConstantArrayType) {
						$commonTypes[] = $type;
						continue;
					}

					$constantArrays[] = $type;
					foreach ($type->getKeyTypes() as $i => $keyType) {
						if (!isset($arrayDescription[$keyType->getValue()])) {
							$arrayDescription[$keyType->getValue()] = [
								'key' => $keyType,
								'value' => $type->getValueTypes()[$i],
								'count' => 1,
							];
							continue;
						}

						$arrayDescription[$keyType->getValue()] = [
							'key' => $keyType,
							'value' => TypeCombinator::union(
								$arrayDescription[$keyType->getValue()]['value'],
								$type->getValueTypes()[$i]
							),
							'count' => $arrayDescription[$keyType->getValue()]['count'] + 1,
						];
					}
				}

				$someKeyCountIsHigherThanOne = false;
				foreach ($arrayDescription as $value) {
					if ($value['count'] > 1) {
						$someKeyCountIsHigherThanOne = true;
						break;
					}
				}

				if (!$someKeyCountIsHigherThanOne) {
					return $joinTypes(UnionTypeHelper::sortTypes(array_merge(
						$commonTypes,
						$constantArrays
					)));
				}

				$constantArraysCount = count($constantArrays);
				$constantArraysDescriptions = [];
				foreach ($arrayDescription as $value) {
					$constantArraysDescriptions[] = sprintf(
						'%s%s => %s',
						$value['count'] < $constantArraysCount ? '?' : '',
						$value['key']->describe(VerbosityLevel::value()),
						$value['value']->describe(VerbosityLevel::value())
					);
				}

				$description = '';
				if (count($commonTypes) > 0) {
					$description = $joinTypes($commonTypes);
					if (count($constantArraysDescriptions) > 0) {
						$description .= '|';
					}
				}

				if (count($constantArraysDescriptions) > 0) {
					$description .= 'array(' . implode(', ', $constantArraysDescriptions) . ')';
				}

				return $description;
			}
		);
	}

	/**
	 * @param callable(Type $type): TrinaryLogic $canCallback
	 * @param callable(Type $type): TrinaryLogic $hasCallback
	 * @return TrinaryLogic
	 */
	private function hasInternal(
		callable $canCallback,
		callable $hasCallback
	): TrinaryLogic
	{
		$results = [];
		foreach ($this->types as $type) {
			if ($canCallback($type)->no()) {
				$results[] = TrinaryLogic::createNo();
				continue;
			}
			$results[] = $hasCallback($type);
		}

		return TrinaryLogic::extremeIdentity(...$results);
	}

	/**
	 * @param callable(Type $type): TrinaryLogic $hasCallback
	 * @param callable(Type $type): object $getCallback
	 * @return object
	 */
	private function getInternal(
		callable $hasCallback,
		callable $getCallback
	)
	{
		/** @var TrinaryLogic|null $result */
		$result = null;

		/** @var object|null $object */
		$object = null;
		foreach ($this->types as $type) {
			$has = $hasCallback($type);
			if (!$has->yes()) {
				continue;
			}
			if ($result !== null && $result->compareTo($has) !== $has) {
				continue;
			}

			$get = $getCallback($type);
			$result = $has;
			$object = $get;
		}

		if ($object === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $object;
	}

	public function canAccessProperties(): TrinaryLogic
	{
		return $this->unionResults(static function (Type $type): TrinaryLogic {
			return $type->canAccessProperties();
		});
	}

	public function hasProperty(string $propertyName): TrinaryLogic
	{
		return $this->unionResults(static function (Type $type) use ($propertyName): TrinaryLogic {
			return $type->hasProperty($propertyName);
		});
	}

	public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope): PropertyReflection
	{
		return $this->getInternal(
			static function (Type $type) use ($propertyName): TrinaryLogic {
				return $type->hasProperty($propertyName);
			},
			static function (Type $type) use ($propertyName, $scope): PropertyReflection {
				return $type->getProperty($propertyName, $scope);
			}
		);
	}

	public function canCallMethods(): TrinaryLogic
	{
		return $this->unionResults(static function (Type $type): TrinaryLogic {
			return $type->canCallMethods();
		});
	}

	public function hasMethod(string $methodName): TrinaryLogic
	{
		return $this->unionResults(static function (Type $type) use ($methodName): TrinaryLogic {
			return $type->hasMethod($methodName);
		});
	}

	public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): MethodReflection
	{
		return $this->getInternal(
			static function (Type $type) use ($methodName): TrinaryLogic {
				return $type->hasMethod($methodName);
			},
			static function (Type $type) use ($methodName, $scope): MethodReflection {
				return $type->getMethod($methodName, $scope);
			}
		);
	}

	public function canAccessConstants(): TrinaryLogic
	{
		return $this->unionResults(static function (Type $type): TrinaryLogic {
			return $type->canAccessConstants();
		});
	}

	public function hasConstant(string $constantName): TrinaryLogic
	{
		return $this->hasInternal(
			static function (Type $type): TrinaryLogic {
				return $type->canAccessConstants();
			},
			static function (Type $type) use ($constantName): TrinaryLogic {
				return $type->hasConstant($constantName);
			}
		);
	}

	public function getConstant(string $constantName): ConstantReflection
	{
		return $this->getInternal(
			static function (Type $type) use ($constantName): TrinaryLogic {
				return $type->hasConstant($constantName);
			},
			static function (Type $type) use ($constantName): ConstantReflection {
				return $type->getConstant($constantName);
			}
		);
	}

	public function resolveStatic(string $className): Type
	{
		return new self(UnionTypeHelper::resolveStatic($className, $this->getTypes()));
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		return new self(UnionTypeHelper::changeBaseClass($className, $this->getTypes()));
	}

	public function isIterable(): TrinaryLogic
	{
		return $this->unionResults(static function (Type $type): TrinaryLogic {
			return $type->isIterable();
		});
	}

	public function isIterableAtLeastOnce(): TrinaryLogic
	{
		return $this->unionResults(static function (Type $type): TrinaryLogic {
			return $type->isIterableAtLeastOnce();
		});
	}

	public function getIterableKeyType(): Type
	{
		return $this->unionTypes(static function (Type $type): Type {
			return $type->getIterableKeyType();
		});
	}

	public function getIterableValueType(): Type
	{
		return $this->unionTypes(static function (Type $type): Type {
			return $type->getIterableValueType();
		});
	}

	public function isArray(): TrinaryLogic
	{
		return $this->unionResults(static function (Type $type): TrinaryLogic {
			return $type->isArray();
		});
	}

	public function isOffsetAccessible(): TrinaryLogic
	{
		return $this->unionResults(static function (Type $type): TrinaryLogic {
			return $type->isOffsetAccessible();
		});
	}

	public function hasOffsetValueType(Type $offsetType): TrinaryLogic
	{
		return $this->unionResults(static function (Type $type) use ($offsetType): TrinaryLogic {
			return $type->hasOffsetValueType($offsetType);
		});
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		$types = [];
		foreach ($this->types as $innerType) {
			$valueType = $innerType->getOffsetValueType($offsetType);
			if ($valueType instanceof ErrorType) {
				continue;
			}

			$types[] = $valueType;
		}

		if (count($types) === 0) {
			return new ErrorType();
		}

		return TypeCombinator::union(...$types);
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		return $this->unionTypes(static function (Type $type) use ($offsetType, $valueType): Type {
			return $type->setOffsetValueType($offsetType, $valueType);
		});
	}

	public function isCallable(): TrinaryLogic
	{
		return $this->unionResults(static function (Type $type): TrinaryLogic {
			return $type->isCallable();
		});
	}

	/**
	 * @param \PHPStan\Reflection\ClassMemberAccessAnswerer $scope
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getCallableParametersAcceptors(ClassMemberAccessAnswerer $scope): array
	{
		foreach ($this->types as $type) {
			if ($type->isCallable()->no()) {
				continue;
			}

			return $type->getCallableParametersAcceptors($scope);
		}

		throw new \PHPStan\ShouldNotHappenException();
	}

	public function isCloneable(): TrinaryLogic
	{
		return $this->unionResults(static function (Type $type): TrinaryLogic {
			return $type->isCloneable();
		});
	}

	public function toBoolean(): BooleanType
	{
		/** @var BooleanType $type */
		$type = $this->unionTypes(static function (Type $type): BooleanType {
			return $type->toBoolean();
		});

		return $type;
	}

	public function toNumber(): Type
	{
		$type = $this->unionTypes(static function (Type $type): Type {
			return $type->toNumber();
		});

		return $type;
	}

	public function toString(): Type
	{
		$type = $this->unionTypes(static function (Type $type): Type {
			return $type->toString();
		});

		return $type;
	}

	public function toInteger(): Type
	{
		$type = $this->unionTypes(static function (Type $type): Type {
			return $type->toInteger();
		});

		return $type;
	}

	public function toFloat(): Type
	{
		$type = $this->unionTypes(static function (Type $type): Type {
			return $type->toFloat();
		});

		return $type;
	}

	public function toArray(): Type
	{
		$type = $this->unionTypes(static function (Type $type): Type {
			return $type->toArray();
		});

		return $type;
	}

	public function inferTemplateTypes(Type $receivedType): TemplateTypeMap
	{
		$types = TemplateTypeMap::empty();

		foreach ($this->types as $type) {
			$receive = $type->isSuperTypeOf($receivedType)->yes() ? $receivedType : new NeverType();
			$types = $types->union($type->inferTemplateTypes($receive));
		}

		return $types;
	}

	public function inferTemplateTypesOn(Type $templateType): TemplateTypeMap
	{
		$types = TemplateTypeMap::empty();

		foreach ($this->types as $type) {
			$types = $types->union($templateType->inferTemplateTypes($type));
		}

		return $types;
	}

	public function traverse(callable $cb): Type
	{
		$types = [];
		$changed = false;

		foreach ($this->types as $type) {
			$newType = $cb($type);
			if ($type !== $newType) {
				$changed = true;
			}
			$types[] = $newType;
		}

		if ($changed) {
			return TypeCombinator::union(...$types);
		}

		return $this;
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self($properties['types']);
	}

	/**
	 * @param callable(Type $type): TrinaryLogic $getResult
	 * @return TrinaryLogic
	 */
	private function unionResults(callable $getResult): TrinaryLogic
	{
		return TrinaryLogic::extremeIdentity(...array_map($getResult, $this->types));
	}

	/**
	 * @param callable(Type $type): Type $getType
	 * @return Type
	 */
	protected function unionTypes(callable $getType): Type
	{
		return TypeCombinator::union(...array_map($getType, $this->types));
	}

}
