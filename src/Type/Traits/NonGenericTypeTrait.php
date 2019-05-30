<?php declare(strict_types = 1);

namespace PHPStan\Type\Traits;

use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;

trait NonGenericTypeTrait
{

	public function inferTemplateTypes(Type $receivedType): TemplateTypeMap
	{
		return TemplateTypeMap::empty();
	}

}
