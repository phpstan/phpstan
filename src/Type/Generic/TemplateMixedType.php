<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

final class TemplateMixedType extends MixedType implements TemplateType
{

	/** @var TemplateTypeScope */
	private $scope;

	/** @var string */
	private $name;

	/** @var TemplateTypeStrategy */
	private $strategy;

	public function __construct(
		TemplateTypeScope $scope,
		string $name,
		bool $isExplicitMixed = false,
		?Type $subtractedType = null,
		?TemplateTypeStrategy $templateTypeStrategy = null
	)
	{
		parent::__construct($isExplicitMixed, $subtractedType);

		$this->scope = $scope;
		$this->name = $name;
		$this->strategy = $templateTypeStrategy ?? new TemplateTypeParameterStrategy();
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getScope(): TemplateTypeScope
	{
		return $this->scope;
	}

	public function describe(VerbosityLevel $level): string
	{
		return $this->name;
	}

	public function equals(Type $type): bool
	{
		return $type instanceof self
			&& $type->scope->equals($this->scope)
			&& $type->name === $this->name;
	}

	public function getBound(): Type
	{
		return new MixedType(
			$this->isExplicitMixed(),
			$this->getSubtractedType()
		);
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		return $this->strategy->accepts($this, $type, $strictTypes);
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		return $this->strategy->isSuperTypeOf($this, $type);
	}

	public function isSubTypeOf(Type $type): TrinaryLogic
	{
		return $this->strategy->isSubTypeOf($this, $type);
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

	public function isArgument(): bool
	{
		return $this->strategy->isArgument();
	}

	public function toArgument(): TemplateType
	{
		return new self(
			$this->scope,
			$this->name,
			$this->isExplicitMixed(),
			$this->getSubtractedType(),
			new TemplateTypeArgumentStrategy()
		);
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
			$this->scope,
			$this->name,
			$this->isExplicitMixed(),
			$type,
			$this->strategy
		);
	}

	public function getTypeWithoutSubtractedType(): Type
	{
		return new self(
			$this->scope,
			$this->name,
			$this->isExplicitMixed(),
			null,
			$this->strategy
		);
	}

	public function changeSubtractedType(?Type $subtractedType): Type
	{
		return new self(
			$this->scope,
			$this->name,
			$this->isExplicitMixed(),
			$subtractedType,
			$this->strategy
		);
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self(
			$properties['scope'],
			$properties['name'],
			$properties['isExplicitMixed'],
			$properties['subtractedType'],
			$properties['strategy']
		);
	}

}
