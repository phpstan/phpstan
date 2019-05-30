<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

final class TemplateMixedType extends MixedType implements TemplateType
{

	/** @var string */
	private $name;

	public function __construct(
		string $name,
		bool $isExplicitMixed = false,
		?Type $subtractedType = null
	)
	{
		parent::__construct($isExplicitMixed, $subtractedType);

		$this->name = $name;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function describe(VerbosityLevel $level): string
	{
		return $this->name;
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
		if ($type instanceof self) {
			return new NeverType();
		}
		if ($this->getSubtractedType() !== null) {
			$type = TypeCombinator::union($this->getSubtractedType(), $type);
		}

		return new static(
			$this->name,
			$this->isExplicitMixed(),
			$type
		);
	}

	public function getTypeWithoutSubtractedType(): Type
	{
		return new self(
			$this->name,
			$this->isExplicitMixed(),
			null
		);
	}

	public function changeSubtractedType(?Type $subtractedType): Type
	{
		return new self(
			$this->name,
			$this->isExplicitMixed(),
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
			$properties['isExplicitMixed'],
			$properties['subtractedType']
		);
	}

}
