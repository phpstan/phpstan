<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

use Nette\Utils\Arrays;
use PHPStan\Type\Type;


abstract class BaseTypeX implements TypeX
{
	/** @var TypeXFactory */
	protected $factory;

	public function __construct(TypeXFactory $factory)
	{
		$this->factory = $factory;
	}

	/**
	 * @return string|null
	 */
	public function getClass()
	{
		if ($this instanceof ObjectType) {
			return $this->getClassName();

		} elseif ($this instanceof UnionType || $this instanceof IntersectionType) {
			$subClasses = [];
			foreach ($this->getTypes() as $subType) {
				$subClasses[] = $subType->getClass();
			}
			$subClasses = array_unique(array_filter($subClasses));
			return count($subClasses) === 1 ? reset($subClasses) : null;
		}

		return null;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		$toClasses = function (TypeX ...$types) {
			return Arrays::flatten(Arrays::map($types, function (TypeX $type) {
				return $type->getReferencedClasses();
			}));
		};

		if ($this instanceof ObjectType && $this->getClassName() !== null) {
			return [$this->getClassName()];

		} elseif ($this instanceof ArrayType) {
			return $toClasses($this->getKeyType(), $this->getValueType());

		} elseif ($this instanceof IterableType) {
			return $toClasses($this->getIterableKeyType(), $this->getIterableValueType());

		} elseif ($this instanceof CallableType) {
			return $this->getCallReturnType()->getReferencedClasses();

		} elseif ($this instanceof UnionType || $this instanceof IntersectionType) {
			return $toClasses(...$this->getTypes());

		} elseif ($this instanceof ComplementType) {
			return $this->getInnerType()->getReferencedClasses();

		} else {
			return [];
		}
	}

	/**
	 * @deprecated
	 */
	public function combineWith(Type $otherType): Type
	{
		$otherTypeX = $this->factory->createFromLegacy($otherType);
		return $this->factory->createUnionType($this, $otherTypeX);
	}

	public function intersectWith(Type $otherType): TypeX
	{
		$otherTypeX = $this->factory->createFromLegacy($otherType);
		return $this->factory->createIntersectionType($this, $otherTypeX);
	}

	public function remove(Type $otherType): Type
	{
		$otherTypeX = $this->factory->createFromLegacy($otherType);
		return $this->combineWith($this->factory->createComplementType($otherTypeX));
	}

	public function accepts(Type $otherType): bool
	{
		$otherTypeX = $this->factory->createFromLegacy($otherType);
		return $this->acceptsX($otherTypeX) || $this->acceptsCompound($otherTypeX);
	}

	public function canAccessProperties(): bool
	{
		return $this->canAccessPropertiesX() === self::RESULT_YES;
	}

	public function canCallMethods(): bool
	{
		return $this->canCallMethodsX() === self::RESULT_YES;
	}

	public function isDocumentableNatively(): bool
	{
		return $this instanceof VoidType
			|| $this instanceof NullType
			|| $this instanceof ConstantBooleanType
			|| $this instanceof BooleanType
			|| $this instanceof ConstantIntegerType
			|| $this instanceof IntegerType
			|| $this instanceof ConstantFloatType
			|| $this instanceof FloatType
			|| $this instanceof ConstantStringType
			|| $this instanceof StringType
			|| $this instanceof ConstantArrayType
			|| $this instanceof ArrayType
			|| $this instanceof ObjectType
			|| $this instanceof IterableType
			|| $this instanceof CallableType;
	}

	public function isItemTypeInferredFromLiteralArray(): bool
	{
		return $this instanceof ArrayType && $this->isInferredFromLiteral();
	}

	public function getItemType(): TypeX
	{
		return $this->getIterableValueType();
	}

	public function getOffsetValueType(TypeX $offsetType): TypeX
	{
		if ($this->canAccessOffset() !== self::RESULT_NO) {
			return $this->factory->createMixedType(); // TODO!

		} else {
			return $this->factory->createErrorType();
		}
	}

	public function setOffsetValueType(TypeX $offsetType = null, TypeX $valueType): TypeX
	{
		return $this; // TODO!
	}

	protected function acceptsCompound(TypeX $otherType): bool
	{
		if ($otherType instanceof UnionType) {
			return Arrays::every($otherType->getTypes(), [$this, 'acceptsX']);

		} elseif ($otherType instanceof IntersectionType) {
			return Arrays::some($otherType->getTypes(), [$this, 'acceptsX']);

		} else {
			return false;
		}
	}
}
