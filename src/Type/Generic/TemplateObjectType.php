<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

final class TemplateObjectType extends ObjectType implements TemplateType
{

	/** @var string */
	private $name;

	public function __construct(
		string $name,
		string $class,
		?Type $subtractedType = null
	)
	{
		parent::__construct($class, $subtractedType);

		$this->name = $name;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function describe(VerbosityLevel $level): string
	{
		return sprintf(
			'%s of %s',
			$this->name,
			parent::describe($level)
		);
	}

	public function equals(Type $type): bool
	{
		return $type instanceof self
			&& $type->name === $this->name;
	}

	public function inferTemplateTypes(Type $receivedType): TemplateTypeMap
	{
		if ($receivedType instanceof UnionType || $receivedType instanceof IntersectionType) {
			return $receivedType->inferTemplateTypesOn($this);
		}

		if (!$this->isSuperTypeOf($receivedType)->yes()) {
			return TemplateTypeMap::empty();
		}

		return new TemplateTypeMap([
			$this->name => $receivedType,
		]);
	}

	public function subtract(Type $type): Type
	{
		if ($this->getSubtractedType() !== null) {
			$type = TypeCombinator::union($this->getSubtractedType(), $type);
		}

		return new self(
			$this->name,
			$this->getClassName(),
			$type
		);
	}

	public function getTypeWithoutSubtractedType(): Type
	{
		return new self(
			$this->name,
			$this->getClassName(),
			null
		);
	}

	public function changeSubtractedType(?Type $subtractedType): Type
	{
		return new self(
			$this->name,
			$this->getClassName(),
			$subtractedType
		);
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self(
			$properties['name'],
			$properties['className'],
			$properties['subtractedType']
		);
	}

}
