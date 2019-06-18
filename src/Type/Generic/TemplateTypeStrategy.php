<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

interface TemplateTypeStrategy
{

	public function accepts(TemplateType $left, Type $right, bool $strictTypes): TrinaryLogic;

	public function isArgument(): bool;

}
