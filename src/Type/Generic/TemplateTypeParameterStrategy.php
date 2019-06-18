<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\CompoundType;
use PHPStan\Type\CompoundTypeHelper;
use PHPStan\Type\Type;

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
