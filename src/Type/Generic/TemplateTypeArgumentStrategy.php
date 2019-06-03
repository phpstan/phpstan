<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\CompoundType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

/**
 * Template type strategy suitable for return type acceptance contexts
 */
class TemplateTypeArgumentStrategy implements TemplateTypeStrategy
{

	public function accepts(TemplateType $left, Type $right, bool $strictTypes): TrinaryLogic
	{
		return $this->isSuperTypeOf($left, $right);
	}

	public function isSuperTypeOf(TemplateType $left, Type $right): TrinaryLogic
	{
		if ($right instanceof CompoundType && !$right instanceof TemplateType) {
			return $right->isSubTypeOf($left);
		}

		return TrinaryLogic::createFromBoolean($left->equals($right));
	}

	public function isSubTypeOf(TemplateType $left, Type $right): TrinaryLogic
	{
		if ($right instanceof UnionType || $right instanceof IntersectionType) {
			return $right->isSuperTypeOf($left);
		}

		return TrinaryLogic::createFromBoolean($left->equals($right));
	}

	public function isArgument(): bool
	{
		return true;
	}

	/**
	 * @param mixed[] $properties
	 */
	public static function __set_state(array $properties): self
	{
		return new self();
	}

}
