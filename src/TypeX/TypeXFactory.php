<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

use PHPStan\Broker\Broker;
use PHPStan\Type\Type;

class TypeXFactory
{
	/** @var Broker */
	private $broker;

	public function setBroker(Broker $broker)
	{
		assert($this->broker === null);
		$this->broker = $broker;
	}

	public function createErrorType(string $message = 'error'): ErrorType
	{
		return new ErrorType($this, $message);
	}

	public function createNeverType(): NeverType
	{
		return new NeverType($this);
	}

	public function createVoidType(): VoidType
	{
		return new VoidType($this);
	}

	public function createMixedType(): MixedType
	{
		return new MixedType($this);
	}

	public function createNullType(): NullType
	{
		return new NullType($this);
	}

	public function createTrueType(): ConstantBooleanType
	{
		return new ConstantBooleanType($this, true);
	}

	public function createFalseType(): ConstantBooleanType
	{
		return new ConstantBooleanType($this, false);
	}

	public function createBooleanType(): BooleanType
	{
		// return $this->createUnionType($this->createTrueType(), $this->createFalseType());
		return new BooleanType($this);
	}

	public function createConstantIntegerType(int $value): ConstantIntegerType
	{
		return new ConstantIntegerType($this, $value);
	}

	public function createIntegerType(): IntegerType
	{
		return new IntegerType($this);
	}

	public function createConstantFloatType(float $value): ConstantFloatType
	{
		return new ConstantFloatType($this, $value);
	}

	public function createFloatType(): FloatType
	{
		return new FloatType($this);
	}

	public function createConstantStringType(string $value): ConstantStringType
	{
		return new ConstantStringType($this, $this->broker, $value);
	}

	public function createStringType(): StringType
	{
		return new StringType($this);
	}

	public function createConstantArrayType(array $keyTypes, array $valueTypes): ConstantArrayType
	{
		return new ConstantArrayType($this, $this->broker, $keyTypes, $valueTypes);
	}

	public function createArrayType(TypeX $keyType = null, TypeX $valueType = null, bool $inferredFromLiteral = false): ArrayType
	{
		return new ArrayType(
			$this,
			$keyType ?? $this->createUnionType($this->createStringType(), $this->createIntegerType()),
			$valueType ?? $this->createMixedType(),
			$inferredFromLiteral
		);
	}

	public function createResourceType(): ResourceType
	{
		return new ResourceType($this);
	}

	public function createObjectType(string $className = NULL): ObjectType
	{
		return new ObjectType($this, $this->broker, $className);
	}

	public function createStaticType(string $baseClassName): StaticType
	{
		return new StaticType($this, $this->broker, $baseClassName);
	}

	public function createThisType(string $baseClassName): ThisType
	{
		return new ThisType($this, $this->broker, $baseClassName);
	}

	public function createIterableType(TypeX $keyType = null, TypeX $valueType = null): IterableType
	{
		return new IterableType(
			$this,
			$keyType ?? $this->createMixedType(),
			$valueType ?? $this->createMixedType()
		);
	}

	public function createCallableType(TypeX $returnType = null): CallableType
	{
		return new CallableType($this, $returnType ?? $this->createMixedType());
	}

	public function createUnionType(TypeX ...$types): TypeX
	{
		// transform A | (B | C) to A | B | C
		foreach ($types as $i => &$type) {
			if ($type instanceof UnionType) {
				array_splice($types, $i, 1, $type->getTypes());
			}
		}

		// remove void types
		for ($i = 0; $i < count($types); $i++) {
			if ($types[$i] instanceof VoidType) {
				array_splice($types, $i--, 1);
			}
		}

		// TODO: simplify 1 | 2 to int?

//		for ($i = 0; $i < count($types); $i++) {
//			for ($j = $i + 1; $j < count($types); $j++) {
//				if ($types[$j]->acceptsX($types[$i])) {
//					array_splice($types, $i--, 1);
//					continue 2;
//
//				} elseif ($types[$i]->acceptsX($types[$j])) {
//					array_splice($types, $j--, 1);
//					continue 1;
//				}
//			}
//		}

		// remove equal types
		for ($i = 0; $i < count($types); $i++) {
			for ($j = $i + 1; $j < count($types); $j++) {
				if ($types[$j]->acceptsX($types[$i]) && $types[$i]->acceptsX($types[$j])) {
					array_splice($types, $j--, 1);
				}
			}
		}

		if (count($types) === 0) {
			return $this->createMixedType();

		} elseif (count($types) === 1) {
			return $types[0];
		}

		return new UnionType($this, $types);
	}

	public function createIntersectionType(TypeX ...$types): TypeX
	{
		// transform A & (B | C) to (A & B) | (A & C)
		foreach ($types as $i => $type) {
			if ($type instanceof UnionType) {
				$topLevelUnionSubTypes = [];
				foreach ($type->getTypes() as $innerUnionSubType) {
					$topLevelUnionSubTypes[] = $this->createIntersectionType(
						$innerUnionSubType,
						...array_slice($types, 0, $i),
						...array_slice($types, $i + 1)
					);
				}

				return $this->createUnionType(...$topLevelUnionSubTypes);
			}
		}

		// transform IntegerType & ConstantIntegerType to ConstantIntegerType
		// transform Child & Parent to Child
		// transform Object & ~null to Object
		// transform A & A to A
		for ($i = 0; $i < count($types); $i++) {
			for ($j = $i + 1; $j < count($types); $j++) {
				if ($types[$j]->acceptsX($types[$i])) {
					array_splice($types, $j--, 1);
					continue 1;

				} elseif ($types[$i]->acceptsX($types[$j])) {
					array_splice($types, $i--, 1);
					continue 2;
				}
			}
		}

		// transform int[] & string to void
		// transform callable & int to void
		// transform A & ~A to void
		// transform int & string to void
		foreach ($types as $typeA) {
			if ($typeA instanceof ErrorType || $typeA instanceof NeverType) {
				// not sure what to do, ignoring

			} elseif ($typeA instanceof IterableType) {
				foreach ($types as $typeB) {
					if ($typeB->isIterable() === TypeX::RESULT_NO) {
						return $this->createVoidType();
					}
				}

			} elseif ($typeA instanceof CallableType) {
				foreach ($types as $typeB) {
					if ($typeB->isCallable() === TypeX::RESULT_NO) {
						return $this->createVoidType();
					}
				}

			} elseif ($typeA instanceof ObjectType) {
				$classRefA = $typeA->getClassRef();
				if ($classRefA !== null && !$classRefA->isInterface()) {
					foreach ($types as $typeB) {
						if ($typeB !== $typeA && $typeB instanceof ObjectType && !$typeB->getClassRef()->isInterface()) {
							return $this->createVoidType();
						}
					}
				}

			} elseif ($typeA instanceof ComplementType) {
				foreach ($types as $typeB) {
					if ($typeB->acceptsX($typeA->getInnerType()) && $typeA->getInnerType()->acceptsX($typeB)) {
						return $this->createVoidType();
					}
				}

			} elseif (count($types) > 1) {
				return $this->createVoidType();
			}
		}

		if (count($types) === 0) { // can this actually happen?
			return $this->createVoidType();

		} elseif (count($types) === 1) {
			return $types[0];

		} else {
			return new IntersectionType($this, $types);
		}
	}

	public function createComplementType(TypeX $type): TypeX
	{
		if ($type instanceof ComplementType) {
			return $type->getInnerType();
		}

		return new ComplementType($this, $type);
	}

	public function createFromLegacy(Type $type): TypeX
	{
		if ($type instanceof TypeX) {
			return $type;
		}

		if ($type instanceof \PHPStan\Type\VoidType) {
			$typeX = $this->createVoidType();

		} elseif ($type instanceof \PHPStan\Type\ResourceType) {
			$typeX = $this->createResourceType();

		} elseif ($type instanceof \PHPStan\Type\NullType) {
			$typeX = $this->createNullType();

		} elseif ($type instanceof \PHPStan\Type\FloatType) {
			$typeX = $this->createFloatType();

		} elseif ($type instanceof \PHPStan\Type\MixedType) {
			$typeX = $this->createMixedType();

		} elseif ($type instanceof \PHPStan\Type\StringType) {
			$typeX = $this->createStringType();

		} elseif ($type instanceof \PHPStan\Type\ObjectType) {
			$typeX = $this->createObjectType($type->getClass());

		} elseif ($type instanceof \PHPStan\Type\CallableType) {
			$typeX = $this->createCallableType($this->createMixedType());

		} elseif ($type instanceof \PHPStan\Type\IntegerType) {
			$typeX = $this->createIntegerType();

		} elseif ($type instanceof \PHPStan\Type\TrueBooleanType) {
			$typeX = $this->createTrueType();

		} elseif ($type instanceof \PHPStan\Type\FalseBooleanType) {
			$typeX = $this->createFalseType();

		} elseif ($type instanceof \PHPStan\Type\TrueOrFalseBooleanType) {
			$typeX = $this->createBooleanType();

		} elseif ($type instanceof \PHPStan\Type\StaticType) {
			$typeX = $this->createStaticType($type->getBaseClass());

		} elseif ($type instanceof \PHPStan\Type\ThisType) {
			$typeX = $this->createThisType($type->getBaseClass());

		} elseif ($type instanceof \PHPStan\Type\NonexistentParentClassType) {
			$typeX = $this->createErrorType(); // TODO: ???

		} elseif ($type instanceof \PHPStan\Type\ArrayType) {
			$valueTypeX = $this->createFromLegacy($type->getItemType());
			$keyTypeX = $this->createUnionType($this->createStringType(), $this->createIntegerType());
			$typeX = $this->createArrayType($keyTypeX, $valueTypeX);

			if ($type->isPossiblyCallable()) {
				$typeX = $this->createIntersectionType(
					$typeX,
					$this->createCallableType($this->createMixedType())
				);
			}

		} elseif ($type instanceof \PHPStan\Type\UnionIterableType) {
			$typeX = $this->createIntersectionType(
				$this->createUnionType(...array_map([$this, 'createFromLegacy'], $type->getTypes())),
				$this->createIterableType(
					$this->createMixedType(),
					$this->createFromLegacy($type->getItemType())
				)
			);

		} elseif ($type instanceof \PHPStan\Type\CommonUnionType) {
			$typeX = $this->createUnionType(...array_map([$this, 'createFromLegacy'], $type->getTypes()));

		} elseif ($type instanceof \PHPStan\Type\IterableIterableType) {
			$typeX = $this->createIterableType(
				$this->createMixedType(),
				$this->createFromLegacy($type->getItemType())
			);

		} else {
			$typeX = $this->createMixedType();
		}

		return $typeX;
	}
}
