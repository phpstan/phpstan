<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\CompoundType;
use PHPStan\Type\CompoundTypeHelper;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

/**
 * Template type strategy suitable for parameter type acceptance contexts
 */
class TemplateTypeParameterStrategy implements TemplateTypeStrategy
{

	public function accepts(TemplateType $left, Type $right, bool $strictTypes): TrinaryLogic
	{
		if ($right instanceof CompoundType) {
			return CompoundTypeHelper::accepts($right, $left, $strictTypes);
		}

		return $left->getBound()->accepts($right, $strictTypes);
	}

	public function isSuperTypeOf(TemplateType $left, Type $right): TrinaryLogic
	{
		if ($right instanceof CompoundType) {
			return $right->isSubTypeOf($left);
		}

		return $left->getBound()->isSuperTypeOf($right);
	}

	public function isSubTypeOf(TemplateType $left, Type $right): TrinaryLogic
	{
		// Inverse of isSuperTypeOf, except that TemplateTypes are
		// never sub types of non-template types.

		if ($right instanceof UnionType || $right instanceof IntersectionType) {
			return $right->isSuperTypeOf($left);
		}

		if (!$right instanceof TemplateType) {
			return TrinaryLogic::createNo();
		}

		return $right->getBound()->isSuperTypeOf($left->getBound());
	}

	public function isArgument(): bool
	{
		return false;
	}

	/**
	 * @param mixed[] $properties
	 */
	public static function __set_state(array $properties): self
	{
		return new self();
	}

}
