<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Type\CompoundType;
use PHPStan\Type\Type;

interface TemplateType extends CompoundType
{

	public function getName(): string;

	public function getScope(): TemplateTypeScope;

	public function getBound(): Type;

	public function toArgument(): TemplateType;

	public function isArgument(): bool;

}
