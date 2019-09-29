<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\CompoundType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class GenericClassStringType extends ClassStringType
{

	/** @var Type */
	private $type;

	public function __construct(Type $type)
	{
		$this->type = $type;
	}

	public function getGenericType(): Type
	{
		return $this->type;
	}

	public function describe(VerbosityLevel $level): string
	{
		return sprintf('%s<%s>', parent::describe($level), $this->type->describe($level));
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		if ($type instanceof CompoundType) {
			return $type->isAcceptedBy($this, $strictTypes);
		}

		if ($type instanceof ConstantStringType) {
			$objectType = new ObjectType($type->getValue());
		} elseif ($type instanceof self) {
			$objectType = $type->type;
		} else {
			return TrinaryLogic::createNo();
		}

		return $this->type->accepts($objectType, $strictTypes);
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		if ($type instanceof ConstantStringType) {
			$objectType = new ObjectType($type->getValue());
			$isSuperType = $this->type->isSuperTypeOf($objectType);
			if (!$isSuperType->yes()) {
				return TrinaryLogic::createNo();
			}

			return $isSuperType;
		} elseif ($type instanceof self) {
			return $this->type->isSuperTypeOf($type->type);
		} elseif ($type instanceof StringType) {
			return TrinaryLogic::createMaybe();
		}

		return TrinaryLogic::createNo();
	}

	public function traverse(callable $cb): Type
	{
		$newType = $cb($this->type);
		if ($newType === $this->type) {
			return $this;
		}

		return new self($newType);
	}

	public function inferTemplateTypes(Type $receivedType): TemplateTypeMap
	{
		if ($receivedType instanceof UnionType || $receivedType instanceof IntersectionType) {
			return $receivedType->inferTemplateTypesOn($this);
		}

		if ($receivedType instanceof ConstantStringType) {
			$typeToInfer = new ObjectType($receivedType->getValue());
		} elseif ($receivedType instanceof self) {
			$typeToInfer = $receivedType->type;
		} else {
			return TemplateTypeMap::createEmpty();
		}

		if (!$this->type->isSuperTypeOf($typeToInfer)->no()) {
			return $this->type->inferTemplateTypes($typeToInfer);
		}

		return TemplateTypeMap::createEmpty();
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self($properties['type']);
	}

}
